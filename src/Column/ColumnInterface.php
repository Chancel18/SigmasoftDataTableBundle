<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Column;

interface ColumnInterface
{
    public function getName(): string;
    
    public function getLabel(): string;
    
    public function isSortable(): bool;
    
    public function isSearchable(): bool;
    
    public function render(mixed $value, object $entity): string;
    
    public function getPropertyPath(): string;
    
    public function getOptions(): array;
    
    public function getValue(object $entity): mixed;
}
