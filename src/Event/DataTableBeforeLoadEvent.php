<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

use Doctrine\ORM\QueryBuilder;
use Sigmasoft\DataTableBundle\Model\DataTableRequest;

class DataTableBeforeLoadEvent extends DataTableEvent
{
    public const NAME = 'sigmasoft_datatable.before_load';

    public function __construct(
        string $entityClass,
        private DataTableRequest $request,
        private QueryBuilder $queryBuilder,
        array $context = []
    ) {
        parent::__construct($entityClass, $context);
    }

    public function getRequest(): DataTableRequest
    {
        return $this->request;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }
}
