<?php

namespace App\Tests\SigmasoftDataTableBundle\Configuration;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Configuration\PhpConfigurator;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Configuration\AbstractDataTableConfiguration;
use Sigmasoft\DataTableBundle\Service\DataTableConfigResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PhpConfiguratorTest extends TestCase
{
    private PhpConfigurator $configurator;
    private DataTableBuilder $builder;
    private DataTableConfiguration $config;
    
    protected function setUp(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $configResolver = $this->createMock(DataTableConfigResolver::class);
        
        $this->builder = new DataTableBuilder($urlGenerator, $configResolver);
        $this->config = new DataTableConfiguration(User::class);
        $this->builder->setCurrentConfig($this->config);
        
        $testConfig = new class extends AbstractDataTableConfiguration {
            public function getEntityClass(): string
            {
                return User::class;
            }
            
            public function configure(): void
            {
                $this->addColumn(new TextColumn('name', 'name', 'Nom'));
                $this->addColumn(new TextColumn('email', 'email', 'Email'));
                $this->setSearchEnabled(true);
                $this->setSearchableFields(['name', 'email']);
                $this->setPaginationEnabled(true);
                $this->setItemsPerPage(20);
                $this->setItemsPerPageOptions([20, 40, 80]);
                $this->setSortField('name');
                $this->setSortDirection('asc');
                $this->setTableClass('table table-test');
                $this->setDateFormat('Y-m-d');
                $this->setExportEnabled(true);
                $this->setExportFormats(['csv', 'pdf']);
            }
        };
        
        $this->configurator = new PhpConfigurator($testConfig);
    }
    
    public function testConfigureColumns(): void
    {
        $this->configurator->configure($this->builder);
        
        $columns = $this->config->getColumns();
        $this->assertCount(2, $columns);
        $this->assertArrayHasKey('name', $columns);
        $this->assertArrayHasKey('email', $columns);
    }
    
    public function testConfigureSearch(): void
    {
        $this->configurator->configure($this->builder);
        
        $this->assertTrue($this->config->isSearchEnabled());
        $this->assertEquals(['name', 'email'], $this->config->getSearchFields());
    }
    
    public function testConfigurePagination(): void
    {
        $this->configurator->configure($this->builder);
        
        $this->assertTrue($this->config->isPaginationEnabled());
        $this->assertEquals(20, $this->config->getItemsPerPage());
        $this->assertEquals([20, 40, 80], $this->config->getItemsPerPageOptions());
    }
    
    public function testConfigureSorting(): void
    {
        $this->configurator->configure($this->builder);
        
        $this->assertEquals('name', $this->config->getSortField());
        $this->assertEquals('asc', $this->config->getSortDirection());
    }
    
    public function testConfigureTableProperties(): void
    {
        $this->configurator->configure($this->builder);
        
        $this->assertEquals('table table-test', $this->config->getTableClass());
        $this->assertEquals('Y-m-d', $this->config->getDateFormat());
    }
    
    public function testConfigureExport(): void
    {
        $this->configurator->configure($this->builder);
        
        $this->assertTrue($this->config->isExportEnabled());
        $this->assertEquals(['csv', 'pdf'], $this->config->getExportFormats());
    }
}
