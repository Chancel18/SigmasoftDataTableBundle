<?php

namespace Sigmasoft\DataTableBundle\Configuration;

use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Column\ColumnInterface;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Column\DateColumn;
use Sigmasoft\DataTableBundle\Column\BadgeColumn;
use Sigmasoft\DataTableBundle\Column\ActionColumn;
use Sigmasoft\DataTableBundle\Exception\DataTableException;
use Symfony\Component\Yaml\Yaml;

class YamlConfigurator implements ConfiguratorInterface
{
    private array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public static function fromFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new DataTableException("Configuration file not found: {$filePath}");
        }
        
        try {
            $config = Yaml::parseFile($filePath);
        } catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
            throw new DataTableException("Invalid YAML configuration: " . $e->getMessage());
        }
        
        if (!isset($config['datatable'])) {
            throw new DataTableException("Invalid configuration: 'datatable' key not found");
        }
        
        return new self($config['datatable']);
    }
    
    public function configure(DataTableBuilder $builder): void
    {
        if (isset($this->config['columns'])) {
            foreach ($this->config['columns'] as $columnConfig) {
                $column = $this->createColumn($columnConfig);
                $builder->addColumn($column);
            }
        }
        
        if (isset($this->config['search'])) {
            $searchConfig = $this->config['search'];
            $builder->configureSearch(
                $searchConfig['enabled'] ?? true,
                $searchConfig['fields'] ?? []
            );
        }
        
        if (isset($this->config['pagination'])) {
            $paginationConfig = $this->config['pagination'];
            $builder->configurePagination(
                $paginationConfig['enabled'] ?? true,
                $paginationConfig['items_per_page'] ?? 10,
                $paginationConfig['items_per_page_options'] ?? [10, 25, 50, 100]
            );
        }
        
        if (isset($this->config['sorting'])) {
            $sortingConfig = $this->config['sorting'];
            $builder->configureSorting(
                $sortingConfig['field'] ?? null,
                $sortingConfig['direction'] ?? 'ASC'
            );
        }
        
        if (isset($this->config['table_class'])) {
            $builder->setTableClass($this->config['table_class']);
        }
        
        if (isset($this->config['date_format'])) {
            $builder->setDateFormat($this->config['date_format']);
        }
        
        if (isset($this->config['export'])) {
            $exportConfig = $this->config['export'];
            if ($exportConfig['enabled'] ?? false) {
                $builder->enableExport($exportConfig['formats'] ?? ['csv']);
            }
        }
    }
    
    private function createColumn(array $columnConfig): ColumnInterface
    {
        $type = $columnConfig['type'] ?? 'text';
        $field = $columnConfig['field'];
        $property = $columnConfig['property'];
        $label = $columnConfig['label'];
        $sortable = $columnConfig['sortable'] ?? true; 
        $searchable = $columnConfig['searchable'] ?? ($type === 'text');
        $options = $columnConfig['options'] ?? [];
        
        switch ($type) {
            case 'text':
                return new TextColumn($field, $property, $label, $sortable, $searchable, $options);
                
            case 'date':
                $format = $columnConfig['format'] ?? 'd/m/Y';
                $options['format'] = $format;
                return new DateColumn($field, $property, $label, $sortable, false, $options);
                
            case 'badge':
                return new BadgeColumn($field, $property, $label, $sortable, false, $options);
                
            case 'action':
                // ActionColumn should be handled via DataTableBuilder::addActionColumn() 
                // since it requires UrlGeneratorInterface which YamlConfigurator doesn't have
                throw new DataTableException("ActionColumn should be configured using addActionColumn() method in DataTableBuilder, not via YAML");
                
            default:
                throw new DataTableException("Unknown column type: {$type}");
        }
    }
}
