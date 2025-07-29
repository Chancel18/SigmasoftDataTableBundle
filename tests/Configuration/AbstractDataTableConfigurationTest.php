<?php

namespace App\Tests\SigmasoftDataTableBundle\Configuration;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Configuration\AbstractDataTableConfiguration;
use PHPUnit\Framework\TestCase;

class AbstractDataTableConfigurationTest extends TestCase
{
    private TestDataTableConfiguration $configuration;
    
    protected function setUp(): void
    {
        $this->configuration = new TestDataTableConfiguration();
    }
    
    public function testGetEntityClass(): void
    {
        $this->assertEquals(User::class, $this->configuration->getEntityClass());
    }
    
    public function testGetColumns(): void
    {
        $columns = $this->configuration->getColumns();
        
        $this->assertCount(2, $columns);
        $this->assertInstanceOf(TextColumn::class, $columns[0]);
        $this->assertEquals('Nom', $columns[0]->getLabel());
    }
    
    public function testSearchConfiguration(): void
    {
        $this->assertTrue($this->configuration->isSearchEnabled());
        $this->assertEquals(['name', 'email'], $this->configuration->getSearchableFields());
    }
    
    public function testPaginationConfiguration(): void
    {
        $this->assertTrue($this->configuration->isPaginationEnabled());
        $this->assertEquals(15, $this->configuration->getItemsPerPage());
        $this->assertEquals([15, 30, 60], $this->configuration->getItemsPerPageOptions());
    }
    
    public function testSortConfiguration(): void
    {
        $this->assertEquals('name', $this->configuration->getSortField());
        $this->assertEquals('desc', $this->configuration->getSortDirection());
    }
    
    public function testTableConfiguration(): void
    {
        $this->assertEquals('table table-custom', $this->configuration->getTableClass());
        $this->assertEquals('d/m/Y H:i', $this->configuration->getDateFormat());
    }
    
    public function testExportConfiguration(): void
    {
        $this->assertTrue($this->configuration->isExportEnabled());
        $this->assertEquals(['csv', 'excel'], $this->configuration->getExportFormats());
    }
}

class TestDataTableConfiguration extends AbstractDataTableConfiguration
{
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
        $this->setItemsPerPage(15);
        $this->setItemsPerPageOptions([15, 30, 60]);
        $this->setSortField('name');
        $this->setSortDirection('desc');
        $this->setTableClass('table table-custom');
        $this->setDateFormat('d/m/Y H:i');
        $this->setExportEnabled(true);
        $this->setExportFormats(['csv', 'excel']);
    }
}
