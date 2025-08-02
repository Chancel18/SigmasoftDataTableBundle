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
        $this->fieldConfig = $fieldConfig ?: EditableFieldConfiguration::create();
        $this->rendererRegistry = $rendererRegistry ?: new FieldRendererRegistry();
        
        // Inclure la configuration du champ dans les options pour la sérialisation
        $options = $this->mergeFieldConfigIntoOptions($options);
        
        parent::__construct($name, $propertyPath, $label, $sortable, $searchable, $options);
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
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->fieldConfig = $this->fieldConfig->placeholder($placeholder);
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function maxLength(int $maxLength): self
    {
        $this->fieldConfig = $this->fieldConfig->maxLength($maxLength);
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function minLength(int $minLength): self
    {
        $this->fieldConfig = $this->fieldConfig->minLength($minLength);
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function pattern(string $pattern): self
    {
        $this->fieldConfig = $this->fieldConfig->pattern($pattern);
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function min(string $min): self
    {
        $this->fieldConfig = $this->fieldConfig->min($min);
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function max(string $max): self
    {
        $this->fieldConfig = $this->fieldConfig->max($max);
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function readonly(bool $readonly = true): self
    {
        $this->fieldConfig = $this->fieldConfig->readonly($readonly);
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->fieldConfig = $this->fieldConfig->disabled($disabled);
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function cssClasses(array $classes): self
    {
        $this->fieldConfig = $this->fieldConfig->cssClasses($classes);
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function dataAttributes(array $attributes): self
    {
        $this->fieldConfig = $this->fieldConfig->dataAttributes($attributes);
        $this->updateOptionsFromFieldConfig();
        return $this;
    }

    public function validationRules(array $rules): self
    {
        $this->fieldConfig = $this->fieldConfig->validationRules($rules);
        $this->updateOptionsFromFieldConfig();
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

    /**
     * Fusionne la configuration du champ dans les options pour la sérialisation
     */
    private function mergeFieldConfigIntoOptions(array $options): array
    {
        // Inclure le type de champ
        $options['field_type'] = $this->fieldConfig->getFieldType();
        
        // Propriétés de base
        $options['field_required'] = $this->fieldConfig->isRequired();
        
        if ($this->fieldConfig->getPlaceholder() !== null) {
            $options['field_placeholder'] = $this->fieldConfig->getPlaceholder();
        }
        
        if ($this->fieldConfig->getMaxLength() !== null) {
            $options['field_max_length'] = $this->fieldConfig->getMaxLength();
        }
        
        if ($this->fieldConfig->getMinLength() !== null) {
            $options['field_min_length'] = $this->fieldConfig->getMinLength();
        }
        
        if ($this->fieldConfig->getPattern() !== null) {
            $options['field_pattern'] = $this->fieldConfig->getPattern();
        }
        
        if ($this->fieldConfig->getMin() !== null) {
            $options['field_min'] = $this->fieldConfig->getMin();
        }
        
        if ($this->fieldConfig->getMax() !== null) {
            $options['field_max'] = $this->fieldConfig->getMax();
        }
        
        if ($this->fieldConfig->getStep() !== null) {
            $options['field_step'] = $this->fieldConfig->getStep();
        }
        
        $options['field_readonly'] = $this->fieldConfig->isReadonly();
        $options['field_disabled'] = $this->fieldConfig->isDisabled();
        
        // Inclure les options du champ si elles existent
        if ($this->fieldConfig->hasOptions()) {
            $options['field_options'] = $this->fieldConfig->getOptions();
        }
        
        // Inclure les règles de validation si elles existent
        $validationRules = $this->fieldConfig->getValidationRules();
        if (!empty($validationRules)) {
            $options['validation_rules'] = $validationRules;
        }
        
        // Inclure les classes CSS si elles existent
        $cssClasses = $this->fieldConfig->getCssClasses();
        if (!empty($cssClasses)) {
            $options['field_css_classes'] = $cssClasses;
        }
        
        // Inclure les attributs de données si ils existent
        $dataAttributes = $this->fieldConfig->getDataAttributes();
        if (!empty($dataAttributes)) {
            $options['data_attributes'] = $dataAttributes;
        }
        
        return $options;
    }

    /**
     * Met à jour les options à partir de la configuration actuelle du champ
     */
    private function updateOptionsFromFieldConfig(): void
    {
        $this->options = $this->mergeFieldConfigIntoOptions($this->options);
    }
}
