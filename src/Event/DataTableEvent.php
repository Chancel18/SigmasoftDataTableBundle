<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Événement de base pour toutes les DataTables
 */
class DataTableEvent extends Event
{
    protected string $entityClass;
    protected array $context = [];
    
    public function __construct(string $entityClass, array $context = [])
    {
        $this->entityClass = $entityClass;
        $this->context = $context;
    }
    
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
    
    public function getContext(): array
    {
        return $this->context;
    }
    
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
    
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }
}