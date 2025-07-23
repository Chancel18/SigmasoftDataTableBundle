<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

/**
 * Événement déclenché lors de l'exécution d'une action groupée personnalisée
 */
class BulkActionEvent extends DataTableEvent
{
    public const NAME = 'sigmasoft_datatable.bulk_action';

    private bool $success = false;
    private ?string $errorMessage = null;

    public function __construct(
        string $entityClass,
        private readonly string $action,
        private readonly object $entity,
        private readonly array $actionConfig = [],
        array $context = []
    ) {
        parent::__construct($entityClass, $context);
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getActionConfig(): array
    {
        return $this->actionConfig;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }
}