<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

/**
 * Renderer pour les champs de type texte (text, email, password, etc.)
 * Modulaire et réutilisable
 */
class TextFieldRenderer extends AbstractFieldRenderer
{
    private const SUPPORTED_TYPES = [
        EditableFieldConfiguration::FIELD_TYPE_TEXT,
        EditableFieldConfiguration::FIELD_TYPE_EMAIL,
        EditableFieldConfiguration::FIELD_TYPE_NUMBER,
        EditableFieldConfiguration::FIELD_TYPE_PASSWORD,
        EditableFieldConfiguration::FIELD_TYPE_URL,
        EditableFieldConfiguration::FIELD_TYPE_TEL,
        EditableFieldConfiguration::FIELD_TYPE_DATE,
        EditableFieldConfiguration::FIELD_TYPE_DATETIME,
        EditableFieldConfiguration::FIELD_TYPE_TIME,
    ];

    public function supports(EditableFieldConfiguration $config): bool
    {
        return in_array($config->getFieldType(), self::SUPPORTED_TYPES, true);
    }

    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        $attributes = $this->generateBaseAttributes($config, $value, $entity, $fieldName);
        $attributes['type'] = $config->getFieldType();

        $fieldHtml = sprintf('<input %s>', $this->attributesToString($attributes));
        
        return $this->wrapWithIndicators($fieldHtml);
    }

    public function getPriority(): int
    {
        return 10; // Priorité élevée pour les champs de base
    }
}
