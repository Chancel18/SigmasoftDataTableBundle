<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Configuration;

class SerializableDataTableConfig
{
    public string $configId = '';
    public string $entityClass = '';
    public array $columnDefinitions = [];
    public array $searchFields = [];
    public array $filters = [];
    public string $sortField = '';
    public string $sortDirection = 'asc';
    public int $page = 1;
    public int $itemsPerPage = 10;
    public string $searchQuery = '';
    public bool $enableSearch = true;
    public bool $enablePagination = true;
    public bool $enableSorting = true;
    public array $actions = [];
    public string $tableClass = 'table table-striped table-hover';

    public function __construct(
        string $configId = '',
        string $entityClass = '',
        array $columnDefinitions = [],
        array $searchFields = [],
        array $filters = [],
        string $sortField = '',
        string $sortDirection = 'asc',
        int $page = 1,
        int $itemsPerPage = 10,
        string $searchQuery = '',
        bool $enableSearch = true,
        bool $enablePagination = true,
        bool $enableSorting = true,
        array $actions = [],
        string $tableClass = 'table table-striped table-hover'
    ) {
        $this->configId = $configId;
        $this->entityClass = $entityClass;
        $this->columnDefinitions = $columnDefinitions;
        $this->searchFields = $searchFields;
        $this->filters = $filters;
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
        $this->page = $page;
        $this->itemsPerPage = $itemsPerPage;
        $this->searchQuery = $searchQuery;
        $this->enableSearch = $enableSearch;
        $this->enablePagination = $enablePagination;
        $this->enableSorting = $enableSorting;
        $this->actions = $actions;
        $this->tableClass = $tableClass;
    }

    public static function fromConfiguration(string $configId, DataTableConfiguration $config): self
    {
        // Convertir les colonnes en définitions sérialisables
        $columnDefinitions = [];
        foreach ($config->getColumns() as $column) {
            $columnDefinitions[] = [
                'type' => get_class($column),
                'name' => $column->getName(),
                'property_path' => $column->getPropertyPath(),
                'label' => $column->getLabel(),
                'sortable' => $column->isSortable(),
                'searchable' => $column->isSearchable(),
                'options' => $column->getOptions()
            ];
        }

        return new self(
            $configId,
            $config->getEntityClass(),
            $columnDefinitions,
            $config->getSearchFields(),
            $config->getFilters(),
            $config->getSortField(),
            $config->getSortDirection(),
            $config->getPage(),
            $config->getItemsPerPage(),
            $config->getSearchQuery(),
            $config->isSearchEnabled(),
            $config->isPaginationEnabled(),
            $config->isSortingEnabled(),
            $config->getActions(),
            $config->getTableClass()
        );
    }

    public function createMutableConfig(): DataTableConfiguration
    {
        $config = new DataTableConfiguration($this->entityClass);
        
        $config
            ->setSearchFields($this->searchFields)
            ->setFilters($this->filters)
            ->setSortField($this->sortField)
            ->setSortDirection($this->sortDirection)
            ->setPage($this->page)
            ->setItemsPerPage($this->itemsPerPage)
            ->setSearchQuery($this->searchQuery)
            ->setSearchEnabled($this->enableSearch)
            ->setPaginationEnabled($this->enablePagination)
            ->setSortingEnabled($this->enableSorting)
            ->setActions($this->actions)
            ->setTableClass($this->tableClass);

        return $config;
    }

    public function withUpdates(
        ?array $filters = null,
        ?string $sortField = null,
        ?string $sortDirection = null,
        ?int $page = null,
        ?int $itemsPerPage = null,
        ?string $searchQuery = null
    ): self {
        return new self(
            $this->configId,
            $this->entityClass,
            $this->columnDefinitions,
            $this->searchFields,
            $filters ?? $this->filters,
            $sortField ?? $this->sortField,
            $sortDirection ?? $this->sortDirection,
            $page ?? $this->page,
            $itemsPerPage ?? $this->itemsPerPage,
            $searchQuery ?? $this->searchQuery,
            $this->enableSearch,
            $this->enablePagination,
            $this->enableSorting,
            $this->actions,
            $this->tableClass
        );
    }
}
