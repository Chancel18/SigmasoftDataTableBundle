<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

/**
 * Événement déclenché lors d'une édition inline
 */
class InlineEditEvent extends DataTableEvent
{
    private object $entity;
    private string $field;
    private mixed $oldValue;
    private mixed $newValue;
    private bool $valid = true;
    private array $errors = [];
    
    public function __construct(
        object $entity,
        string $field,
        mixed $oldValue,
        mixed $newValue,
        array $context = []
    ) {
        $entityClass = get_class($entity);
        parent::__construct($entityClass, $context);
        
        $this->entity = $entity;
        $this->field = $field;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }
    
    public function getEntity(): object
    {
        return $this->entity;
    }
    
    public function getField(): string
    {
        return $this->field;
    }
    
    public function getOldValue(): mixed
    {
        return $this->oldValue;
    }
    
    public function getNewValue(): mixed
    {
        return $this->newValue;
    }
    
    public function setNewValue(mixed $newValue): self
    {
        $this->newValue = $newValue;
        return $this;
    }
    
    public function isValid(): bool
    {
        return $this->valid;
    }
    
    public function setValid(bool $valid): self
    {
        $this->valid = $valid;
        return $this;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function addError(string $error): self
    {
        $this->errors[] = $error;
        $this->valid = false;
        return $this;
    }
    
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        $this->valid = empty($errors);
        return $this;
    }
}