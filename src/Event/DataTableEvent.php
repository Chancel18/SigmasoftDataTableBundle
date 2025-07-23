<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class DataTableEvent extends Event
{
    public function __construct(
        protected string $entityClass,
        protected array $context = []
    ) {}

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function addContext(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    public function getContextValue(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }
}
