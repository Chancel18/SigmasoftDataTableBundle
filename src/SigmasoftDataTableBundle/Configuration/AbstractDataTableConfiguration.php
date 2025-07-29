<?php

namespace Sigmasoft\DataTableBundle\Configuration;

use Sigmasoft\DataTableBundle\Column\ColumnInterface;

abstract class AbstractDataTableConfiguration implements DataTableConfigurationInterface
{
    protected array $columns = [];
    protected bool $searchEnabled = true;
    protected array $searchableFields = [];
    protected bool $paginationEnabled = true;
    protected int $itemsPerPage = 10;
    protected array $itemsPerPageOptions = [10, 25, 50, 100];
    protected ?string $sortField = null;
    protected string $sortDirection = 'ASC';
    protected string $tableClass = 'table table-striped table-hover';
    protected string $dateFormat = 'd/m/Y';
    protected bool $exportEnabled = false;
    protected array $exportFormats = ['csv', 'excel', 'pdf'];
    
    abstract public function getEntityClass(): string;
    
    abstract public function configure(): void;
    
    public function __construct()
    {
        $this->configure();
    }
    
    protected function addColumn(ColumnInterface $column): self
    {
        $this->columns[] = $column;
        return $this;
    }
    
    protected function setSearchEnabled(bool $enabled): self
    {
        $this->searchEnabled = $enabled;
        return $this;
    }
    
    protected function setSearchableFields(array $fields): self
    {
        $this->searchableFields = $fields;
        return $this;
    }
    
    protected function setPaginationEnabled(bool $enabled): self
    {
        $this->paginationEnabled = $enabled;
        return $this;
    }
    
    protected function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;
        return $this;
    }
    
    protected function setItemsPerPageOptions(array $options): self
    {
        $this->itemsPerPageOptions = $options;
        return $this;
    }
    
    protected function setSortField(?string $field): self
    {
        $this->sortField = $field;
        return $this;
    }
    
    protected function setSortDirection(string $direction): self
    {
        $this->sortDirection = $direction;
        return $this;
    }
    
    protected function setTableClass(string $class): self
    {
        $this->tableClass = $class;
        return $this;
    }
    
    protected function setDateFormat(string $format): self
    {
        $this->dateFormat = $format;
        return $this;
    }
    
    protected function setExportEnabled(bool $enabled): self
    {
        $this->exportEnabled = $enabled;
        return $this;
    }
    
    protected function setExportFormats(array $formats): self
    {
        $this->exportFormats = $formats;
        return $this;
    }
    
    public function getColumns(): array
    {
        return $this->columns;
    }
    
    public function isSearchEnabled(): bool
    {
        return $this->searchEnabled;
    }
    
    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }
    
    public function isPaginationEnabled(): bool
    {
        return $this->paginationEnabled;
    }
    
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
    
    public function getItemsPerPageOptions(): array
    {
        return $this->itemsPerPageOptions;
    }
    
    public function getSortField(): ?string
    {
        return $this->sortField;
    }
    
    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }
    
    public function getTableClass(): string
    {
        return $this->tableClass;
    }
    
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }
    
    public function isExportEnabled(): bool
    {
        return $this->exportEnabled;
    }
    
    public function getExportFormats(): array
    {
        return $this->exportFormats;
    }
}
