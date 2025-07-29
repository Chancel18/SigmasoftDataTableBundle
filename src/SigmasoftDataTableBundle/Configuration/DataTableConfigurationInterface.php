<?php

namespace Sigmasoft\DataTableBundle\Configuration;

interface DataTableConfigurationInterface
{
    public function getEntityClass(): string;
    
    public function getColumns(): array;
    
    public function isSearchEnabled(): bool;
    
    public function getSearchableFields(): array;
    
    public function isPaginationEnabled(): bool;
    
    public function getItemsPerPage(): int;
    
    public function getItemsPerPageOptions(): array;
    
    public function getSortField(): ?string;
    
    public function getSortDirection(): string;
    
    public function getTableClass(): string;
    
    public function getDateFormat(): string;
    
    public function isExportEnabled(): bool;
    
    public function getExportFormats(): array;
}
