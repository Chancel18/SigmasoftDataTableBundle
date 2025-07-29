<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;

interface DataTableRegistryInterface
{
    public function register(string $id, DataTableConfiguration $configuration): void;
    
    public function get(string $id): ?DataTableConfiguration;
    
    public function has(string $id): bool;
    
    public function generateId(): string;
}
