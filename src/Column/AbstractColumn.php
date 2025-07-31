<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Column;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

abstract class AbstractColumn implements ColumnInterface
{
    protected PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        protected string $name,
        protected string $propertyPath,
        protected string $label = '',
        protected bool $sortable = true,
        protected bool $searchable = true,
        protected array $options = []
    ) {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->label = $label ?: ucfirst($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getPropertyPath(): string
    {
        return $this->propertyPath;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function hasOption(string $key): bool
    {
        return array_key_exists($key, $this->options);
    }

    public function getValue(object $entity): mixed
    {
        try {
            return $this->propertyAccessor->getValue($entity, $this->propertyPath);
        } catch (\Exception) {
            return null;
        }
    }

    public function render(mixed $value, object $entity): string
    {
        $actualValue = $value ?? $this->getValue($entity);
        return $this->doRender($actualValue, $entity);
    }

    abstract protected function doRender(mixed $value, object $entity): string;
}
