<?php
declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Twig\Components;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sigmasoft\DataTableBundle\Twig\Components\SigmasoftDataTableComponent;
use Sigmasoft\DataTableBundle\Service\DataTableServiceInterface;
use Sigmasoft\DataTableBundle\Service\ConfigurationManager;
use Sigmasoft\DataTableBundle\Service\RealtimeUpdateService;
use Sigmasoft\DataTableBundle\Service\EntityConfiguration;
use Sigmasoft\DataTableBundle\Model\DataTableResult;
use Sigmasoft\DataTableBundle\Model\DataTableRequest;

class SigmasoftDataTableComponentTest extends TestCase
{
    private SigmasoftDataTableComponent $component;
    private MockObject $dataTableService;
    private MockObject $configurationManager;
    private MockObject $realtimeService;
    private MockObject $entityConfiguration;

    protected function setUp(): void
    {
        $this->dataTableService = $this->createMock(DataTableServiceInterface::class);
        $this->configurationManager = $this->createMock(ConfigurationManager::class);
        $this->realtimeService = $this->createMock(RealtimeUpdateService::class);
        $this->entityConfiguration = $this->createMock(EntityConfiguration::class);

        $this->component = new SigmasoftDataTableComponent(
            $this->dataTableService,
            $this->configurationManager,
            $this->realtimeService
        );
    }

    public function testMountInitializesComponent(): void
    {
        $entityClass = 'stdClass';
        $overrideConfig = ['items_per_page' => 15];

        $this->configurationManager
            ->expects($this->once())
            ->method('hasEntityConfig')
            ->with($entityClass)
            ->willReturn(true);

        $this->configurationManager
            ->expects($this->once())
            ->method('getEntityConfig')
            ->with($entityClass)
            ->willReturn($this->entityConfiguration);

        $this->entityConfiguration
            ->expects($this->once())
            ->method('getRealtimeConfig')
            ->willReturn([
                'enabled' => false,
                'auto_refresh' => false,
                'refresh_interval' => 30000
            ]);

        $this->entityConfiguration
            ->expects($this->once())
            ->method('getDefaultSort')
            ->willReturn(['field' => 'id', 'direction' => 'asc']);

        $this->entityConfiguration
            ->expects($this->once())
            ->method('getFields')
            ->willReturn(['id' => ['type' => 'integer'], 'name' => ['type' => 'string']]);

        $this->entityConfiguration
            ->expects($this->once())
            ->method('getSearchFields')
            ->willReturn(['name']);

        $this->entityConfiguration
            ->expects($this->once())
            ->method('getItemsPerPage')
            ->willReturn(10);

        $mockResult = new DataTableResult([], 0, 1, 10);
        $this->dataTableService
            ->expects($this->once())
            ->method('getData')
            ->willReturn($mockResult);

        $this->component->mount($entityClass, $overrideConfig);

        $this->assertSame($entityClass, $this->component->entityClass);
        $this->assertSame($overrideConfig, $this->component->overrideConfig);
        $this->assertSame(1, $this->component->page);
        $this->assertSame('', $this->component->inputSearch);
    }

    public function testMountThrowsExceptionForInvalidEntity(): void
    {
        $entityClass = 'NonExistentEntity';

        $this->configurationManager
            ->expects($this->once())
            ->method('hasEntityConfig')
            ->with($entityClass)
            ->willReturn(false);

        $this->component->mount($entityClass);

        $this->assertTrue($this->component->hasErrors());
        $errors = $this->component->getErrors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Erreur lors de l\'initialisation', $errors[0]);
    }

    public function testSortActionChangesDirection(): void
    {
        $this->setupMountedComponent();
        
        $fieldConfig = ['sortable' => true];
        $this->entityConfiguration
            ->expects($this->once())
            ->method('getField')
            ->with('name')
            ->willReturn($fieldConfig);

        $this->entityConfiguration
            ->expects($this->once())
            ->method('getDefaultSort')
            ->willReturn(['field' => 'id', 'direction' => 'asc']);

        $mockResult = new DataTableResult([], 0, 1, 10);
        $this->dataTableService
            ->expects($this->once())
            ->method('getData')
            ->willReturn($mockResult);

        $this->component->sort('name');

        $this->assertSame(1, $this->component->page);
        $this->assertArrayHasKey('_sort', $this->component->filters);
        $this->assertSame('name', $this->component->filters['_sort']['field']);
        $this->assertSame('asc', $this->component->filters['_sort']['direction']);
    }

    public function testSortActionPreventsUnsortableField(): void
    {
        $this->setupMountedComponent();
        
        $fieldConfig = ['sortable' => false];
        $this->entityConfiguration
            ->expects($this->once())
            ->method('getField')
            ->with('description')
            ->willReturn($fieldConfig);

        $this->component->sort('description');

        $this->assertTrue($this->component->hasErrors());
        $errors = $this->component->getErrors();
        $this->assertStringContainsString('n\'est pas triable', $errors[0]);
    }

    public function testChangePageAction(): void
    {
        $this->setupMountedComponent();

        $mockResult = new DataTableResult([], 0, 1, 10);
        $this->dataTableService
            ->expects($this->once())
            ->method('getData')
            ->willReturn($mockResult);

        $this->component->changePage(3);

        $this->assertSame(3, $this->component->page);
    }

    public function testChangePageIgnoresInvalidPage(): void
    {
        $this->setupMountedComponent();

        $initialPage = $this->component->page;
        $this->component->changePage(0);

        $this->assertSame($initialPage, $this->component->page);
    }

    public function testSearchAction(): void
    {
        $this->setupMountedComponent();

        $this->component->inputSearch = 'test search';

        $mockResult = new DataTableResult([], 0, 1, 10);
        $this->dataTableService
            ->expects($this->once())
            ->method('getData')
            ->willReturn($mockResult);

        $this->component->search();

        $this->assertSame(1, $this->component->page);
    }

    public function testClearSearchAction(): void
    {
        $this->setupMountedComponent();

        $this->component->inputSearch = 'test search';

        $mockResult = new DataTableResult([], 0, 1, 10);
        $this->dataTableService
            ->expects($this->once())
            ->method('getData')
            ->willReturn($mockResult);

        $this->component->clearSearch();

        $this->assertSame('', $this->component->inputSearch);
        $this->assertSame(1, $this->component->page);
    }

    public function testApplyFilterAction(): void
    {
        $this->setupMountedComponent();

        $mockResult = new DataTableResult([], 0, 1, 10);
        $this->dataTableService
            ->expects($this->once())
            ->method('getData')
            ->willReturn($mockResult);

        $this->component->applyFilter('status', 'active');

        $this->assertSame(1, $this->component->page);
        $this->assertArrayHasKey('status', $this->component->filters);
        $this->assertSame('active', $this->component->filters['status']);
    }

    public function testApplyFilterWithEmptyValueRemovesFilter(): void
    {
        $this->setupMountedComponent();

        $this->component->filters['status'] = 'active';

        $mockResult = new DataTableResult([], 0, 1, 10);
        $this->dataTableService
            ->expects($this->once())
            ->method('getData')
            ->willReturn($mockResult);

        $this->component->applyFilter('status', '');

        $this->assertArrayNotHasKey('status', $this->component->filters);
    }

    public function testClearFiltersAction(): void
    {
        $this->setupMountedComponent();

        $this->component->filters = ['status' => 'active', 'category' => 'test'];

        $mockResult = new DataTableResult([], 0, 1, 10);
        $this->dataTableService
            ->expects($this->once())
            ->method('getData')
            ->willReturn($mockResult);

        $this->component->clearFilters();

        $this->assertSame([], $this->component->filters);
        $this->assertSame(1, $this->component->page);
    }

    public function testDeleteItemAction(): void
    {
        $this->setupMountedComponent();

        $entity = new \stdClass();
        $this->dataTableService
            ->expects($this->once())
            ->method('findEntity')
            ->with('stdClass', 123)
            ->willReturn($entity);

        $this->dataTableService
            ->expects($this->once())
            ->method('deleteEntity')
            ->with('stdClass', 123)
            ->willReturn(true);

        $mockResult = new DataTableResult([], 0, 1, 10);
        $this->dataTableService
            ->expects($this->once())
            ->method('getData')
            ->willReturn($mockResult);

        $this->component->deleteItem(123);

        $this->assertTrue($this->component->showAlertMessage);
        $this->assertSame('success', $this->component->alertType);
    }

    public function testGetStreamName(): void
    {
        $this->component->entityClass = 'App\\Entity\\User';
        
        $expected = 'datatable-app-entity-user';
        $this->assertSame($expected, $this->component->getStreamName());
    }

    public function testGetRealtimeConfig(): void
    {
        $this->setupMountedComponent();

        $this->entityConfiguration
            ->expects($this->once())
            ->method('getRealtimeConfig')
            ->willReturn([
                'enabled' => true,
                'auto_refresh' => true,
                'refresh_interval' => 15000
            ]);

        $config = $this->component->getRealtimeConfig();

        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('autoRefresh', $config);
        $this->assertArrayHasKey('refreshInterval', $config);
        $this->assertArrayHasKey('streamName', $config);
    }

    private function setupMountedComponent(): void
    {
        $entityClass = 'stdClass';

        $this->configurationManager
            ->method('hasEntityConfig')
            ->willReturn(true);

        $this->configurationManager
            ->method('getEntityConfig')
            ->willReturn($this->entityConfiguration);

        $this->entityConfiguration
            ->method('getRealtimeConfig')
            ->willReturn([
                'enabled' => false,
                'auto_refresh' => false,
                'refresh_interval' => 30000
            ]);

        $this->entityConfiguration
            ->method('getDefaultSort')
            ->willReturn(['field' => 'id', 'direction' => 'asc']);

        $this->entityConfiguration
            ->method('getFields')
            ->willReturn(['id' => ['type' => 'integer'], 'name' => ['type' => 'string']]);

        $this->entityConfiguration
            ->method('getSearchFields')
            ->willReturn(['name']);

        $this->entityConfiguration
            ->method('getItemsPerPage')
            ->willReturn(10);

        $mockResult = new DataTableResult([], 0, 1, 10);
        $this->dataTableService
            ->method('getData')
            ->willReturn($mockResult);

        $this->component->mount($entityClass);
    }
}