<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

class InlineEditService
{
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private ?LoggerInterface $logger = null
    ) {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function updateField(string $entityClass, int $entityId, string $fieldName, mixed $newValue): array
    {
        try {
            // Récupérer l'entité
            $entity = $this->entityManager->getRepository($entityClass)->find($entityId);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'error' => 'Entité non trouvée',
                    'code' => 'ENTITY_NOT_FOUND'
                ];
            }

            // Valider le nom du champ
            if (!$this->propertyAccessor->isWritable($entity, $fieldName)) {
                return [
                    'success' => false,
                    'error' => 'Champ non modifiable',
                    'code' => 'FIELD_NOT_WRITABLE'
                ];
            }

            // Obtenir l'ancienne valeur pour l'audit
            $oldValue = $this->propertyAccessor->getValue($entity, $fieldName);

            // Convertir la nouvelle valeur selon le type
            $convertedValue = $this->convertValue($entity, $fieldName, $newValue);

            // Mettre à jour la valeur
            $this->propertyAccessor->setValue($entity, $fieldName, $convertedValue);

            // Valider l'entité
            $violations = $this->validator->validate($entity);

            if (count($violations) > 0) {
                // Restaurer l'ancienne valeur en cas d'erreur
                $this->propertyAccessor->setValue($entity, $fieldName, $oldValue);
                
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }

                return [
                    'success' => false,
                    'error' => 'Données invalides: ' . implode(', ', $errors),
                    'code' => 'VALIDATION_ERROR',
                    'violations' => $errors
                ];
            }

            // Sauvegarder
            $this->entityManager->flush();

            // Logger l'action
            if ($this->logger) {
                $this->logger->info('Inline edit successful', [
                    'entity_class' => $entityClass,
                    'entity_id' => $entityId,
                    'field_name' => $fieldName,
                    'old_value' => $oldValue,
                    'new_value' => $convertedValue
                ]);
            }

            return [
                'success' => true,
                'message' => 'Modification sauvegardée',
                'old_value' => $oldValue,
                'new_value' => $convertedValue,
                'formatted_value' => $this->formatValueForDisplay($convertedValue)
            ];

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Inline edit failed', [
                    'entity_class' => $entityClass,
                    'entity_id' => $entityId,
                    'field_name' => $fieldName,
                    'new_value' => $newValue,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            return [
                'success' => false,
                'error' => 'Erreur lors de la sauvegarde: ' . $e->getMessage(),
                'code' => 'SAVE_ERROR'
            ];
        }
    }

    private function convertValue(object $entity, string $fieldName, mixed $value): mixed
    {
        // Obtenir les métadonnées du champ
        $metadata = $this->entityManager->getClassMetadata(get_class($entity));
        
        if (!$metadata->hasField($fieldName)) {
            // Si ce n'est pas un champ direct, retourner la valeur telle quelle
            return $value;
        }

        $fieldMapping = $metadata->getFieldMapping($fieldName);
        $fieldType = $fieldMapping['type'];

        return match ($fieldType) {
            'integer', 'smallint', 'bigint' => (int) $value,
            'decimal', 'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'datetime', 'datetime_immutable' => $this->convertToDateTime($value),
            'date', 'date_immutable' => $this->convertToDate($value),
            'time', 'time_immutable' => $this->convertToTime($value),
            'json' => is_string($value) ? json_decode($value, true) : $value,
            default => (string) $value
        };
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
        } catch (\Exception) {
            return null;
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
        } catch (\Exception) {
            return null;
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

    public function bulkUpdate(string $entityClass, array $updates): array
    {
        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($updates as $update) {
            $entityId = $update['entity_id'] ?? null;
            $fieldName = $update['field_name'] ?? null;
            $newValue = $update['new_value'] ?? null;

            if (!$entityId || !$fieldName) {
                $results[] = [
                    'entity_id' => $entityId,
                    'field_name' => $fieldName,
                    'success' => false,
                    'error' => 'Paramètres manquants'
                ];
                $errorCount++;
                continue;
            }

            $result = $this->updateField($entityClass, (int) $entityId, $fieldName, $newValue);
            $result['entity_id'] = $entityId;
            $result['field_name'] = $fieldName;
            
            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }

            $results[] = $result;
        }

        return [
            'total' => count($updates),
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'results' => $results
        ];
    }
}
