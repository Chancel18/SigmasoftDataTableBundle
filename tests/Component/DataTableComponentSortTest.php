<?php

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Component;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Component\DataTableComponent;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Configuration\SerializableDataTableConfig;
use Sigmasoft\DataTableBundle\DataProvider\DataProviderInterface;
use Sigmasoft\DataTableBundle\Service\ColumnFactory;
use Sigmasoft\DataTableBundle\Service\DataTableRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;

class DataTableComponentSortTest extends TestCase
{
    private DataTableComponent $component;
    private DataProviderInterface $dataProvider;
    private EntityManagerInterface $entityManager;
    private ColumnFactory $columnFactory;
    private DataTableRegistry $registry;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->dataProvider = $this->createMock(DataProviderInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->columnFactory = $this->createMock(ColumnFactory::class);
        $this->registry = $this->createMock(DataTableRegistry::class);

        // Create component
        $this->component = new DataTableComponent(
            $this->dataProvider,
            $this->entityManager,
            $this->columnFactory,
            $this->registry
        );
    }

    public function testSortActionUpdatesConfigurationCorrectly(): void
    {
        // Setup initial configuration
        $initialConfig = new DataTableConfiguration(User::class);
        $initialConfig->addColumn(new TextColumn('name', 'name', 'Name'));
        $initialConfig->setSortField('id');
        $initialConfig->setSortDirection('asc');

        // Mock registry to return the initial config
        $this->registry->method('generateId')->willReturn('test_config_id');
        $this->registry->method('register');
        
        // Create initial serializable config
        $serializableConfig = SerializableDataTableConfig::fromConfiguration('test_config_id', $initialConfig);
        
        // Mock pagination data
        $mockPagination = $this->createMock(PaginationInterface::class);
        $mockPagination->method('getTotalItemCount')->willReturn(5);
        $mockPagination->method('getCurrentPageNumber')->willReturn(1);
        $mockPagination->method('count')->willReturn(5);
        
        // Mock data provider to return pagination
        $this->dataProvider->method('getData')->willReturn($mockPagination);
        
        // Mock column factory to reconstruct configuration
        $this->columnFactory->method('reconstructConfiguration')->willReturn($initialConfig);

        // Simulate mount to initialize the component
        $this->component->config = $serializableConfig;
        $this->component->searchInput = '';

        // Capture initial state
        $initialSortField = $this->component->config->sortField;
        $initialSortDirection = $this->component->config->sortDirection;
        
        echo "\n=== TEST SORT ACTION ===\n";
        echo "Initial state:\n";
        echo "- sortField: '$initialSortField'\n";
        echo "- sortDirection: '$initialSortDirection'\n";
        
        // Test 1: Sort by different field (should set to 'asc')
        echo "\n1. Testing sort by different field 'name':\n";
        $this->component->sort('name');
        
        echo "After sort('name'):\n";
        echo "- sortField: '{$this->component->config->sortField}'\n";
        echo "- sortDirection: '{$this->component->config->sortDirection}'\n";
        echo "- page: {$this->component->config->page}\n";
        
        $this->assertEquals('name', $this->component->config->sortField);
        $this->assertEquals('asc', $this->component->config->sortDirection);
        $this->assertEquals(1, $this->component->config->page); // Should reset to page 1
        
        // Test 2: Sort by same field (should toggle to 'desc')
        echo "\n2. Testing sort by same field 'name' again (should toggle):\n";
        $this->component->sort('name');
        
        echo "After second sort('name'):\n";
        echo "- sortField: '{$this->component->config->sortField}'\n";
        echo "- sortDirection: '{$this->component->config->sortDirection}'\n";
        echo "- page: {$this->component->config->page}\n";
        
        $this->assertEquals('name', $this->component->config->sortField);
        $this->assertEquals('desc', $this->component->config->sortDirection);
        $this->assertEquals(1, $this->component->config->page);
        
        // Test 3: Sort by same field again (should toggle back to 'asc')
        echo "\n3. Testing sort by same field 'name' third time (should toggle back):\n";
        $this->component->sort('name');
        
        echo "After third sort('name'):\n";
        echo "- sortField: '{$this->component->config->sortField}'\n";
        echo "- sortDirection: '{$this->component->config->sortDirection}'\n";
        echo "- page: {$this->component->config->page}\n";
        
        $this->assertEquals('name', $this->component->config->sortField);
        $this->assertEquals('asc', $this->component->config->sortDirection);
        $this->assertEquals(1, $this->component->config->page);
        
        echo "\n=== SORT ACTION TEST COMPLETED ===\n";
    }

    public function testSortActionCallsDataProviderWithUpdatedConfig(): void
    {
        // Setup initial configuration
        $initialConfig = new DataTableConfiguration(User::class);
        $initialConfig->addColumn(new TextColumn('name', 'name', 'Name'));
        $initialConfig->setSortField('id');
        $initialConfig->setSortDirection('asc');

        // Mock dependencies
        $this->registry->method('generateId')->willReturn('test_config_id');
        $this->registry->method('register');
        
        $serializableConfig = SerializableDataTableConfig::fromConfiguration('test_config_id', $initialConfig);
        
        $mockPagination = $this->createMock(PaginationInterface::class);
        $mockPagination->method('getTotalItemCount')->willReturn(5);
        $mockPagination->method('getCurrentPageNumber')->willReturn(1);
        $mockPagination->method('count')->willReturn(5);
        
        // Initialize component
        $this->component->config = $serializableConfig;
        $this->component->searchInput = '';
        
        // Mock column factory to return the updated configuration
        $this->columnFactory->expects($this->once())
            ->method('reconstructConfiguration')
            ->willReturnCallback(function($config, $registry) use ($initialConfig) {
                // The config passed should have the updated sort values
                $updatedConfig = clone $initialConfig;
                $updatedConfig->setSortField($config->sortField);
                $updatedConfig->setSortDirection($config->sortDirection);
                $updatedConfig->setPage($config->page);
                return $updatedConfig;
            });
        
        // Mock data provider to verify it's called with correct config
        $this->dataProvider->expects($this->once())
            ->method('getData')
            ->willReturnCallback(function($config) use ($mockPagination) {
                echo "\nDataProvider called with:\n";
                echo "- Entity: {$config->getEntityClass()}\n";
                echo "- Sort field: '{$config->getSortField()}'\n";
                echo "- Sort direction: '{$config->getSortDirection()}'\n";
                echo "- Page: {$config->getPage()}\n";
                
                // Verify the configuration has the expected values
                $this->assertEquals('name', $config->getSortField());
                $this->assertEquals('asc', $config->getSortDirection());
                $this->assertEquals(1, $config->getPage());
                
                return $mockPagination;
            });
        
        // Execute sort action
        echo "\n=== TESTING DATA PROVIDER CALL ===\n";
        $this->component->sort('name');
        
        echo "\n=== DATA PROVIDER CALL TEST COMPLETED ===\n";
    }

    public function testConfigObjectImmutability(): void
    {
        // Test that withUpdates creates a new object
        $config = new SerializableDataTableConfig(
            'test_id',
            User::class,
            [],
            [],
            [],
            'id',
            'asc',
            1,
            10,
            ''
        );
        
        echo "\n=== TESTING CONFIG IMMUTABILITY ===\n";
        echo "Original config object hash: " . spl_object_hash($config) . "\n";
        echo "Original sortField: '{$config->sortField}'\n";
        
        $newConfig = $config->withUpdates(
            filters: null,
            sortField: 'name',
            sortDirection: 'desc',
            page: 1,
            itemsPerPage: null,
            searchQuery: null
        );
        
        echo "New config object hash: " . spl_object_hash($newConfig) . "\n";
        echo "New sortField: '{$newConfig->sortField}'\n";
        echo "Objects are different: " . ($config !== $newConfig ? 'YES' : 'NO') . "\n";
        
        $this->assertNotSame($config, $newConfig);
        $this->assertEquals('id', $config->sortField); // Original unchanged
        $this->assertEquals('name', $newConfig->sortField); // New has updated value
        
        echo "\n=== CONFIG IMMUTABILITY TEST COMPLETED ===\n";
    }
}
