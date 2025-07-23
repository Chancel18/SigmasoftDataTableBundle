<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Sigmasoft\DataTableBundle\Model\DataTableRequest;
use Sigmasoft\DataTableBundle\Model\DataTableResult;

interface DataTableServiceInterface
{
    public function getData(DataTableRequest $request): DataTableResult;
    public function deleteEntity(string $entityClass, int $id): bool;
    public function getValue(mixed $item, string $field, array $formatConfig): mixed;
    public function findEntity(string $entityClass, int $id): ?object;
    public function executeBulkAction(string $entityClass, string $action, array $ids): array;
    public function exportData(DataTableRequest $request, string $format): array;
}