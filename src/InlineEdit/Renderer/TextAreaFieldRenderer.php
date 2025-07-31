<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

/**
 * Renderer pour les champs textarea
 * Gère les textes longs avec redimensionnement automatique
 */
class TextAreaFieldRenderer extends AbstractFieldRenderer
{
    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->isTextAreaType();
    }

    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        $attributes = $this->generateBaseAttributes($config, $value, $entity, $fieldName);
        
        // Les textarea n'utilisent pas l'attribut value
        $textContent = $attributes['value'] ?? '';
        unset($attributes['value']);

        // Ajouter des attributs spécifiques aux textarea
        $attributes['rows'] = $config->getDataAttributes()['rows'] ?? 3;
        $attributes['cols'] = $config->getDataAttributes()['cols'] ?? null;

        $textareaHtml = sprintf(
            '<textarea %s>%s</textarea>',
            $this->attributesToString($attributes),
            htmlspecialchars($textContent, ENT_QUOTES, 'UTF-8')
        );
        
        return $this->wrapWithIndicators($textareaHtml);
    }

    protected function buildCssClasses(EditableFieldConfiguration $config): string
    {
        $classes = [
            'form-control',
            'form-control-sm', 
            'editable-field',
            'auto-resize' // Classe pour le redimensionnement automatique
        ];

        // Ajouter les classes personnalisées
        $classes = array_merge($classes, $config->getCssClasses());

        return implode(' ', array_unique($classes));
    }

    public function getPriority(): int
    {
        return 12; // Priorité élevée
    }
}
