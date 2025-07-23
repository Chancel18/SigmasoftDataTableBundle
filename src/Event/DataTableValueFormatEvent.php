<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

/**
 * Événement déclenché avant le formatage d'une valeur
 * Permet de modifier la valeur avant son formatage final
 */
class DataTableValueFormatEvent extends DataTableEvent
{
    public const NAME = 'sigmasoft_datatable.value_format';

    public function __construct(
        string $entityClass,
        private readonly object $entity,
        private readonly string $field,
        private mixed $value,
        private array $formatConfig,
        array $context = []
    ) {
        parent::__construct($entityClass, $context);
    }

    /**
     * Retourne l'entité concernée
     */
    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * Retourne le nom du champ
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Retourne la valeur à formater
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Modifie la valeur à formater
     */
    public function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Retourne la configuration de formatage
     */
    public function getFormatConfig(): array
    {
        return $this->formatConfig;
    }

    /**
     * Modifie la configuration de formatage
     */
    public function setFormatConfig(array $formatConfig): self
    {
        $this->formatConfig = $formatConfig;
        return $this;
    }

    /**
     * Ajoute ou modifie une option de formatage
     */
    public function setFormatOption(string $key, mixed $value): self
    {
        $this->formatConfig[$key] = $value;
        return $this;
    }
}
