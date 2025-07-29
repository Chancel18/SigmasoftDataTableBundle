<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Factory;

use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;

final class DataTableFactory
{
    public function __construct(
        private DataTableBuilder $builder,
        private array $defaults = []
    ) {
    }

    public function create(string $entityClass): DataTableBuilder
    {
        $config = $this->builder->createDataTable($entityClass);
        
        // Apply default configuration
        if (!empty($this->defaults)) {
            $this->applyDefaults($config);
        }
        
        return $this->builder;
    }

    public function createForEntity(string $entityClass, array $columns = [], array $options = []): DataTableConfiguration
    {
        $config = new DataTableConfiguration($entityClass);
        
        // Apply defaults
        $this->applyDefaults($config);
        
        // Apply options
        foreach ($options as $key => $value) {
            match ($key) {
                'items_per_page' => $config->setItemsPerPage($value),
                'enable_search' => $config->setSearchEnabled($value),
                'enable_pagination' => $config->setPaginationEnabled($value),
                'enable_sorting' => $config->setSortingEnabled($value),
                'table_class' => $config->setTableClass($value),
                'sort_field' => $config->setSortField($value),
                'sort_direction' => $config->setSortDirection($value),
                default => null
            };
        }
        
        return $config;
    }

    private function applyDefaults(DataTableConfiguration $config): void
    {
        $config
            ->setItemsPerPage($this->defaults['items_per_page'] ?? 10)
            ->setSearchEnabled($this->defaults['enable_search'] ?? true)
            ->setPaginationEnabled($this->defaults['enable_pagination'] ?? true)
            ->setSortingEnabled($this->defaults['enable_sorting'] ?? true)
            ->setTableClass($this->defaults['table_class'] ?? 'table table-striped table-hover align-middle');
    }
}
