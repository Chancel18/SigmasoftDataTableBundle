<?php

namespace Sigmasoft\DataTableBundle\Configuration;

use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;

class PhpConfigurator implements ConfiguratorInterface
{
    private DataTableConfigurationInterface $configuration;
    
    public function __construct(DataTableConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }
    
    public function configure(DataTableBuilder $builder): void
    {
        foreach ($this->configuration->getColumns() as $column) {
            $builder->addColumn($column);
        }
        
        $builder->configureSearch(
            $this->configuration->isSearchEnabled(),
            $this->configuration->getSearchableFields()
        );
        
        $builder->configurePagination(
            $this->configuration->isPaginationEnabled(),
            $this->configuration->getItemsPerPage(),
            $this->configuration->getItemsPerPageOptions()
        );
        
        if ($this->configuration->getSortField()) {
            $builder->configureSorting(
                $this->configuration->getSortField(),
                $this->configuration->getSortDirection()
            );
        }
        
        $builder->setTableClass($this->configuration->getTableClass());
        $builder->setDateFormat($this->configuration->getDateFormat());
        
        if ($this->configuration->isExportEnabled()) {
            $builder->enableExport($this->configuration->getExportFormats());
        }
    }
}
