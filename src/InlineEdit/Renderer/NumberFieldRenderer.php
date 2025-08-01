<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

use Sigmasoft\DataTableBundle\Column\NumberColumn;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Renderer pour l'édition inline des champs numériques
 * 
 * Fonctionnalités :
 * - Input HTML5 de type number
 * - Validation côté client des nombres
 * - Support des min/max et step
 * - Formatage automatique lors de la saisie
 * - Conversion des formats localisés
 */
class NumberFieldRenderer extends AbstractFieldRenderer
{
    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        parent::__construct($propertyAccessor);
    }

    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->getFieldType() === 'number';
    }

    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        $fieldId = sprintf('number-field-%s-%s', $fieldName, $this->getEntityId($entity));
        $numericValue = $this->convertToNumericValue($value);
        
        // Récupération des options numériques
        $dataAttributes = $config->getDataAttributes();
        $min = $dataAttributes['min'] ?? null;
        $max = $dataAttributes['max'] ?? null;
        $step = $dataAttributes['step'] ?? 'any';
        $decimals = $dataAttributes['decimals'] ?? 2;
        $format = $dataAttributes['format'] ?? NumberColumn::FORMAT_DECIMAL;
        $placeholder = $dataAttributes['placeholder'] ?? '';
        
        // Classes CSS
        $classes = ['form-control', 'editable-number-field'];
        if ($config->isRequired()) {
            $classes[] = 'required';
        }
        
        // Attributs HTML5
        $attributes = [
            'type' => 'number',
            'id' => $fieldId,
            'name' => $fieldName,
            'class' => implode(' ', $classes),
            'value' => $numericValue,
            'data-field-type' => 'number',
            'data-format' => $format,
            'data-decimals' => $decimals,
            'step' => $step,
        ];
        
        if ($placeholder) {
            $attributes['placeholder'] = $placeholder;
        }
        
        if ($min !== null) {
            $attributes['min'] = $min;
        }
        
        if ($max !== null) {
            $attributes['max'] = $max;
        }
        
        if ($config->isRequired()) {
            $attributes['required'] = 'required';
        }
        
        // Données pour JavaScript
        $jsOptions = [
            'fieldName' => $fieldName,
            'entityId' => $this->getEntityId($entity),
            'format' => $format,
            'decimals' => $decimals,
            'thousandsSeparator' => $dataAttributes['thousands_separator'] ?? ' ',
            'decimalSeparator' => $dataAttributes['decimal_separator'] ?? ',',
            'currency' => $dataAttributes['currency'] ?? null,
            'prefix' => $dataAttributes['prefix'] ?? null,
            'suffix' => $dataAttributes['suffix'] ?? null,
        ];
        
        $attributesString = $this->buildAttributesString($attributes);
        $jsOptionsJson = htmlspecialchars(json_encode($jsOptions), ENT_QUOTES, 'UTF-8');
        
        return sprintf(
            '<div class="editable-number-wrapper" data-config="%s">
                <input %s>
                <div class="invalid-feedback"></div>
                <small class="form-text text-muted number-format-hint" style="display:none;">
                    Format: %s
                </small>
            </div>',
            $jsOptionsJson,
            $attributesString,
            $this->getFormatHint($format, $config)
        );
    }

    public function processValue(EditableFieldConfiguration $config, mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Conversion de la valeur en nombre
        $numericValue = $this->convertToNumericValue($value);
        
        if ($numericValue === null) {
            throw new \InvalidArgumentException('Valeur numérique invalide : ' . $value);
        }

        // Validation des contraintes
        $this->validateConstraints($config, $numericValue);
        
        return $numericValue;
    }

    private function convertToNumericValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float)$value;
        }

        if (is_string($value)) {
            // Nettoyage des séparateurs de milliers et conversion du séparateur décimal
            $cleaned = str_replace([' ', '€', '$', '%'], '', $value);
            $cleaned = str_replace(',', '.', $cleaned);
            
            if (is_numeric($cleaned)) {
                return (float)$cleaned;
            }
        }

        return null;
    }

    private function validateConstraints(EditableFieldConfiguration $config, float $value): void
    {
        $dataAttributes = $config->getDataAttributes();
        $min = $dataAttributes['min'] ?? null;
        $max = $dataAttributes['max'] ?? null;

        if ($min !== null && $value < $min) {
            throw new \InvalidArgumentException(sprintf(
                'La valeur %s est inférieure au minimum autorisé (%s)',
                $value,
                $min
            ));
        }

        if ($max !== null && $value > $max) {
            throw new \InvalidArgumentException(sprintf(
                'La valeur %s est supérieure au maximum autorisé (%s)',
                $value,
                $max
            ));
        }
    }

    private function getFormatHint(string $format, EditableFieldConfiguration $config): string
    {
        switch ($format) {
            case NumberColumn::FORMAT_INTEGER:
                return 'Nombre entier';
                
            case NumberColumn::FORMAT_CURRENCY:
                $dataAttributes = $config->getDataAttributes();
                $currency = $dataAttributes['currency'] ?? 'EUR';
                return sprintf('Montant en %s', $currency);
                
            case NumberColumn::FORMAT_PERCENTAGE:
                return 'Pourcentage (0-100)';
                
            case NumberColumn::FORMAT_DECIMAL:
                $dataAttributes = $config->getDataAttributes();
                $decimals = $dataAttributes['decimals'] ?? 2;
                return sprintf('Nombre décimal (%d décimales)', $decimals);
                
            default:
                return 'Nombre';
        }
    }

    protected function buildAttributesString(array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            
            if ($value === true) {
                $parts[] = $key;
            } else {
                $parts[] = sprintf('%s="%s"', $key, htmlspecialchars((string)$value, ENT_QUOTES));
            }
        }
        
        return implode(' ', $parts);
    }

    public function getValidationRules(EditableFieldConfiguration $config): array
    {
        $rules = [];
        
        // Règles spécifiques aux nombres
        $rules['numeric'] = true;
        $rules['required'] = $config->isRequired();
        
        $dataAttributes = $config->getDataAttributes();
        $min = $dataAttributes['min'] ?? null;
        $max = $dataAttributes['max'] ?? null;
        
        if ($min !== null) {
            $rules['min'] = $min;
        }
        
        if ($max !== null) {
            $rules['max'] = $max;
        }
        
        $step = $dataAttributes['step'] ?? null;
        if ($step !== null && $step !== 'any') {
            $rules['step'] = $step;
        }
        
        return $rules;
    }

    public function getJavaScriptInit(): string
    {
        return <<<'JS'
// Initialisation des champs numériques éditables
document.addEventListener('DOMContentLoaded', function() {
    const numberFields = document.querySelectorAll('.editable-number-field');
    
    numberFields.forEach(field => {
        // Formatage à la perte de focus
        field.addEventListener('blur', function() {
            const wrapper = this.closest('.editable-number-wrapper');
            const config = JSON.parse(wrapper.dataset.config || '{}');
            
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(config.decimals || 2);
            }
        });
        
        // Validation en temps réel
        field.addEventListener('input', function() {
            const value = parseFloat(this.value);
            const min = parseFloat(this.min);
            const max = parseFloat(this.max);
            
            this.classList.remove('is-invalid');
            
            if (isNaN(value)) {
                return;
            }
            
            if (!isNaN(min) && value < min) {
                this.classList.add('is-invalid');
                this.nextElementSibling.textContent = `Valeur minimale : ${min}`;
            } else if (!isNaN(max) && value > max) {
                this.classList.add('is-invalid');
                this.nextElementSibling.textContent = `Valeur maximale : ${max}`;
            }
        });
        
        // Affichage de l'aide au format au focus
        field.addEventListener('focus', function() {
            const hint = this.parentElement.querySelector('.number-format-hint');
            if (hint) {
                hint.style.display = 'block';
            }
        });
        
        field.addEventListener('blur', function() {
            const hint = this.parentElement.querySelector('.number-format-hint');
            if (hint) {
                hint.style.display = 'none';
            }
        });
    });
});
JS;
    }
    
    private function getEntityId(object $entity): mixed
    {
        return $this->propertyAccessor->getValue($entity, 'id');
    }
}