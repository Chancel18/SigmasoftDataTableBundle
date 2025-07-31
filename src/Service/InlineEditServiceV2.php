<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;
use Sigmasoft\DataTableBundle\Exception\DataTableException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Psr\Log\LoggerInterface;

/**
 * Service amélioré pour l'édition inline avec architecture robuste
 * - Support des transactions
 * - Validation renforcée avec configuration
 * - Contrôles de sécurité
 * - Gestion d'erreurs optimisée
 * - Audit et logging complets
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 */
class InlineEditServiceV2
{
    private PropertyAccessorInterface $propertyAccessor;
    
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private ?Security $security = null,
        private ?LoggerInterface $logger = null
    ) {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Met à jour un champ avec validation renforcée et sécurité
     */
    public function updateField(
        string $entityClass,
        int $entityId,
        string $fieldName,
        mixed $newValue,
        ?EditableFieldConfiguration $fieldConfig = null,
        array $securityOptions = []
    ): array {
        // Commencer une transaction
        $this->entityManager->beginTransaction();
        
        try {
            // 1. Validation des paramètres
            $this->validateUpdateParameters($entityClass, $entityId, $fieldName);
            
            // 2. Récupération et validation de l'entité
            $entity = $this->findAndValidateEntity($entityClass, $entityId);
            
            // 3. Contrôles de sécurité
            $this->performSecurityChecks($entity, $fieldName, $securityOptions);
            
            // 4. Validation de l'accessibilité du champ
            $this->validateFieldAccessibility($entity, $fieldName);
            
            // 5. Sauvegarde de l'ancienne valeur pour audit
            $oldValue = $this->getFieldValue($entity, $fieldName);
            
            // 6. Conversion et validation de la nouvelle valeur
            $convertedValue = $this->convertAndValidateValue(
                $entity, 
                $fieldName, 
                $newValue, 
                $fieldConfig
            );
            
            // 7. Application de la nouvelle valeur
            $this->setFieldValue($entity, $fieldName, $convertedValue);
            
            // 8. Validation de l'entité complète
            $this->validateEntity($entity, $fieldName);
            
            // 9. Sauvegarde en base
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            // 10. Audit et logging
            $this->logSuccessfulUpdate($entityClass, $entityId, $fieldName, $oldValue, $convertedValue);
            
            return $this->buildSuccessResponse($oldValue, $convertedValue);
            
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->logFailedUpdate($entityClass, $entityId, $fieldName, $newValue, $e);
            
            return $this->buildErrorResponse($e);
        }
    }

    /**
     * Mise à jour en lot avec gestion transactionnelle
     */
    public function bulkUpdate(
        string $entityClass,
        array $updates,
        array $globalSecurityOptions = []
    ): array {
        if (empty($updates)) {
            return $this->buildBulkResponse(0, 0, 0, []);
        }
        
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        
        // Transaction globale pour tout le batch
        $this->entityManager->beginTransaction();
        
        try {
            foreach ($updates as $index => $update) {
                $result = $this->processBulkUpdateItem($entityClass, $update, $globalSecurityOptions, $index);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
                
                $results[] = $result;
            }
            
            // Si toutes les opérations ont réussi, valider
            if ($errorCount === 0) {
                $this->entityManager->commit();
                $this->logBulkUpdateSuccess($entityClass, count($updates));
            } else {
                // En cas d'erreur, annuler toutes les modifications
                $this->entityManager->rollback();
                $this->logBulkUpdatePartialFailure($entityClass, $successCount, $errorCount);
            }
            
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->logBulkUpdateFailure($entityClass, $e);
            throw $e;
        }
        
        return $this->buildBulkResponse(count($updates), $successCount, $errorCount, $results);
    }

    /**
     * Validation des paramètres d'entrée
     */
    private function validateUpdateParameters(string $entityClass, int $entityId, string $fieldName): void
    {
        if (!class_exists($entityClass)) {
            throw new DataTableException("Classe d'entité invalide: {$entityClass}");
        }
        
        if ($entityId <= 0) {
            throw new DataTableException("ID d'entité invalide: {$entityId}");
        }
        
        if (empty(trim($fieldName))) {
            throw new DataTableException("Nom de champ invalide: {$fieldName}");
        }
        
        // Validation contre l'injection via le nom du champ
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*$/', $fieldName)) {
            throw new DataTableException("Nom de champ non autorisé: {$fieldName}");
        }
    }

    /**
     * Récupération et validation de l'entité
     */
    private function findAndValidateEntity(string $entityClass, int $entityId): object
    {
        $entity = $this->entityManager->getRepository($entityClass)->find($entityId);
        
        if (!$entity) {
            throw new DataTableException("Entité non trouvée: {$entityClass}#{$entityId}");
        }
        
        return $entity;
    }

    /**
     * Contrôles de sécurité
     */
    private function performSecurityChecks(object $entity, string $fieldName, array $securityOptions): void
    {
        // Vérifier les permissions si Security est disponible
        if ($this->security && isset($securityOptions['required_role'])) {
            if (!$this->security->isGranted($securityOptions['required_role'])) {
                throw new AccessDeniedException("Permission insuffisante pour modifier {$fieldName}");
            }
        }
        
        // Vérifier la propriété de l'entité si spécifié
        if ($this->security && isset($securityOptions['owner_field'])) {
            $ownerField = $securityOptions['owner_field'];
            $currentUser = $this->security->getUser();
            
            if ($currentUser && $this->propertyAccessor->isReadable($entity, $ownerField)) {
                $owner = $this->propertyAccessor->getValue($entity, $ownerField);
                if ($owner !== $currentUser) {
                    throw new AccessDeniedException("Vous ne pouvez modifier que vos propres données");
                }
            }
        }
        
        // Vérifier les champs en lecture seule
        if (isset($securityOptions['readonly_fields']) && 
            in_array($fieldName, $securityOptions['readonly_fields'], true)) {
            throw new AccessDeniedException("Le champ {$fieldName} est en lecture seule");
        }
    }

    /**
     * Validation de l'accessibilité du champ
     */
    private function validateFieldAccessibility(object $entity, string $fieldName): void
    {
        if (!$this->propertyAccessor->isWritable($entity, $fieldName)) {
            throw new DataTableException("Champ non modifiable: {$fieldName}");
        }
    }

    /**
     * Récupération sécurisée de la valeur d'un champ
     */
    private function getFieldValue(object $entity, string $fieldName): mixed
    {
        try {
            return $this->propertyAccessor->getValue($entity, $fieldName);
        } catch (AccessException $e) {
            throw new DataTableException("Impossible de lire le champ {$fieldName}: " . $e->getMessage());
        }
    }

    /**
     * Définition sécurisée de la valeur d'un champ
     */
    private function setFieldValue(object $entity, string $fieldName, mixed $value): void
    {
        try {
            $this->propertyAccessor->setValue($entity, $fieldName, $value);
        } catch (AccessException $e) {
            throw new DataTableException("Impossible de modifier le champ {$fieldName}: " . $e->getMessage());
        }
    }

    /**
     * Conversion et validation de valeur avec configuration
     */
    private function convertAndValidateValue(
        object $entity,
        string $fieldName,
        mixed $value,
        ?EditableFieldConfiguration $fieldConfig
    ): mixed {
        // Conversion basique selon les métadonnées Doctrine
        $convertedValue = $this->convertValueByDoctrineType($entity, $fieldName, $value);
        
        // Validation additionnelle selon la configuration du champ
        if ($fieldConfig) {
            $this->validateValueAgainstFieldConfig($convertedValue, $fieldConfig, $fieldName);
        }
        
        return $convertedValue;
    }

    /**
     * Conversion de valeur selon le type Doctrine
     */
    private function convertValueByDoctrineType(object $entity, string $fieldName, mixed $value): mixed
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($entity));
        
        if (!$metadata->hasField($fieldName)) {
            // Pour les relations ou propriétés calculées
            return $value;
        }

        $fieldMapping = $metadata->getFieldMapping($fieldName);
        $fieldType = $fieldMapping['type'];

        return match ($fieldType) {
            'integer', 'smallint', 'bigint' => $this->convertToInteger($value),
            'decimal', 'float' => $this->convertToFloat($value),
            'boolean' => $this->convertToBoolean($value),
            'datetime', 'datetime_immutable' => $this->convertToDateTime($value),
            'date', 'date_immutable' => $this->convertToDate($value),
            'time', 'time_immutable' => $this->convertToTime($value),
            'json' => $this->convertToJson($value),
            default => $this->convertToString($value)
        };
    }

    /**
     * Validation selon la configuration du champ
     */
    private function validateValueAgainstFieldConfig(
        mixed $value,
        EditableFieldConfiguration $fieldConfig,
        string $fieldName
    ): void {
        $validationRules = $fieldConfig->getValidationRules();
        
        // Validation de la longueur pour les chaînes
        if (is_string($value)) {
            $minLength = $fieldConfig->getMinLength();
            if ($minLength !== null && strlen($value) < $minLength) {
                throw new DataTableException(
                    "La valeur du champ {$fieldName} est trop courte (minimum: {$minLength})"
                );
            }
            
            $maxLength = $fieldConfig->getMaxLength();
            if ($maxLength !== null && strlen($value) > $maxLength) {
                throw new DataTableException(
                    "La valeur du champ {$fieldName} est trop longue (maximum: {$maxLength})"
                );
            }
        }
        
        // Validation du pattern
        $pattern = $fieldConfig->getPattern();
        if ($pattern !== null && is_string($value)) {
            if (!preg_match('/' . $pattern . '/', $value)) {
                throw new DataTableException(
                    "La valeur du champ {$fieldName} ne respecte pas le format requis"
                );
            }
        }
        
        // Validation des valeurs min/max pour les nombres
        if (is_numeric($value)) {
            $min = $fieldConfig->getMin();
            if ($min !== null && $value < (float) $min) {
                throw new DataTableException(
                    "La valeur du champ {$fieldName} est trop petite (minimum: {$min})"
                );
            }
            
            $max = $fieldConfig->getMax();
            if ($max !== null && $value > (float) $max) {
                throw new DataTableException(
                    "La valeur du champ {$fieldName} est trop grande (maximum: {$max})"
                );
            }
        }
        
        // Validation des options pour les selects
        if ($fieldConfig->hasOptions() && $fieldConfig->isSelectType()) {
            $validOptions = array_keys($fieldConfig->getOptions());
            if (!in_array($value, $validOptions, true)) {
                throw new DataTableException(
                    "Valeur non autorisée pour le champ {$fieldName}"
                );
            }
        }
    }

    /**
     * Validation de l'entité via Symfony Validator
     */
    private function validateEntity(object $entity, string $fieldName): void
    {
        $violations = $this->validator->validate($entity);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            
            throw new DataTableException(
                "Validation échouée pour {$fieldName}: " . implode(', ', $errors)
            );
        }
    }

    // Méthodes de conversion spécialisées
    private function convertToInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        throw new DataTableException("Impossible de convertir '{$value}' en entier");
    }

    private function convertToFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        throw new DataTableException("Impossible de convertir '{$value}' en nombre décimal");
    }

    private function convertToBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function convertToDateTime(mixed $value): ?\DateTimeInterface
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        try {
            return new \DateTime((string) $value);
        } catch (\Exception $e) {
            throw new DataTableException("Format de date/heure invalide: {$value}");
        }
    }

    private function convertToDate(mixed $value): ?\DateTimeInterface
    {
        $dateTime = $this->convertToDateTime($value);
        return $dateTime ? $dateTime->setTime(0, 0, 0) : null;
    }

    private function convertToTime(mixed $value): ?\DateTimeInterface
    {
        if (empty($value)) {
            return null;
        }

        try {
            return new \DateTime('1970-01-01 ' . (string) $value);
        } catch (\Exception $e) {
            throw new DataTableException("Format d'heure invalide: {$value}");
        }
    }

    private function convertToJson(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new DataTableException("JSON invalide: " . json_last_error_msg());
            }
            return $decoded;
        }
        
        return $value;
    }

    private function convertToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        
        return (string) $value;
    }

    // Méthodes de construction de réponse
    private function buildSuccessResponse(mixed $oldValue, mixed $newValue): array
    {
        return [
            'success' => true,
            'message' => 'Modification sauvegardée avec succès',
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'formatted_value' => $this->formatValueForDisplay($newValue)
        ];
    }

    private function buildErrorResponse(\Exception $e): array
    {
        $errorCode = $e instanceof DataTableException ? 'BUSINESS_ERROR' : 'SYSTEM_ERROR';
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'code' => $errorCode
        ];
    }

    private function buildBulkResponse(int $total, int $success, int $error, array $results): array
    {
        return [
            'total' => $total,
            'success_count' => $success,
            'error_count' => $error,
            'success_rate' => $total > 0 ? round(($success / $total) * 100, 2) : 0,
            'results' => $results
        ];
    }

    // Méthodes de traitement pour bulk update
    private function processBulkUpdateItem(
        string $entityClass,
        array $update,
        array $globalSecurityOptions,
        int $index
    ): array {
        $entityId = $update['entity_id'] ?? null;
        $fieldName = $update['field_name'] ?? null;
        $newValue = $update['new_value'] ?? null;
        $fieldConfig = $update['field_config'] ?? null;
        $localSecurityOptions = array_merge($globalSecurityOptions, $update['security_options'] ?? []);

        if (!$entityId || !$fieldName) {
            return [
                'index' => $index,
                'entity_id' => $entityId,
                'field_name' => $fieldName,
                'success' => false,
                'error' => 'Paramètres manquants (entity_id et field_name requis)',
                'code' => 'MISSING_PARAMETERS'
            ];
        }

        try {
            $result = $this->updateField(
                $entityClass,
                (int) $entityId,
                $fieldName,
                $newValue,
                $fieldConfig,
                $localSecurityOptions
            );
            
            $result['index'] = $index;
            $result['entity_id'] = $entityId;
            $result['field_name'] = $fieldName;
            
            return $result;
            
        } catch (\Exception $e) {
            return [
                'index' => $index,
                'entity_id' => $entityId,
                'field_name' => $fieldName,
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e instanceof DataTableException ? 'BUSINESS_ERROR' : 'SYSTEM_ERROR'
            ];
        }
    }

    private function formatValueForDisplay(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    // Méthodes de logging
    private function logSuccessfulUpdate(
        string $entityClass,
        int $entityId,
        string $fieldName,
        mixed $oldValue,
        mixed $newValue
    ): void {
        if ($this->logger) {
            $this->logger->info('Inline edit successful', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'field_name' => $fieldName,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'user' => $this->security?->getUser()?->getUserIdentifier()
            ]);
        }
    }

    private function logFailedUpdate(
        string $entityClass,
        int $entityId,
        string $fieldName,
        mixed $newValue,
        \Exception $e
    ): void {
        if ($this->logger) {
            $this->logger->error('Inline edit failed', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'field_name' => $fieldName,
                'new_value' => $newValue,
                'error' => $e->getMessage(),
                'user' => $this->security?->getUser()?->getUserIdentifier(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function logBulkUpdateSuccess(string $entityClass, int $count): void
    {
        if ($this->logger) {
            $this->logger->info('Bulk inline edit successful', [
                'entity_class' => $entityClass,
                'updated_count' => $count,
                'user' => $this->security?->getUser()?->getUserIdentifier()
            ]);
        }
    }

    private function logBulkUpdatePartialFailure(string $entityClass, int $success, int $error): void
    {
        if ($this->logger) {
            $this->logger->warning('Bulk inline edit partially failed', [
                'entity_class' => $entityClass,
                'success_count' => $success,
                'error_count' => $error,
                'user' => $this->security?->getUser()?->getUserIdentifier()
            ]);
        }
    }

    private function logBulkUpdateFailure(string $entityClass, \Exception $e): void
    {
        if ($this->logger) {
            $this->logger->error('Bulk inline edit failed completely', [
                'entity_class' => $entityClass,
                'error' => $e->getMessage(),
                'user' => $this->security?->getUser()?->getUserIdentifier(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
