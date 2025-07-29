<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Column;

use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;
use Sigmasoft\DataTableBundle\InlineEdit\Renderer\FieldRendererRegistry;

/**
 * Version améliorée de EditableColumn
 * Architecture modulaire et séparation claire des responsabilités
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 */
final class EditableColumnV2 extends AbstractColumn
{
    private EditableFieldConfiguration $fieldConfig;
    private FieldRendererRegistry $rendererRegistry;

    public function __construct(
        string $name,
        string $propertyPath,
        string $label = '',
        EditableFieldConfiguration $fieldConfig = null,
        bool $sortable = true,
        bool $searchable = true,
        array $options = [],
        ?FieldRendererRegistry $rendererRegistry = null
    ) {
        parent::__construct($name, $propertyPath, $label, $sortable, $searchable, $options);
        
        $this->fieldConfig = $fieldConfig ?: EditableFieldConfiguration::create();
        $this->rendererRegistry = $rendererRegistry ?: new FieldRendererRegistry();
    }

    /**
     * Factory method pour une création simplifiée
     */
    public static function text(
        string $name,
        string $propertyPath,
        string $label = ''
    ): self {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_TEXT);
        return new self($name, $propertyPath, $label, $config);
    }

    /**
     * Factory method pour un champ email
     */
    public static function email(
        string $name,
        string $propertyPath,
        string $label = ''
    ): self {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_EMAIL);
        return new self($name, $propertyPath, $label, $config);
    }

    /**
     * Factory method pour un champ number
     */
    public static function number(
        string $name,
        string $propertyPath,
        string $label = ''
    ): self {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_NUMBER);
        return new self($name, $propertyPath, $label, $config);
    }

    /**
     * Factory method pour un select
     */
    public static function select(
        string $name,
        string $propertyPath,
        string $label = '',
        array $options = []
    ): self {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_SELECT)
            ->options($options);
        return new self($name, $propertyPath, $label, $config);
    }

    /**
     * Factory method pour un textarea
     */
    public static function textarea(
        string $name,
        string $propertyPath,
        string $label = '',
        int $rows = 3
    ): self {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_TEXTAREA)
            ->dataAttributes(['rows' => $rows]);
        return new self($name, $propertyPath, $label, $config);
    }

    /**
     * API fluide pour la configuration
     */
    public function required(bool $required = true): self
    {
        $this->fieldConfig = $this->fieldConfig->required($required);
        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->fieldConfig = $this->fieldConfig->placeholder($placeholder);
        return $this;
    }

    public function maxLength(int $maxLength): self
    {
        $this->fieldConfig = $this->fieldConfig->maxLength($maxLength);
        return $this;
    }

    public function minLength(int $minLength): self
    {
        $this->fieldConfig = $this->fieldConfig->minLength($minLength);
        return $this;
    }

    public function pattern(string $pattern): self
    {
        $this->fieldConfig = $this->fieldConfig->pattern($pattern);
        return $this;
    }

    public function min(string $min): self
    {
        $this->fieldConfig = $this->fieldConfig->min($min);
        return $this;
    }

    public function max(string $max): self
    {
        $this->fieldConfig = $this->fieldConfig->max($max);
        return $this;
    }

    public function readonly(bool $readonly = true): self
    {
        $this->fieldConfig = $this->fieldConfig->readonly($readonly);
        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->fieldConfig = $this->fieldConfig->disabled($disabled);
        return $this;
    }

    public function cssClasses(array $classes): self
    {
        $this->fieldConfig = $this->fieldConfig->cssClasses($classes);
        return $this;
    }

    public function dataAttributes(array $attributes): self
    {
        $this->fieldConfig = $this->fieldConfig->dataAttributes($attributes);
        return $this;
    }

    public function validationRules(array $rules): self
    {
        $this->fieldConfig = $this->fieldConfig->validationRules($rules);
        return $this;
    }

    /**
     * Méthode de rendu simplifiée - délègue au registry
     */
    protected function doRender(mixed $value, object $entity): string
    {
        try {
            return $this->rendererRegistry->renderField(
                $this->fieldConfig,
                $value,
                $entity,
                $this->name
            );
        } catch (\Exception $e) {
            // Failsafe - afficher une version non-éditable en cas d'erreur
            return sprintf(
                '<span class="text-muted" title="Erreur de rendu: %s">%s</span>',
                htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
            );
        }
    }

    /**
     * Accès à la configuration du champ
     */
    public function getFieldConfiguration(): EditableFieldConfiguration
    {
        return $this->fieldConfig;
    }

    /**
     * Permet d'injecter un registry personnalisé (pour les tests)
     */
    public function setRendererRegistry(FieldRendererRegistry $registry): self
    {
        $this->rendererRegistry = $registry;
        return $this;
    }
}
