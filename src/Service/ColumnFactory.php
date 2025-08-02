<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Sigmasoft\DataTableBundle\Column\ActionColumn;
use Sigmasoft\DataTableBundle\Column\BadgeColumn;
use Sigmasoft\DataTableBundle\Column\ColumnInterface;
use Sigmasoft\DataTableBundle\Column\DateColumn;
use Sigmasoft\DataTableBundle\Column\EditableColumn;
use Sigmasoft\DataTableBundle\Column\EditableColumnV2;
use Sigmasoft\DataTableBundle\Column\NumberColumn;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Configuration\SerializableDataTableConfig;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;
use Sigmasoft\DataTableBundle\InlineEdit\Renderer\FieldRendererRegistry;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ColumnFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FieldRendererRegistry $rendererRegistry
    ) {
    }

    public function createColumnFromDefinition(array $definition): ColumnInterface
    {
        $type = $definition['type'];
        $name = $definition['name'];
        $propertyPath = $definition['property_path'];
        $label = $definition['label'];
        $sortable = $definition['sortable'];
        $searchable = $definition['searchable'];
        $options = $definition['options'];

        return match ($type) {
            TextColumn::class => new TextColumn($name, $propertyPath, $label, $sortable, $searchable, $options),
            DateColumn::class => new DateColumn($name, $propertyPath, $label, $sortable, $searchable, $options),
            BadgeColumn::class => new BadgeColumn($name, $propertyPath, $label, $sortable, $searchable, $options),
            NumberColumn::class => new NumberColumn($name, $propertyPath, $label, $options),
            EditableColumn::class => new EditableColumn($name, $propertyPath, $label, $sortable, $searchable, $options),
            EditableColumnV2::class => new EditableColumnV2(
                $name,
                $propertyPath,
                $label,
                $this->createEditableFieldConfiguration($options),
                $sortable,
                $searchable,
                $options,
                $this->rendererRegistry
            ),
            ActionColumn::class => new ActionColumn(
                $this->urlGenerator,
                $name,
                $label,
                $options['actions'] ?? [],
                $options
            ),
            default => throw new \InvalidArgumentException("Unknown column type: {$type}")
        };
    }

    private function createEditableFieldConfiguration(array $options): EditableFieldConfiguration
    {
        $fieldType = $options['field_type'] ?? EditableFieldConfiguration::FIELD_TYPE_TEXT;
        $config = EditableFieldConfiguration::create($fieldType);

        // Propriétés de base
        if (isset($options['field_required'])) {
            $config = $config->required((bool) $options['field_required']);
        }

        if (isset($options['field_placeholder'])) {
            $config = $config->placeholder($options['field_placeholder']);
        }

        if (isset($options['field_max_length'])) {
            $config = $config->maxLength((int) $options['field_max_length']);
        }

        if (isset($options['field_min_length'])) {
            $config = $config->minLength((int) $options['field_min_length']);
        }

        if (isset($options['field_pattern'])) {
            $config = $config->pattern($options['field_pattern']);
        }

        if (isset($options['field_min'])) {
            $config = $config->min($options['field_min']);
        }

        if (isset($options['field_max'])) {
            $config = $config->max($options['field_max']);
        }

        if (isset($options['field_step'])) {
            $config = $config->step($options['field_step']);
        }

        if (isset($options['field_readonly'])) {
            $config = $config->readonly((bool) $options['field_readonly']);
        }

        if (isset($options['field_disabled'])) {
            $config = $config->disabled((bool) $options['field_disabled']);
        }

        // Options spécifiques (pour select, radio, etc.)
        if (isset($options['field_options'])) {
            $config = $config->options($options['field_options']);
        }

        // Règles de validation
        if (isset($options['validation_rules'])) {
            $config = $config->validationRules($options['validation_rules']);
        }

        // Classes CSS
        if (isset($options['field_css_classes'])) {
            $config = $config->cssClasses($options['field_css_classes']);
        }

        // Attributs de données
        if (isset($options['data_attributes'])) {
            $config = $config->dataAttributes($options['data_attributes']);
        }

        return $config;
    }

    public function reconstructConfiguration(
        SerializableDataTableConfig $serializableConfig,
        DataTableRegistryInterface $registry
    ): DataTableConfiguration {
        // Essayer de récupérer la configuration originale du registry
        try {
            $originalConfig = $registry->get($serializableConfig->configId);
            
            // Mettre à jour avec les valeurs sérialisées
            $originalConfig
                ->setFilters($serializableConfig->filters)
                ->setSortField($serializableConfig->sortField)
                ->setSortDirection($serializableConfig->sortDirection)
                ->setPage($serializableConfig->page)
                ->setItemsPerPage($serializableConfig->itemsPerPage)
                ->setSearchQuery($serializableConfig->searchQuery);
            
            return $originalConfig;
        } catch (\Exception $e) {
            // Si la configuration n'est pas trouvée dans le registry (nouvelle requête)
            // Reconstruire à partir des données sérialisées
            $config = $serializableConfig->createMutableConfig();
            
            foreach ($serializableConfig->columnDefinitions as $definition) {
                $column = $this->createColumnFromDefinition($definition);
                $config->addColumn($column);
            }
            
            // Re-enregistrer dans le registry pour les prochaines actions
            $registry->register($serializableConfig->configId, $config);
            
            return $config;
        }
    }
}
