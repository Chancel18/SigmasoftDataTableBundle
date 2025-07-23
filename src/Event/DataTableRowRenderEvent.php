<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

class DataTableRowRenderEvent extends DataTableEvent
{
    public const NAME = 'sigmasoft_datatable.row_render';

    public function __construct(
        string $entityClass,
        private object $entity,
        private array $rowData,
        private array $rowAttributes = [],
        array $context = []
    ) {
        parent::__construct($entityClass, $context);
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getRowData(): array
    {
        return $this->rowData;
    }

    public function setRowData(array $rowData): void
    {
        $this->rowData = $rowData;
    }

    public function getRowAttributes(): array
    {
        return $this->rowAttributes;
    }

    public function setRowAttributes(array $attributes): void
    {
        $this->rowAttributes = $attributes;
    }

    public function addRowAttribute(string $key, string $value): void
    {
        $this->rowAttributes[$key] = $value;
    }
}