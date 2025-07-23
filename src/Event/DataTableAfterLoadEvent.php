<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

use Sigmasoft\DataTableBundle\Model\DataTableRequest;
use Sigmasoft\DataTableBundle\Model\DataTableResult;

class DataTableAfterLoadEvent extends DataTableEvent
{
    public const NAME = 'sigmasoft_datatable.after_load';

    public function __construct(
        string $entityClass,
        private DataTableRequest $request,
        private DataTableResult $result,
        array $context = []
    ) {
        parent::__construct($entityClass, $context);
    }

    public function getRequest(): DataTableRequest
    {
        return $this->request;
    }

    public function getResult(): DataTableResult
    {
        return $this->result;
    }

    public function setResult(DataTableResult $result): void
    {
        $this->result = $result;
    }
}
