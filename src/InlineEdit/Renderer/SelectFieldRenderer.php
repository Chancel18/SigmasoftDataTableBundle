<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

/**
 * Renderer pour les champs select
 * Gère les options et la sélection multiple
 */
class SelectFieldRenderer extends AbstractFieldRenderer
{
    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->isSelectType();
    }

    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        $attributes = $this->generateBaseAttributes($config, $value, $entity, $fieldName);
        unset($attributes['value']); // Les selects n'ont pas d'attribut value

        $selectHtml = sprintf('<select %s>', $this->attributesToString($attributes));
        
        // Ajouter les options
        if ($config->hasOptions()) {
            foreach ($config->getOptions() as $optionValue => $optionLabel) {
                $selected = $this->isOptionSelected($optionValue, $value) ? ' selected' : '';
                $selectHtml .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    htmlspecialchars((string) $optionValue, ENT_QUOTES, 'UTF-8'),
                    $selected,
                    htmlspecialchars((string) $optionLabel, ENT_QUOTES, 'UTF-8')
                );
            }
        }

        $selectHtml .= '</select>';
        
        return $this->wrapWithIndicators($selectHtml);
    }

    /**
     * Détermine si une option est sélectionnée
     */
    private function isOptionSelected(mixed $optionValue, mixed $currentValue): bool
    {
        // Comparaison loose pour gérer les types différents (string vs int)
        return (string) $optionValue === (string) $currentValue;
    }

    protected function buildCssClasses(EditableFieldConfiguration $config): string
    {
        $classes = [
            'form-select',
            'form-select-sm', 
            'editable-field'
        ];

        // Ajouter les classes personnalisées
        $classes = array_merge($classes, $config->getCssClasses());

        return implode(' ', array_unique($classes));
    }

    public function getPriority(): int
    {
        return 15; // Priorité élevée pour être utilisé avant le TextFieldRenderer
    }
}
