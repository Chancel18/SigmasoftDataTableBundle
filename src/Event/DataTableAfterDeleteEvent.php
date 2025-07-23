<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

class DataTableAfterDeleteEvent extends DataTableEvent
{
    public const NAME = 'sigmasoft_datatable.after_delete';

    public function __construct(
        string $entityClass,
        private int $entityId,
        private bool $success,
        array $context = []
    ) {
        parent::__construct($entityClass, $context);
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
