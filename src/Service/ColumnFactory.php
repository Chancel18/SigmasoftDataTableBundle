<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Sigmasoft\DataTableBundle\Column\ActionColumn;
use Sigmasoft\DataTableBundle\Column\BadgeColumn;
use Sigmasoft\DataTableBundle\Column\ColumnInterface;
use Sigmasoft\DataTableBundle\Column\DateColumn;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Configuration\SerializableDataTableConfig;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ColumnFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
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
