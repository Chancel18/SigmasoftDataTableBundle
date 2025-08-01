<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\DataProvider;

use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Event\DataTableEvents;
use Sigmasoft\DataTableBundle\Event\DataTableQueryEvent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

final class DoctrineDataProvider implements DataProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaginatorInterface $paginator,
        private ?LoggerInterface $logger = null,
        private ?EventDispatcherInterface $eventDispatcher = null
    ) {
    }

    public function getData(DataTableConfiguration $configuration): PaginationInterface
    {
        $queryBuilder = $this->createBaseQueryBuilder($configuration);
        
        // Déclencher l'événement PRE_QUERY
        if ($this->eventDispatcher) {
            $event = new DataTableQueryEvent($configuration->getEntityClass(), $queryBuilder);
            $event->setSearchTerm($configuration->getSearchValue())
                  ->setSortField($configuration->getSortBy())
                  ->setSortDirection($configuration->getSortOrder())
                  ->setCurrentPage($configuration->getPage())
                  ->setItemsPerPage($configuration->getItemsPerPage());
            
            $this->eventDispatcher->dispatch($event, DataTableEvents::PRE_QUERY);
            $queryBuilder = $event->getQueryBuilder();
        }
        
        $this->applyFilters($queryBuilder, $configuration);
        $this->applySearch($queryBuilder, $configuration);
        $this->applySorting($queryBuilder, $configuration);

        $query = $queryBuilder->getQuery();

        if ($this->logger) {
            $this->logger->debug('DataTable SQL Query', [
                'sql' => $query->getSQL(),
                'parameters' => $query->getParameters()->toArray(),
            ]);
        }

        $results = $this->paginator->paginate(
            $query,
            $configuration->getPage(),
            $configuration->getItemsPerPage()
        );
        
        // Déclencher l'événement POST_QUERY
        if ($this->eventDispatcher) {
            $event = new DataTableQueryEvent($configuration->getEntityClass(), $queryBuilder);
            $event->setResults($results);
            $this->eventDispatcher->dispatch($event, DataTableEvents::POST_QUERY);
        }
        
        return $results;
    }

    public function getTotalCount(DataTableConfiguration $configuration): int
    {
        try {
            $queryBuilder = $this->createBaseQueryBuilder($configuration);
            $this->applyFilters($queryBuilder, $configuration);
            $this->applySearch($queryBuilder, $configuration);

            $queryBuilder->select('COUNT(DISTINCT e.id)');

            return (int) $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Error counting total items', [
                    'error' => $e->getMessage(),
                    'entity' => $configuration->getEntityClass()
                ]);
            }
            return 0;
        }
    }

    public function supports(string $entityClass): bool
    {
        return class_exists($entityClass) && $this->entityManager->getMetadataFactory()->hasMetadataFor($entityClass);
    }

    private function createBaseQueryBuilder(DataTableConfiguration $configuration): QueryBuilder
    {
        $repository = $this->entityManager->getRepository($configuration->getEntityClass());
        return $repository->createQueryBuilder('e');
    }

    private function applyFilters(QueryBuilder $queryBuilder, DataTableConfiguration $configuration): void
    {
        foreach ($configuration->getFilters() as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $paramName = 'filter_' . str_replace('.', '_', $field);

            if (str_contains($field, '.')) {
                $this->handleRelationalFilter($queryBuilder, $field, $value, $paramName);
            } else {
                $queryBuilder->andWhere("e.{$field} = :{$paramName}")
                    ->setParameter($paramName, $value);
            }
        }
    }

    private function applySearch(QueryBuilder $queryBuilder, DataTableConfiguration $configuration): void
    {
        $searchQuery = trim($configuration->getSearchQuery());
        if ($searchQuery === '' || empty($configuration->getSearchFields())) {
            return;
        }

        $searchConditions = [];
        $joinedAliases = [];

        foreach ($configuration->getSearchFields() as $field) {
            if (!$this->isValidFieldPath($field, $configuration->getEntityClass())) {
                continue; // Skip invalid fields
            }

            if (str_contains($field, '.')) {
                $alias = $this->addJoinIfNeeded($queryBuilder, $field, $joinedAliases);
                $property = $this->getPropertyFromPath($field);
                $searchConditions[] = "LOWER({$alias}.{$property}) LIKE :search";
            } else {
                $searchConditions[] = "LOWER(e.{$field}) LIKE :search";
            }
        }

        if (!empty($searchConditions)) {
            $queryBuilder->andWhere(implode(' OR ', $searchConditions))
                ->setParameter('search', '%' . strtolower($searchQuery) . '%');
        }
    }

    private function applySorting(QueryBuilder $queryBuilder, DataTableConfiguration $configuration): void
    {
        $sortField = $configuration->getSortField();
        if ($sortField === '') {
            return;
        }

        // Validate the sort field exists
        if (!$this->isValidFieldPath($sortField, $configuration->getEntityClass())) {
            if ($this->logger) {
                $this->logger->warning('Invalid sort field', [
                    'field' => $sortField,
                    'entity' => $configuration->getEntityClass()
                ]);
            }
            return;
        }

        $direction = strtoupper($configuration->getSortDirection());
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }

        if (str_contains($sortField, '.')) {
            $joinedAliases = [];
            $alias = $this->addJoinIfNeeded($queryBuilder, $sortField, $joinedAliases);
            $property = $this->getPropertyFromPath($sortField);
            $queryBuilder->orderBy("{$alias}.{$property}", $direction);
        } else {
            $queryBuilder->orderBy("e.{$sortField}", $direction);
        }
    }

    private function handleRelationalFilter(QueryBuilder $queryBuilder, string $field, mixed $value, string $paramName): void
    {
        if (!$this->isValidFieldPath($field, $queryBuilder->getRootEntities()[0])) {
            return; // Skip invalid fields
        }

        $joinedAliases = [];
        $alias = $this->addJoinIfNeeded($queryBuilder, $field, $joinedAliases);
        $property = $this->getPropertyFromPath($field);

        $queryBuilder->andWhere("{$alias}.{$property} = :{$paramName}")
            ->setParameter($paramName, $value);
    }

    private function addJoinIfNeeded(QueryBuilder $queryBuilder, string $field, array &$joinedAliases): string
    {
        $parts = explode('.', $field);
        $currentAlias = 'e';
        
        // Handle multi-level relations (e.g., user.profile.name)
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $relation = $parts[$i];
            $nextAlias = $relation;
            
            // Create unique alias for nested relations
            if ($i > 0) {
                $nextAlias = implode('_', array_slice($parts, 0, $i + 1));
            }
            
            if (!in_array($nextAlias, $joinedAliases)) {
                $queryBuilder->leftJoin("{$currentAlias}.{$relation}", $nextAlias);
                $joinedAliases[] = $nextAlias;
            }
            
            $currentAlias = $nextAlias;
        }

        return $currentAlias;
    }

    private function isValidFieldPath(string $fieldPath, string $entityClass): bool
    {
        try {
            $metadata = $this->entityManager->getClassMetadata($entityClass);
            $parts = explode('.', $fieldPath);
            
            $currentMetadata = $metadata;
            
            foreach ($parts as $part) {
                if ($currentMetadata->hasField($part)) {
                    // It's a field, should be the last part
                    return array_search($part, $parts) === count($parts) - 1;
                } elseif ($currentMetadata->hasAssociation($part)) {
                    // It's an association, get target metadata
                    $targetClass = $currentMetadata->getAssociationTargetClass($part);
                    $currentMetadata = $this->entityManager->getClassMetadata($targetClass);
                } else {
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getPropertyFromPath(string $fieldPath): string
    {
        $parts = explode('.', $fieldPath);
        return end($parts);
    }
}
