<?php

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Configuration;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Column\DateColumn;
use Sigmasoft\DataTableBundle\Exception\DataTableException;
use PHPUnit\Framework\TestCase;

class DataTableConfigurationTest extends TestCase
{
    private DataTableConfiguration $configuration;

    protected function setUp(): void
    {
        $this->configuration = new DataTableConfiguration(User::class);
    }

    public function testConstructor(): void
    {
        $this->assertEquals(User::class, $this->configuration->getEntityClass());
        $this->assertEquals([], $this->configuration->getColumns());
        $this->assertEquals([], $this->configuration->getSearchFields());
        $this->assertEquals([], $this->configuration->getFilters());
        $this->assertEquals('', $this->configuration->getSortField());
        $this->assertEquals('asc', $this->configuration->getSortDirection());
        $this->assertEquals(1, $this->configuration->getPage());
        $this->assertEquals(10, $this->configuration->getItemsPerPage());
        $this->assertEquals('', $this->configuration->getSearchQuery());
        $this->assertTrue($this->configuration->isSearchEnabled());
        $this->assertTrue($this->configuration->isPaginationEnabled());
        $this->assertTrue($this->configuration->isSortingEnabled());
        $this->assertEquals([], $this->configuration->getActions());
        $this->assertEquals('table table-striped table-hover', $this->configuration->getTableClass());
    }

    public function testConstructorWithInvalidEntityClass(): void
    {
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage('Entity class "NonExistentClass" not found or is not a valid Doctrine entity.');
        
        new DataTableConfiguration('NonExistentClass');
    }

    public function testConstructorWithEmptyEntityClass(): void
    {
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage('Entity class "" not found or is not a valid Doctrine entity.');
        
        new DataTableConfiguration('');
    }

    public function testAddColumn(): void
    {
        $column = new TextColumn('email', 'email', 'Email', true, true);
        
        $result = $this->configuration->addColumn($column);
        
        $this->assertSame($this->configuration, $result);
        $this->assertSame($column, $this->configuration->getColumn('email'));
        $this->assertContains('email', $this->configuration->getSearchFields());
    }

    public function testAddNonSearchableColumn(): void
    {
        $column = new TextColumn('id', 'id', 'ID', true, false);
        
        $this->configuration->addColumn($column);
        
        $this->assertSame($column, $this->configuration->getColumn('id'));
        $this->assertNotContains('id', $this->configuration->getSearchFields());
    }

    public function testGetNonExistentColumn(): void
    {
        $result = $this->configuration->getColumn('nonexistent');
        
        $this->assertNull($result);
    }

    public function testSetSearchFields(): void
    {
        $fields = ['email', 'name'];
        
        $result = $this->configuration->setSearchFields($fields);
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals($fields, $this->configuration->getSearchFields());
    }

    public function testSetFilters(): void
    {
        $filters = ['status' => 'active', 'type' => 'user'];
        
        $result = $this->configuration->setFilters($filters);
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals($filters, $this->configuration->getFilters());
    }

    public function testAddFilter(): void
    {
        $result = $this->configuration->addFilter('status', 'active');
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals(['status' => 'active'], $this->configuration->getFilters());
    }

    public function testSetSortField(): void
    {
        $result = $this->configuration->setSortField('email');
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals('email', $this->configuration->getSortField());
    }

    public function testSetSortDirection(): void
    {
        $result = $this->configuration->setSortDirection('desc');
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals('desc', $this->configuration->getSortDirection());
    }

    public function testSetInvalidSortDirection(): void
    {
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage('Invalid sort direction "invalid". Must be "asc" or "desc".');
        
        $this->configuration->setSortDirection('invalid');
    }

    public function testSetPage(): void
    {
        $result = $this->configuration->setPage(5);
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals(5, $this->configuration->getPage());
    }

    public function testSetInvalidPage(): void
    {
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage('Invalid page number "-1". Must be greater than 0.');
        
        $this->configuration->setPage(-1);
    }

    public function testSetPageZero(): void
    {
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage('Invalid page number "0". Must be greater than 0.');
        
        $this->configuration->setPage(0);
    }

    public function testSetItemsPerPage(): void
    {
        $result = $this->configuration->setItemsPerPage(25);
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals(25, $this->configuration->getItemsPerPage());
    }

    public function testSetInvalidItemsPerPage(): void
    {
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage('Invalid items per page "-5". Must be greater than 0.');
        
        $this->configuration->setItemsPerPage(-5);
    }

    public function testSetItemsPerPageZero(): void
    {
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage('Invalid items per page "0". Must be greater than 0.');
        
        $this->configuration->setItemsPerPage(0);
    }

    public function testSetSearchQuery(): void
    {
        $result = $this->configuration->setSearchQuery('john doe');
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals('john doe', $this->configuration->getSearchQuery());
    }

    public function testSetSearchEnabled(): void
    {
        $result = $this->configuration->setSearchEnabled(false);
        
        $this->assertSame($this->configuration, $result);
        $this->assertFalse($this->configuration->isSearchEnabled());
    }

    public function testSetPaginationEnabled(): void
    {
        $result = $this->configuration->setPaginationEnabled(false);
        
        $this->assertSame($this->configuration, $result);
        $this->assertFalse($this->configuration->isPaginationEnabled());
    }

    public function testSetSortingEnabled(): void
    {
        $result = $this->configuration->setSortingEnabled(false);
        
        $this->assertSame($this->configuration, $result);
        $this->assertFalse($this->configuration->isSortingEnabled());
    }

    public function testSetActions(): void
    {
        $actions = [
            'edit' => ['route' => 'app_user_edit'],
            'delete' => ['route' => 'app_user_delete']
        ];
        
        $result = $this->configuration->setActions($actions);
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals($actions, $this->configuration->getActions());
    }

    public function testAddAction(): void
    {
        $action = ['route' => 'app_user_edit', 'icon' => 'bi-pencil'];
        
        $result = $this->configuration->addAction('edit', $action);
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals(['edit' => $action], $this->configuration->getActions());
    }

    public function testHasActionsWithNoActions(): void
    {
        $this->assertFalse($this->configuration->hasActions());
    }

    public function testHasActionsWithActions(): void
    {
        $this->configuration->addAction('edit', ['route' => 'app_user_edit']);
        
        $this->assertTrue($this->configuration->hasActions());
    }

    public function testSetTableClass(): void
    {
        $tableClass = 'table table-dark table-striped';
        
        $result = $this->configuration->setTableClass($tableClass);
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals($tableClass, $this->configuration->getTableClass());
    }

    public function testMethodChaining(): void
    {
        $result = $this->configuration
            ->setSortField('email')
            ->setSortDirection('desc')
            ->setPage(2)
            ->setItemsPerPage(20)
            ->setSearchQuery('test')
            ->setSearchEnabled(false)
            ->setPaginationEnabled(false)
            ->setSortingEnabled(false);
        
        $this->assertSame($this->configuration, $result);
        $this->assertEquals('email', $this->configuration->getSortField());
        $this->assertEquals('desc', $this->configuration->getSortDirection());
        $this->assertEquals(2, $this->configuration->getPage());
        $this->assertEquals(20, $this->configuration->getItemsPerPage());
        $this->assertEquals('test', $this->configuration->getSearchQuery());
        $this->assertFalse($this->configuration->isSearchEnabled());
        $this->assertFalse($this->configuration->isPaginationEnabled());
        $this->assertFalse($this->configuration->isSortingEnabled());
    }
}
