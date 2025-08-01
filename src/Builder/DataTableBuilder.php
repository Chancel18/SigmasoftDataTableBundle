<?php

/**
 * SigmasoftDataTableBundle - Bundle Symfony pour tables de données interactives
 * 
 * @author Gédéon MAKELA <g.makela@sigmasoft-solution.com>
 * @copyright 2025 Sigmasoft Solutions
 * @license MIT
 * @package Sigmasoft\DataTableBundle
 * @version 2.0.5
 * @link https://github.com/Chancel18/SigmasoftDataTableBundle
 * @support support@sigmasoft-solution.com
 */

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Builder;

use Sigmasoft\DataTableBundle\Column\ActionColumn;
use Sigmasoft\DataTableBundle\Column\BadgeColumn;
use Sigmasoft\DataTableBundle\Column\ColumnInterface;
use Sigmasoft\DataTableBundle\Column\DateColumn;
use Sigmasoft\DataTableBundle\Column\NumberColumn;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Service\DataTableConfigResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DataTableBuilder
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private DataTableConfigResolver $configResolver
    ) {
    }

    public function createDataTable(string $entityClass): DataTableConfiguration
    {
        $config = new DataTableConfiguration($entityClass);
        
        // Appliquer les configurations par défaut du bundle
        $defaults = $this->configResolver->getGlobalDefaults();
        if (!empty($defaults)) {
            if (isset($defaults['items_per_page'])) {
                $config->setItemsPerPage($defaults['items_per_page']);
            }
            if (isset($defaults['enable_search'])) {
                $config->enableSearch($defaults['enable_search']);
            }
            if (isset($defaults['enable_pagination'])) {
                $config->enablePagination($defaults['enable_pagination']);
            }
            if (isset($defaults['enable_sorting'])) {
                $config->enableSorting($defaults['enable_sorting']);
            }
            if (isset($defaults['table_class'])) {
                $config->setTableClass($defaults['table_class']);
            }
            if (isset($defaults['date_format'])) {
                $config->setDateFormat($defaults['date_format']);
            }
        }
        
        return $config;
    }
    
    public function createDataTableFromConfig(string $entityClass): DataTableConfiguration
    {
        $config = new DataTableConfiguration($entityClass);
        $this->setCurrentConfig($config);
        $this->configResolver->resolveConfiguration($entityClass, $this);
        return $config;
    }
    
    private DataTableConfiguration $currentConfig;
    
    public function setCurrentConfig(DataTableConfiguration $config): void
    {
        $this->currentConfig = $config;
    }
    
    public function addColumn(ColumnInterface $column): self
    {
        if (isset($this->currentConfig)) {
            $this->currentConfig->addColumn($column);
        }
        return $this;
    }
    
    public function configureSearch(bool $enabled = true, array $fields = []): self
    {
        if (isset($this->currentConfig)) {
            $this->currentConfig->setSearchEnabled($enabled);
            if (!empty($fields)) {
                $this->currentConfig->setSearchFields($fields);
            }
        }
        return $this;
    }
    
    public function configurePagination(bool $enabled = true, int $itemsPerPage = 10, array $itemsPerPageOptions = []): self
    {
        if (isset($this->currentConfig)) {
            $this->currentConfig->setPaginationEnabled($enabled);
            $this->currentConfig->setItemsPerPage($itemsPerPage);
            if (!empty($itemsPerPageOptions)) {
                $this->currentConfig->setItemsPerPageOptions($itemsPerPageOptions);
            }
        }
        return $this;
    }
    
    public function configureSorting(?string $field = null, string $direction = 'ASC'): self
    {
        if (isset($this->currentConfig)) {
            if ($field) {
                $this->currentConfig->setSortField($field);
                $this->currentConfig->setSortDirection($direction);
            }
        }
        return $this;
    }
    
    public function setTableClass(string $class): self
    {
        if (isset($this->currentConfig)) {
            $this->currentConfig->setTableClass($class);
        }
        return $this;
    }
    
    public function setDateFormat(string $format): self
    {
        if (isset($this->currentConfig)) {
            $this->currentConfig->setDateFormat($format);
        }
        return $this;
    }
    
    public function enableExport(array $formats = ['csv']): self
    {
        if (isset($this->currentConfig)) {
            $this->currentConfig->setExportEnabled(true);
            $this->currentConfig->setExportFormats($formats);
        }
        return $this;
    }

    public function addTextColumn(
        DataTableConfiguration $config,
        string $name,
        string $propertyPath = null,
        string $label = '',
        array $options = []
    ): DataTableConfiguration {
        $column = new TextColumn(
            $name,
            $propertyPath ?? $name,
            $label,
            $options['sortable'] ?? true,
            $options['searchable'] ?? true,
            $options
        );

        return $config->addColumn($column);
    }

    public function addDateColumn(
        DataTableConfiguration $config,
        string $name,
        string $propertyPath = null,
        string $label = '',
        array $options = []
    ): DataTableConfiguration {
        $column = new DateColumn(
            $name,
            $propertyPath ?? $name,
            $label,
            $options['sortable'] ?? true,
            $options['searchable'] ?? false,
            $options
        );

        return $config->addColumn($column);
    }

    public function addBadgeColumn(
        DataTableConfiguration $config,
        string $name,
        string $propertyPath = null,
        string $label = '',
        array $options = []
    ): DataTableConfiguration {
        $column = new BadgeColumn(
            $name,
            $propertyPath ?? $name,
            $label,
            $options['sortable'] ?? true,
            $options['searchable'] ?? false,
            $options
        );

        return $config->addColumn($column);
    }

    public function addNumberColumn(
        DataTableConfiguration $config,
        string $name,
        string $propertyPath = null,
        string $label = '',
        array $options = []
    ): DataTableConfiguration {
        $column = new NumberColumn(
            $name,
            $propertyPath ?? $name,
            $label,
            $options
        );

        return $config->addColumn($column);
    }

    public function addActionColumn(
        DataTableConfiguration $config,
        array $actions = [],
        string $label = 'Actions'
    ): DataTableConfiguration {
        $column = new ActionColumn(
            $this->urlGenerator,
            'actions',
            $label,
            $actions
        );

        return $config->addColumn($column);
    }

    public function addCustomColumn(DataTableConfiguration $config, ColumnInterface $column): DataTableConfiguration
    {
        return $config->addColumn($column);
    }

    // Méthodes de compatibilité avec l'ancienne API
    public function configureSearchLegacy(DataTableConfiguration $config, bool $enabled = true, array $fields = []): DataTableConfiguration
    {
        $config->setSearchEnabled($enabled);
        if (!empty($fields)) {
            $config->setSearchFields($fields);
        }
        return $config;
    }

    public function configurePaginationLegacy(DataTableConfiguration $config, bool $enabled = true, int $itemsPerPage = 10): DataTableConfiguration
    {
        $config->setPaginationEnabled($enabled);
        $config->setItemsPerPage($itemsPerPage);
        return $config;
    }

    public function configureSortingLegacy(DataTableConfiguration $config, bool $enabled = true, string $defaultField = '', string $defaultDirection = 'asc'): DataTableConfiguration
    {
        $config->setSortingEnabled($enabled);
        if ($defaultField !== '') {
            $config->setSortField($defaultField);
            $config->setSortDirection($defaultDirection);
        }
        return $config;
    }

    public function setTableClassLegacy(DataTableConfiguration $config, string $class): DataTableConfiguration
    {
        $config->setTableClass($class);
        return $config;
    }
}
