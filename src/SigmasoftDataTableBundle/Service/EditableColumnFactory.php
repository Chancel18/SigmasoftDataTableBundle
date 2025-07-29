<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Sigmasoft\DataTableBundle\Column\EditableColumnV2;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;
use Sigmasoft\DataTableBundle\InlineEdit\Renderer\FieldRendererRegistry;

/**
 * Factory pour créer des colonnes éditables avec injection de dépendances
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 */
class EditableColumnFactory
{
    public function __construct(
        private FieldRendererRegistry $rendererRegistry
    ) {}

    public function text(
        string $name,
        string $propertyPath,
        string $label = ''
    ): EditableColumnV2 {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_TEXT);
        return new EditableColumnV2($name, $propertyPath, $label, $config, true, true, [], $this->rendererRegistry);
    }

    public function email(
        string $name,
        string $propertyPath,
        string $label = ''
    ): EditableColumnV2 {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_EMAIL);
        return new EditableColumnV2($name, $propertyPath, $label, $config, true, true, [], $this->rendererRegistry);
    }

    public function number(
        string $name,
        string $propertyPath,
        string $label = ''
    ): EditableColumnV2 {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_NUMBER);
        return new EditableColumnV2($name, $propertyPath, $label, $config, true, true, [], $this->rendererRegistry);
    }

    public function select(
        string $name,
        string $propertyPath,
        string $label = '',
        array $options = []
    ): EditableColumnV2 {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_SELECT)
            ->options($options);
        return new EditableColumnV2($name, $propertyPath, $label, $config, true, true, [], $this->rendererRegistry);
    }

    public function textarea(
        string $name,
        string $propertyPath,
        string $label = '',
        int $rows = 3
    ): EditableColumnV2 {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_TEXTAREA)
            ->dataAttributes(['rows' => $rows]);
        return new EditableColumnV2($name, $propertyPath, $label, $config, true, true, [], $this->rendererRegistry);
    }

    public function color(
        string $name,
        string $propertyPath,
        string $label = '',
        bool $showPresets = true
    ): EditableColumnV2 {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR)
            ->dataAttributes(['show_presets' => $showPresets]);
        return new EditableColumnV2($name, $propertyPath, $label, $config, true, true, [], $this->rendererRegistry);
    }

    public function create(
        string $name,
        string $propertyPath,
        string $label = '',
        EditableFieldConfiguration $fieldConfig = null
    ): EditableColumnV2 {
        return new EditableColumnV2($name, $propertyPath, $label, $fieldConfig, true, true, [], $this->rendererRegistry);
    }
}
