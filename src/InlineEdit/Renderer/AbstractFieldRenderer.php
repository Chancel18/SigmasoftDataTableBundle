<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Classe de base pour les renderers de champs
 * Fournit des utilitaires communs et simplifie l'implémentation
 */
abstract class AbstractFieldRenderer implements FieldRendererInterface
{
    protected PropertyAccessorInterface $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function getPriority(): int
    {
        return 0; // Priorité par défaut
    }

    /**
     * Génère les attributs HTML de base pour un input
     */
    protected function generateBaseAttributes(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): array {
        $entityId = $this->propertyAccessor->getValue($entity, 'id');
        $cleanValue = $this->escapeValue($value);

        $attributes = [
            'class' => $this->buildCssClasses($config),
            'data-entity-id' => $entityId,
            'data-field-name' => $fieldName,
            'data-original-value' => $cleanValue,
        ];

        // Ajouter les attributs de base selon le type
        if (!$config->isCheckboxType() && !$config->isRadioType()) {
            $attributes['value'] = $cleanValue;
        }

        // Attributs de validation
        if ($config->isRequired()) {
            $attributes['required'] = 'required';
        }

        if ($config->getPlaceholder()) {
            $attributes['placeholder'] = $config->getPlaceholder();
        }

        if ($config->getMaxLength()) {
            $attributes['maxlength'] = $config->getMaxLength();
        }

        if ($config->getMinLength()) {
            $attributes['minlength'] = $config->getMinLength();
        }

        if ($config->getPattern()) {
            $attributes['pattern'] = $config->getPattern();
        }

        if ($config->getMin()) {
            $attributes['min'] = $config->getMin();
        }

        if ($config->getMax()) {
            $attributes['max'] = $config->getMax();
        }

        if ($config->getStep()) {
            $attributes['step'] = $config->getStep();
        }

        if ($config->isReadonly()) {
            $attributes['readonly'] = 'readonly';
        }

        if ($config->isDisabled()) {
            $attributes['disabled'] = 'disabled';
        }

        // Ajouter les data attributes personnalisés
        foreach ($config->getDataAttributes() as $key => $val) {
            $attributes["data-{$key}"] = $val;
        }

        return $attributes;
    }

    /**
     * Construit les classes CSS pour le champ
     */
    protected function buildCssClasses(EditableFieldConfiguration $config): string
    {
        $classes = [
            'form-control',
            'form-control-sm', 
            'editable-field'
        ];

        // Ajouter les classes personnalisées
        $classes = array_merge($classes, $config->getCssClasses());

        return implode(' ', array_unique($classes));
    }

    /**
     * Échappe une valeur pour l'affichage HTML
     */
    protected function escapeValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d\TH:i');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Convertit un tableau d'attributs en chaîne HTML
     */
    protected function attributesToString(array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $name => $value) {
            if ($value === null) {
                continue;
            }
            $parts[] = sprintf('%s="%s"', $name, htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
        }
        return implode(' ', $parts);
    }

    /**
     * Alias pour attributesToString (compatibilité)
     */
    protected function buildAttributesString(array $attributes): string
    {
        return $this->attributesToString($attributes);
    }

    /**
     * Génère le wrapper avec indicateurs
     */
    protected function wrapWithIndicators(string $fieldHtml): string
    {
        return sprintf('
            <div class="editable-cell-wrapper position-relative">
                %s
                <div class="saving-indicator d-none position-absolute top-0 end-0 me-1 mt-1">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Sauvegarde...</span>
                    </div>
                </div>
                <div class="error-indicator d-none position-absolute top-0 end-0 me-1 mt-1">
                    <i class="fas fa-exclamation-triangle text-danger" title="Erreur de sauvegarde"></i>
                </div>
            </div>
        ', $fieldHtml);
    }
}
