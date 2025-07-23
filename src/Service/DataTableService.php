<?php
declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sigmasoft\DataTableBundle\Model\DataTableResult;
use Sigmasoft\DataTableBundle\Model\DataTableRequest;
use Sigmasoft\DataTableBundle\Event\DataTableAfterDeleteEvent;
use Sigmasoft\DataTableBundle\Event\DataTableAfterLoadEvent;
use Sigmasoft\DataTableBundle\Event\DataTableBeforeDeleteEvent;
use Sigmasoft\DataTableBundle\Event\DataTableBeforeLoadEvent;
use Sigmasoft\DataTableBundle\Event\DataTableValueFormatEvent;
use Sigmasoft\DataTableBundle\Event\BulkActionEvent;
use Sigmasoft\DataTableBundle\Exception\EntityNotFoundException;
use Sigmasoft\DataTableBundle\Exception\EntityNotAllowedException;

class DataTableService implements DataTableServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PaginatorInterface $paginator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ValueFormatter $valueFormatter,
        private readonly ConfigurationManager $configurationManager,
        private readonly array $allowedEntities = []
    ) {}

    public function getData(DataTableRequest $request): DataTableResult
    {
        $this->validateEntityClass($request->entityClass);

        $queryBuilder = $this->buildBaseQuery($request);

        // Événement avant chargement
        $beforeLoadEvent = new DataTableBeforeLoadEvent(
            $request->entityClass,
            $request,
            $queryBuilder,
            ['user_id' => $this->getCurrentUserId()]
        );
        $this->eventDispatcher->dispatch($beforeLoadEvent, DataTableBeforeLoadEvent::NAME);

        $queryBuilder = $beforeLoadEvent->getQueryBuilder();
        $query = $queryBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $query,
            $request->page,
            $request->itemsPerPage
        );

        $result = DataTableResult::fromPagination($pagination);

        // Événement après chargement
        $afterLoadEvent = new DataTableAfterLoadEvent(
            $request->entityClass,
            $request,
            $result,
            ['user_id' => $this->getCurrentUserId()]
        );
        $this->eventDispatcher->dispatch($afterLoadEvent, DataTableAfterLoadEvent::NAME);

        return $afterLoadEvent->getResult();
    }

    public function deleteEntity(string $entityClass, int $id): bool
    {
        $this->validateEntityClass($entityClass);

        $repository = $this->entityManager->getRepository($entityClass);
        $entity = $repository->find($id);

        if (!$entity) {
            throw new EntityNotFoundException($entityClass, $id);
        }

        // Événement avant suppression
        $beforeDeleteEvent = new DataTableBeforeDeleteEvent(
            $entityClass,
            $entity,
            $id,
            ['user_id' => $this->getCurrentUserId()]
        );
        $this->eventDispatcher->dispatch($beforeDeleteEvent, DataTableBeforeDeleteEvent::NAME);

        if ($beforeDeleteEvent->isPreventDefault()) {
            throw new \RuntimeException($beforeDeleteEvent->getErrorMessage() ?? 'Suppression annulée');
        }

        $success = false;
        try {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
            $success = true;
        } catch (\Exception $e) {
            // Log l'erreur
            $success = false;
        }

        // Événement après suppression
        $afterDeleteEvent = new DataTableAfterDeleteEvent(
            $entityClass,
            $id,
            $success,
            ['user_id' => $this->getCurrentUserId()]
        );
        $this->eventDispatcher->dispatch($afterDeleteEvent, DataTableAfterDeleteEvent::NAME);

        return $success;
    }

    public function getValue(mixed $item, string $field, array $formatConfig): mixed
    {
        $value = $this->valueFormatter->extractValue($item, $field);

        // Événement de formatage de valeur
        $valueFormatEvent = new DataTableValueFormatEvent(
            get_class($item),
            $item,
            $field,
            $value,
            $formatConfig,
            ['user_id' => $this->getCurrentUserId()]
        );
        $this->eventDispatcher->dispatch($valueFormatEvent, DataTableValueFormatEvent::NAME);

        return $this->valueFormatter->formatValue($valueFormatEvent->getValue(), $field, $formatConfig);
    }

    public function findEntity(string $entityClass, int $id): ?object
    {
        try {
            return $this->entityManager->find($entityClass, $id);
        } catch (\Exception) {
            return null;
        }
    }

    private function buildBaseQuery(DataTableRequest $request): QueryBuilder
    {
        $repo = $this->entityManager->getRepository($request->entityClass);
        $builder = $repo->createQueryBuilder('e');

        $this->applySearch($builder, $request);
        $this->applySorting($builder, $request);
        $this->applyFilters($builder, $request);

        return $builder;
    }

    private function applySearch(QueryBuilder $builder, DataTableRequest $request): void
    {
        if (empty($request->search) || empty($request->searchFields)) {
            return;
        }

        $searchConditions = [];
        $joinedAliases = $this->getJoinedAliases($builder);
        $paramCounter = 0;
        
        foreach ($request->searchFields as $field) {
            $paramName = 'search_param_' . (++$paramCounter);
            
            if (str_contains($field, '.')) {
                $parts = explode('.', $field, 2);
                $alias = $parts[0];
                $property = $parts[1];
                
                // Validation des noms d'alias et de propriété pour éviter les injections
                if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $alias) || 
                    !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $property)) {
                    continue; // Ignorer les champs invalides
                }
                
                // Éviter les jointures dupliquées
                if (!in_array($alias, $joinedAliases)) {
                    $builder->leftJoin('e.' . $alias, $alias);
                    $joinedAliases[] = $alias;
                }
                
                $searchConditions[] = 'LOWER(' . $alias . '.' . $property . ') LIKE :' . $paramName;
            } else {
                // Validation du nom de champ pour éviter les injections
                if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field)) {
                    continue; // Ignorer les champs invalides
                }
                
                $searchConditions[] = 'LOWER(e.' . $field . ') LIKE :' . $paramName;
            }
            
            $builder->setParameter($paramName, '%' . strtolower($request->search) . '%');
        }

        if (!empty($searchConditions)) {
            $builder->andWhere(implode(' OR ', $searchConditions));
        }
    }

    private function applySorting(QueryBuilder $builder, DataTableRequest $request): void
    {
        if (empty($request->sortField)) {
            return;
        }

        // Validation de la direction de tri
        $sortDirection = strtoupper($request->sortDirection ?? 'ASC');
        if (!in_array($sortDirection, ['ASC', 'DESC'])) {
            $sortDirection = 'ASC';
        }

        $joinedAliases = $this->getJoinedAliases($builder);
        
        if (str_contains($request->sortField, '.')) {
            $parts = explode('.', $request->sortField, 2);
            $alias = $parts[0];
            $property = $parts[1];
            
            // Validation des noms d'alias et de propriété
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $alias) || 
                !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $property)) {
                return; // Ignorer le tri si les noms sont invalides
            }
            
            // Éviter les jointures dupliquées
            if (!in_array($alias, $joinedAliases)) {
                $builder->leftJoin('e.' . $alias, $alias);
            }
            
            $builder->orderBy($alias . '.' . $property, $sortDirection);
        } else {
            // Validation du nom de champ
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $request->sortField)) {
                return; // Ignorer le tri si le nom est invalide
            }
            
            $builder->orderBy('e.' . $request->sortField, $sortDirection);
        }
    }

    private function applyFilters(QueryBuilder $builder, DataTableRequest $request): void
    {
        if (empty($request->filters)) {
            return;
        }
        
        $joinedAliases = $this->getJoinedAliases($builder);
        
        foreach ($request->filters as $field => $value) {
            if (empty($value)) {
                continue;
            }

            if (str_contains($field, '.')) {
                $parts = explode('.', $field);
                $alias = $parts[0];
                $property = $parts[1];
                
                // Éviter les jointures dupliquées
                if (!in_array($alias, $joinedAliases)) {
                    $builder->leftJoin('e.' . $alias, $alias);
                    $joinedAliases[] = $alias;
                }
                
                $builder->andWhere($alias . '.' . $property . ' = :filter_' . str_replace('.', '_', $field));
            } else {
                $builder->andWhere('e.' . $field . ' = :filter_' . $field);
            }

            $builder->setParameter('filter_' . str_replace('.', '_', $field), $value);
        }
    }

    /**
     * Récupère les alias déjà joints dans la requête
     */
    private function getJoinedAliases(QueryBuilder $builder): array
    {
        $joins = $builder->getDQLPart('join');
        $aliases = [];
        
        if (isset($joins['e'])) {
            foreach ($joins['e'] as $join) {
                $aliases[] = $join->getAlias();
            }
        }
        
        return $aliases;
    }

    private function validateEntityClass(string $entityClass): void
    {
        if (!empty($this->allowedEntities) && !in_array($entityClass, $this->allowedEntities)) {
            throw new EntityNotAllowedException($entityClass);
        }

        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException("Entity class does not exist: $entityClass");
        }
    }

    private function getCurrentUserId(): ?int
    {
        // Implement logic to get current user ID from security context
        return null;
    }

    public function executeBulkAction(string $entityClass, string $action, array $ids): array
    {
        $this->validateEntityClass($entityClass);

        $entityConfig = $this->configurationManager->getEntityConfig($entityClass);
        $bulkActions = $entityConfig->getBulkActions();

        $actionConfig = null;
        foreach ($bulkActions as $config) {
            if ($config['action'] === $action) {
                $actionConfig = $config;
                break;
            }
        }

        if (!$actionConfig) {
            throw new \InvalidArgumentException("Bulk action '$action' not found");
        }

        return $this->executeBulkActionByType($entityClass, $action, $ids, $actionConfig);
    }

    public function exportData(DataTableRequest $request, string $format): array
    {
        $this->validateEntityClass($request->entityClass);

        $entityConfig = $this->configurationManager->getEntityConfig($request->entityClass);

        if (!$entityConfig->isExportEnabled()) {
            throw new \RuntimeException("Export not enabled for entity: {$request->entityClass}");
        }

        $exportConfig = $entityConfig->getExportConfig();
        $allowedFormats = $exportConfig['formats'] ?? ['csv'];

        if (!in_array($format, $allowedFormats)) {
            throw new \InvalidArgumentException("Export format '$format' not allowed");
        }

        // Construire la requête sans pagination pour l'export
        $queryBuilder = $this->buildBaseQuery($request);
        $results = $queryBuilder->getQuery()->getResult();

        return $this->generateExport($results, $format, $entityConfig);
    }

    private function executeBulkActionByType(string $entityClass, string $action, array $ids, array $actionConfig): array
    {
        $repository = $this->entityManager->getRepository($entityClass);
        $entities = $repository->findBy(['id' => $ids]);

        $successCount = 0;
        $errors = [];

        foreach ($entities as $entity) {
            try {
                switch ($action) {
                    case 'activate':
                        if (method_exists($entity, 'setIsActive')) {
                            $entity->setIsActive(true);
                            $successCount++;
                        }
                        break;

                    case 'deactivate':
                        if (method_exists($entity, 'setIsActive')) {
                            $entity->setIsActive(false);
                            $successCount++;
                        }
                        break;

                    case 'delete':
                        $this->entityManager->remove($entity);
                        $successCount++;
                        break;

                    default:
                        // Action personnalisée - déclencher un événement
                        $event = new BulkActionEvent($entityClass, $action, $entity, $actionConfig);
                        $this->eventDispatcher->dispatch($event, BulkActionEvent::NAME);

                        if ($event->isSuccess()) {
                            $successCount++;
                        } else {
                            $errors[] = $event->getErrorMessage();
                        }
                        break;
                }
            } catch (\Exception $e) {
                $errors[] = "Erreur pour l'ID {$entity->getId()}: " . $e->getMessage();
            }
        }

        if ($successCount > 0) {
            $this->entityManager->flush();
        }

        $totalProcessed = count($entities);
        $message = "$successCount/$totalProcessed éléments traités avec succès";

        if (!empty($errors)) {
            $message .= ". Erreurs: " . implode(', ', $errors);
        }

        return [
            'success' => $successCount > 0,
            'message' => $message,
            'processed' => $totalProcessed,
            'success_count' => $successCount,
            'errors' => $errors
        ];
    }

    private function generateExport(array $results, string $format, EntityConfiguration $entityConfig): array
    {
        $fields = $entityConfig->getFields();
        $exportConfig = $entityConfig->getExportConfig();

        $data = [];

        // Headers
        if ($exportConfig['include_headers'] ?? true) {
            $headers = [];
            foreach ($fields as $fieldName => $fieldConfig) {
                if ($fieldConfig['visible'] ?? true) {
                    $headers[] = $fieldConfig['label'];
                }
            }
            $data[] = $headers;
        }

        // Data rows
        foreach ($results as $item) {
            $row = [];
            foreach ($fields as $fieldName => $fieldConfig) {
                if ($fieldConfig['visible'] ?? true) {
                    $value = $this->getValue($item, $fieldName, $fieldConfig['options'] ?? []);
                    // Nettoyer les valeurs pour l'export (enlever HTML, etc.)
                    $row[] = $this->cleanValueForExport($value);
                }
            }
            $data[] = $row;
        }

        return [
            'data' => $data,
            'format' => $format,
            'filename' => $this->generateExportFilename($entityConfig, $format)
        ];
    }

    private function cleanValueForExport(mixed $value): string
    {
        if (is_null($value)) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        // Enlever les tags HTML
        $cleaned = strip_tags((string) $value);

        // Nettoyer les caractères spéciaux pour CSV
        return str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $cleaned);
    }

    private function generateExportFilename(EntityConfiguration $entityConfig, string $format): string
    {
        $label = strtolower(str_replace(' ', '_', $entityConfig->getLabel()));
        $date = date('Y-m-d_H-i-s');

        return "{$label}_export_{$date}.{$format}";
    }
}