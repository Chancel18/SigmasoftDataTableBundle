<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Configuration;

use Sigmasoft\DataTableBundle\Column\ColumnInterface;
use Sigmasoft\DataTableBundle\Exception\DataTableException;

class DataTableConfiguration
{
    private array $columns = [];
    private array $searchFields = [];
    private array $filters = [];
    private string $sortField = '';
    private string $sortDirection = 'asc';
    private int $page = 1;
    private int $itemsPerPage = 10;
    private string $searchQuery = '';
    private bool $enableSearch = true;
    private bool $enablePagination = true;
    private bool $enableSorting = true;
    private array $actions = [];
    private string $tableClass = 'table table-striped table-hover';
    private string $entityClass = '';
    private array $itemsPerPageOptions = [10, 25, 50, 100];
    private string $dateFormat = 'd/m/Y';
    private bool $exportEnabled = false;
    private array $exportFormats = ['csv'];
    private string $theme = 'bootstrap5';
    private array $paginationSizes = [5, 10, 25, 50, 100];

    public function __construct(string $entityClass)
    {
        if (empty($entityClass) || !class_exists($entityClass)) {
            throw DataTableException::invalidEntityClass($entityClass);
        }
        $this->entityClass = $entityClass;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function addColumn(ColumnInterface $column): self
    {
        $this->columns[$column->getName()] = $column;
        
        if ($column->isSearchable()) {
            $this->searchFields[] = $column->getPropertyPath();
        }
        
        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumn(string $name): ?ColumnInterface
    {
        return $this->columns[$name] ?? null;
    }

    public function getSearchFields(): array
    {
        return $this->searchFields;
    }

    public function setSearchFields(array $searchFields): self
    {
        $this->searchFields = $searchFields;
        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function addFilter(string $field, mixed $value): self
    {
        $this->filters[$field] = $value;
        return $this;
    }

    public function getSortField(): string
    {
        return $this->sortField;
    }

    public function setSortField(string $sortField): self
    {
        $this->sortField = $sortField;
        return $this;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function setSortDirection(string $sortDirection): self
    {
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            throw DataTableException::invalidSortDirection($sortDirection);
        }
        $this->sortDirection = $sortDirection;
        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        if ($page < 1) {
            throw DataTableException::invalidPage($page);
        }
        $this->page = $page;
        return $this;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function setItemsPerPage(int $itemsPerPage): self
    {
        if ($itemsPerPage < 1) {
            throw DataTableException::invalidItemsPerPage($itemsPerPage);
        }
        $this->itemsPerPage = $itemsPerPage;
        return $this;
    }

    public function getSearchQuery(): string
    {
        return $this->searchQuery;
    }

    public function setSearchQuery(string $searchQuery): self
    {
        $this->searchQuery = $searchQuery;
        return $this;
    }

    public function isSearchEnabled(): bool
    {
        return $this->enableSearch;
    }

    public function setSearchEnabled(bool $enableSearch): self
    {
        $this->enableSearch = $enableSearch;
        return $this;
    }

    public function isPaginationEnabled(): bool
    {
        return $this->enablePagination;
    }

    public function setPaginationEnabled(bool $enablePagination): self
    {
        $this->enablePagination = $enablePagination;
        return $this;
    }

    public function isSortingEnabled(): bool
    {
        return $this->enableSorting;
    }

    public function setSortingEnabled(bool $enableSorting): self
    {
        $this->enableSorting = $enableSorting;
        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): self
    {
        $this->actions = $actions;
        return $this;
    }

    public function addAction(string $name, array $config): self
    {
        $this->actions[$name] = $config;
        return $this;
    }

    public function getTableClass(): string
    {
        return $this->tableClass;
    }

    public function setTableClass(string $tableClass): self
    {
        $this->tableClass = $tableClass;
        return $this;
    }

    public function getItemsPerPageOptions(): array
    {
        return $this->itemsPerPageOptions;
    }

    public function setItemsPerPageOptions(array $itemsPerPageOptions): self
    {
        $this->itemsPerPageOptions = $itemsPerPageOptions;
        return $this;
    }

    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    public function setDateFormat(string $dateFormat): self
    {
        $this->dateFormat = $dateFormat;
        return $this;
    }

    public function isExportEnabled(): bool
    {
        return $this->exportEnabled;
    }

    public function setExportEnabled(bool $exportEnabled): self
    {
        $this->exportEnabled = $exportEnabled;
        return $this;
    }

    public function getExportFormats(): array
    {
        return $this->exportFormats;
    }

    public function setExportFormats(array $exportFormats): self
    {
        $this->exportFormats = $exportFormats;
        return $this;
    }

    public function hasActions(): bool
    {
        return !empty($this->actions);
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    public function getPaginationSizes(): array
    {
        return $this->paginationSizes;
    }

    public function setPaginationSizes(array $paginationSizes): self
    {
        $this->paginationSizes = $paginationSizes;
        return $this;
    }
}
