<?php

/**
 * Tests for DataTableBuilder
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package App\Tests\SigmasoftDataTableBundle\Builder
 */

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Builder;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Service\DataTableConfigResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DataTableBuilderTest extends TestCase
{
    private DataTableBuilder $builder;
    private UrlGeneratorInterface $urlGenerator;
    private DataTableConfigResolver $configResolver;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->configResolver = $this->createMock(DataTableConfigResolver::class);
        $this->builder = new DataTableBuilder($this->urlGenerator, $this->configResolver);
    }

    public function testCreateDataTable(): void
    {
        $config = $this->builder->createDataTable(User::class);
        
        $this->assertInstanceOf(DataTableConfiguration::class, $config);
        $this->assertEquals(User::class, $config->getEntityClass());
    }

    public function testAddTextColumn(): void
    {
        $config = $this->builder->createDataTable(User::class);
        $options = ['sortable' => true, 'searchable' => true];
        
        $result = $this->builder->addTextColumn($config, 'name', 'name', 'Name', $options);
        
        $this->assertSame($config, $result);
        $columns = $config->getColumns();
        $this->assertCount(1, $columns);
        
        $column = $columns[0];
        $this->assertEquals('name', $column->getName());
        $this->assertEquals('name', $column->getPropertyPath());
        $this->assertEquals('Name', $column->getLabel());
        $this->assertTrue($column->isSortable());
        $this->assertTrue($column->isSearchable());
    }

    public function testAddDateColumn(): void
    {
        $config = $this->builder->createDataTable(User::class);
        $options = ['format' => 'd/m/Y H:i'];
        
        $result = $this->builder->addDateColumn($config, 'createdAt', 'createdAt', 'Created At', $options);
        
        $this->assertSame($config, $result);
        $columns = $config->getColumns();
        $this->assertCount(1, $columns);
        
        $column = $columns[0];
        $this->assertEquals('createdAt', $column->getName());
        $this->assertEquals('d/m/Y H:i', $column->getOption('format'));
    }

    public function testAddBadgeColumn(): void
    {
        $config = $this->builder->createDataTable(User::class);
        $options = [
            'badge_class' => 'bg-success',
            'value_mapping' => ['1' => 'Active', '0' => 'Inactive']
        ];
        
        $result = $this->builder->addBadgeColumn($config, 'status', 'status', 'Status', $options);
        
        $this->assertSame($config, $result);
        $columns = $config->getColumns();
        $this->assertCount(1, $columns);
        
        $column = $columns[0];
        $this->assertEquals('status', $column->getName());
        $this->assertEquals('bg-success', $column->getOption('badge_class'));
        $this->assertEquals(['1' => 'Active', '0' => 'Inactive'], $column->getOption('value_mapping'));
    }

    public function testAddActionColumn(): void
    {
        $config = $this->builder->createDataTable(User::class);
        $actions = [
            'show' => [
                'route' => 'user_show',
                'icon' => 'bi bi-eye'
            ],
            'edit' => [
                'route' => 'user_edit',
                'icon' => 'bi bi-pencil'
            ]
        ];
        
        $result = $this->builder->addActionColumn($config, $actions);
        
        $this->assertSame($config, $result);
        $columns = $config->getColumns();
        $this->assertCount(1, $columns);
        
        $column = $columns[0];
        $this->assertEquals('actions', $column->getName());
        $this->assertEquals($actions, $column->getOption('actions'));
    }

    public function testConfigureSearch(): void
    {
        $config = $this->builder->createDataTable(User::class);
        $searchFields = ['name', 'email'];
        
        $result = $this->builder->configureSearch($config, true, $searchFields);
        
        $this->assertSame($config, $result);
        $this->assertTrue($config->isSearchEnabled());
        $this->assertEquals($searchFields, $config->getSearchFields());
    }

    public function testConfigurePagination(): void
    {
        $config = $this->builder->createDataTable(User::class);
        
        $result = $this->builder->configurePagination($config, true, 25);
        
        $this->assertSame($config, $result);
        $this->assertTrue($config->isPaginationEnabled());
        $this->assertEquals(25, $config->getItemsPerPage());
    }

    public function testConfigureSorting(): void
    {
        $config = $this->builder->createDataTable(User::class);
        
        $result = $this->builder->configureSorting($config, true, 'name', 'asc');
        
        $this->assertSame($config, $result);
        $this->assertTrue($config->isSortingEnabled());
        $this->assertEquals('name', $config->getSortField());
        $this->assertEquals('asc', $config->getSortDirection());
    }

    public function testSetTableClass(): void
    {
        $config = $this->builder->createDataTable(User::class);
        $tableClass = 'table table-striped table-hover';
        
        $result = $this->builder->setTableClass($config, $tableClass);
        
        $this->assertSame($config, $result);
        $this->assertEquals($tableClass, $config->getTableClass());
    }

    public function testFluentApi(): void
    {
        $config = $this->builder->createDataTable(User::class);
        
        // Test chaining multiple operations
        $config = $this->builder->addTextColumn($config, 'name', 'name', 'Name');
        $config = $this->builder->addTextColumn($config, 'email', 'email', 'Email');
        $config = $this->builder->configureSearch($config, true, ['name', 'email']);
        $config = $this->builder->configurePagination($config, true, 15);
        $result = $this->builder->configureSorting($config, true, 'name', 'asc');
        
        $this->assertSame($config, $result);
        $this->assertCount(2, $config->getColumns());
        $this->assertTrue($config->isSearchEnabled());
        $this->assertTrue($config->isPaginationEnabled());
        $this->assertTrue($config->isSortingEnabled());
        $this->assertEquals(15, $config->getItemsPerPage());
        $this->assertEquals('name', $config->getSortField());
        $this->assertEquals('asc', $config->getSortDirection());
    }

    public function testAddTextColumnWithDefaults(): void
    {
        $config = $this->builder->createDataTable(User::class);
        
        $result = $this->builder->addTextColumn($config, 'id', 'id', 'ID');
        
        $this->assertSame($config, $result);
        $columns = $config->getColumns();
        $column = $columns[0];
        
        $this->assertFalse($column->isSortable()); // Default should be false
        $this->assertFalse($column->isSearchable()); // Default should be false
    }
}
