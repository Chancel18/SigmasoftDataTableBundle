<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

class DataTableBeforeDeleteEvent extends DataTableEvent
{
    public const NAME = 'sigmasoft_datatable.before_delete';

    private bool $preventDefault = false;
    private ?string $errorMessage = null;

    public function __construct(
        string $entityClass,
        private object $entity,
        private int $entityId,
        array $context = []
    ) {
        parent::__construct($entityClass, $context);
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function preventDefault(string $errorMessage = null): void
    {
        $this->preventDefault = true;
        $this->errorMessage = $errorMessage;
    }

    public function isPreventDefault(): bool
    {
        return $this->preventDefault;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
}
