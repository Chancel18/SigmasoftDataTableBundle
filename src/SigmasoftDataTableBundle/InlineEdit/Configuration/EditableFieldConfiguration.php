<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Configuration;

/**
 * Configuration pour un champ éditable
 * Sépare la configuration du rendu pour une meilleure maintenabilité
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 */
class EditableFieldConfiguration
{
    public const FIELD_TYPE_TEXT = 'text';
    public const FIELD_TYPE_EMAIL = 'email';
    public const FIELD_TYPE_NUMBER = 'number';
    public const FIELD_TYPE_PASSWORD = 'password';
    public const FIELD_TYPE_URL = 'url';
    public const FIELD_TYPE_TEL = 'tel';
    public const FIELD_TYPE_SELECT = 'select';
    public const FIELD_TYPE_TEXTAREA = 'textarea';
    public const FIELD_TYPE_DATE = 'date';
    public const FIELD_TYPE_DATETIME = 'datetime-local';
    public const FIELD_TYPE_TIME = 'time';
    public const FIELD_TYPE_CHECKBOX = 'checkbox';
    public const FIELD_TYPE_RADIO = 'radio';
    public const FIELD_TYPE_COLOR = 'color';

    private array $validFieldTypes = [
        self::FIELD_TYPE_TEXT,
        self::FIELD_TYPE_EMAIL,
        self::FIELD_TYPE_NUMBER,
        self::FIELD_TYPE_PASSWORD,
        self::FIELD_TYPE_URL,
        self::FIELD_TYPE_TEL,
        self::FIELD_TYPE_SELECT,
        self::FIELD_TYPE_TEXTAREA,
        self::FIELD_TYPE_DATE,
        self::FIELD_TYPE_DATETIME,
        self::FIELD_TYPE_TIME,
        self::FIELD_TYPE_CHECKBOX,
        self::FIELD_TYPE_RADIO,
        self::FIELD_TYPE_COLOR,
    ];

    public function __construct(
        private string $fieldType = self::FIELD_TYPE_TEXT,
        private bool $required = false,
        private ?string $placeholder = null,
        private ?int $maxLength = null,
        private ?int $minLength = null,
        private ?string $pattern = null,
        private ?array $options = null,
        private ?string $min = null,
        private ?string $max = null,
        private ?string $step = null,
        private bool $readonly = false,
        private bool $disabled = false,
        private array $validationRules = [],
        private array $cssClasses = [],
        private array $dataAttributes = []
    ) {
        $this->validateFieldType($fieldType);
    }

    private function validateFieldType(string $fieldType): void
    {
        if (!in_array($fieldType, $this->validFieldTypes, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid field type "%s". Valid types: %s', 
                    $fieldType, 
                    implode(', ', $this->validFieldTypes)
                )
            );
        }
    }

    public function getFieldType(): string
    {
        return $this->fieldType;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function getMin(): ?string
    {
        return $this->min;
    }

    public function getMax(): ?string
    {
        return $this->max;
    }

    public function getStep(): ?string
    {
        return $this->step;
    }

    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getCssClasses(): array
    {
        return $this->cssClasses;
    }

    public function getDataAttributes(): array
    {
        return $this->dataAttributes;
    }

    public function hasOptions(): bool
    {
        return !empty($this->options);
    }

    public function isSelectType(): bool
    {
        return $this->fieldType === self::FIELD_TYPE_SELECT;
    }

    public function isTextAreaType(): bool
    {
        return $this->fieldType === self::FIELD_TYPE_TEXTAREA;
    }

    public function isCheckboxType(): bool
    {
        return $this->fieldType === self::FIELD_TYPE_CHECKBOX;
    }

    public function isRadioType(): bool
    {
        return $this->fieldType === self::FIELD_TYPE_RADIO;
    }

    /**
     * Builder pattern pour une configuration fluide
     */
    public static function create(string $fieldType = self::FIELD_TYPE_TEXT): self
    {
        return new self($fieldType);
    }

    public function required(bool $required = true): self
    {
        $clone = clone $this;
        $clone->required = $required;
        return $clone;
    }

    public function placeholder(string $placeholder): self
    {
        $clone = clone $this;
        $clone->placeholder = $placeholder;
        return $clone;
    }

    public function maxLength(int $maxLength): self
    {
        $clone = clone $this;
        $clone->maxLength = $maxLength;
        return $clone;
    }

    public function minLength(int $minLength): self
    {
        $clone = clone $this;
        $clone->minLength = $minLength;
        return $clone;
    }

    public function pattern(string $pattern): self
    {
        $clone = clone $this;
        $clone->pattern = $pattern;
        return $clone;
    }

    public function options(array $options): self
    {
        $clone = clone $this;
        $clone->options = $options;
        return $clone;
    }

    public function min(string $min): self
    {
        $clone = clone $this;
        $clone->min = $min;
        return $clone;
    }

    public function max(string $max): self
    {
        $clone = clone $this;
        $clone->max = $max;
        return $clone;
    }

    public function step(string $step): self
    {
        $clone = clone $this;
        $clone->step = $step;
        return $clone;
    }

    public function readonly(bool $readonly = true): self
    {
        $clone = clone $this;
        $clone->readonly = $readonly;
        return $clone;
    }

    public function disabled(bool $disabled = true): self
    {
        $clone = clone $this;
        $clone->disabled = $disabled;
        return $clone;
    }

    public function validationRules(array $rules): self
    {
        $clone = clone $this;
        $clone->validationRules = $rules;
        return $clone;
    }

    public function cssClasses(array $classes): self
    {
        $clone = clone $this;
        $clone->cssClasses = $classes;
        return $clone;
    }

    public function dataAttributes(array $attributes): self
    {
        $clone = clone $this;
        $clone->dataAttributes = $attributes;
        return $clone;
    }
}
