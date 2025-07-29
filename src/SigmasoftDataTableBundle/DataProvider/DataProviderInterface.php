<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\DataProvider;

use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Knp\Component\Pager\Pagination\PaginationInterface;

interface DataProviderInterface
{
    public function getData(DataTableConfiguration $configuration): PaginationInterface;
    
    public function getTotalCount(DataTableConfiguration $configuration): int;
    
    public function supports(string $entityClass): bool;
}
