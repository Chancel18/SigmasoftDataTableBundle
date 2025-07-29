<?php

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Component;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Component\DataTableComponent;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Configuration\SerializableDataTableConfig;
use Sigmasoft\DataTableBundle\DataProvider\DataProviderInterface;
use Sigmasoft\DataTableBundle\Service\ColumnFactory;
use Sigmasoft\DataTableBundle\Service\DataTableRegistryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DataTableComponentTest extends TestCase
{
    private DataTableComponent $component;
    private DataProviderInterface|MockObject $dataProvider;
    private EntityManagerInterface|MockObject $entityManager;
    private ColumnFactory|MockObject $columnFactory;
    private DataTableRegistryInterface|MockObject $registry;

    protected function setUp(): void
    {
        $this->dataProvider = $this->createMock(DataProviderInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->columnFactory = $this->createMock(ColumnFactory::class);
        $this->registry = $this->createMock(DataTableRegistryInterface::class);

        $this->component = new DataTableComponent(
            $this->dataProvider,
            $this->entityManager,
            $this->columnFactory,
            $this->registry,
            null // logger
        );
    }

    public function testMount(): void
    {
        $configuration = new DataTableConfiguration(User::class);
        $serializableConfig = SerializableDataTableConfig::fromConfiguration($configuration);

        $this->registry
            ->expects($this->once())
            ->method('generateId')
            ->willReturn('test_id');

        $this->registry
            ->expects($this->once())
            ->method('register')
            ->with('test_id', $configuration);

        $this->component->mount($configuration);

        $this->assertInstanceOf(SerializableDataTableConfig::class, $this->component->config);
        $this->assertEquals(User::class, $this->component->config->entityClass);
    }

    public function testSortAction(): void
    {
        $this->setupComponent();

        // Mock current sort field and direction
        $this->component->config = $this->createConfigMock('email', 'asc');

        $newConfig = $this->createConfigMock('email', 'desc', 1);
        $this->mockConfigWithUpdates($newConfig, [
            'sortField' => 'email',
            'sortDirection' => 'desc',
            'page' => 1
        ]);

        $this->setupDataProviderMock();

        $this->component->sort('email');

        $this->assertSame($newConfig, $this->component->config);
    }

    public function testSortActionNewField(): void
    {
        $this->setupComponent();

        // Mock current sort field different from new field
        $this->component->config = $this->createConfigMock('name', 'desc');

        $newConfig = $this->createConfigMock('email', 'asc', 1);
        $this->mockConfigWithUpdates($newConfig, [
            'sortField' => 'email',
            'sortDirection' => 'asc',
            'page' => 1
        ]);

        $this->setupDataProviderMock();

        $this->component->sort('email');

        $this->assertSame($newConfig, $this->component->config);
    }

    public function testSearchAction(): void
    {
        $this->setupComponent();

        $this->component->config = $this->createConfigMock();

        $newConfig = $this->createConfigMock('', '', 1, '', 'john');
        $this->mockConfigWithUpdates($newConfig, [
            'searchQuery' => 'john',
            'page' => 1
        ]);

        $this->setupDataProviderMock();

        $this->component->search('john');

        $this->assertSame($newConfig, $this->component->config);
    }

    public function testClearSearchAction(): void
    {
        $this->setupComponent();

        $this->component->config = $this->createConfigMock('', '', 1, '', 'old_search');

        $newConfig = $this->createConfigMock('', '', 1, '', '');
        $this->mockConfigWithUpdates($newConfig, [
            'searchQuery' => '',
            'page' => 1
        ]);

        $this->setupDataProviderMock();

        $this->component->clearSearch();

        $this->assertSame($newConfig, $this->component->config);
    }

    public function testChangePageAction(): void
    {
        $this->setupComponent();

        $this->component->config = $this->createConfigMock('', '', 1);

        $newConfig = $this->createConfigMock('', '', 3);
        $this->mockConfigWithUpdates($newConfig, ['page' => 3]);

        $this->setupDataProviderMock();

        $this->component->changePage(3);

        $this->assertSame($newConfig, $this->component->config);
    }

    public function testChangePageActionWithInvalidPage(): void
    {
        $this->setupComponent();

        $this->component->config = $this->createConfigMock('', '', 2);

        $newConfig = $this->createConfigMock('', '', 1);
        $this->mockConfigWithUpdates($newConfig, ['page' => 1]);

        $this->setupDataProviderMock();

        // Test with negative page number
        $this->component->changePage(-1);

        $this->assertSame($newConfig, $this->component->config);
    }

    public function testChangeItemsPerPageAction(): void
    {
        $this->setupComponent();

        $this->component->config = $this->createConfigMock('', '', 1, '', '', 10);

        $newConfig = $this->createConfigMock('', '', 1, '', '', 25);
        $this->mockConfigWithUpdates($newConfig, [
            'itemsPerPage' => 25,
            'page' => 1
        ]);

        $this->setupDataProviderMock();

        $this->component->changeItemsPerPage(25);

        $this->assertSame($newConfig, $this->component->config);
    }

    public function testDeleteItemAction(): void
    {
        $this->setupComponent();

        $repository = $this->createMock(EntityRepository::class);
        $user = new User();

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $repository
            ->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($user);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->setupDataProviderMock();

        $this->component->deleteItem(123);

        $this->assertTrue($this->component->showAlert);
        $this->assertEquals('Élément supprimé avec succès', $this->component->alertMessage);
        $this->assertEquals('success', $this->component->alertType);
    }

    public function testDeleteItemActionWithNonExistentItem(): void
    {
        $this->setupComponent();

        $repository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $repository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->entityManager
            ->expects($this->never())
            ->method('remove');

        $this->component->deleteItem(999);

        $this->assertTrue($this->component->showAlert);
        $this->assertEquals('Élément non trouvé', $this->component->alertMessage);
        $this->assertEquals('error', $this->component->alertType);
    }

    public function testDeleteItemActionWithException(): void
    {
        $this->setupComponent();

        $repository = $this->createMock(EntityRepository::class);
        $user = new User();

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $repository
            ->expects($this->once())
            ->method('find')
            ->willReturn($user);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(new \Exception('Database error'));

        $this->component->deleteItem(123);

        $this->assertTrue($this->component->showAlert);
        $this->assertEquals('Erreur lors de la suppression', $this->component->alertMessage);
        $this->assertEquals('error', $this->component->alertType);
    }

    public function testDismissAlertAction(): void
    {
        $this->component->showAlert = true;

        $this->component->dismissAlert();

        $this->assertFalse($this->component->showAlert);
    }

    public function testGetData(): void
    {
        $this->setupComponent();
        $pagination = $this->createMock(PaginationInterface::class);

        $this->setupDataProviderMock($pagination);

        $result = $this->component->getData();

        $this->assertSame($pagination, $result);
    }

    public function testGetConfiguration(): void
    {
        $this->setupComponent();
        $configuration = new DataTableConfiguration(User::class);

        $this->columnFactory
            ->expects($this->once())
            ->method('reconstructConfiguration')
            ->with($this->component->config, $this->registry)
            ->willReturn($configuration);

        $result = $this->component->getConfiguration();

        $this->assertSame($configuration, $result);
    }

    private function setupComponent(): void
    {
        $this->component->config = $this->createConfigMock();
    }

    private function createConfigMock(
        string $sortField = '',
        string $sortDirection = 'asc',
        int $page = 1,
        string $entityClass = '',
        string $searchQuery = '',
        int $itemsPerPage = 10
    ): SerializableDataTableConfig|MockObject {
        $config = $this->createMock(SerializableDataTableConfig::class);
        $config->sortField = $sortField;
        $config->sortDirection = $sortDirection;
        $config->page = $page;
        $config->entityClass = $entityClass ?: User::class;
        $config->searchQuery = $searchQuery;
        $config->itemsPerPage = $itemsPerPage;
        return $config;
    }

    private function mockConfigWithUpdates(SerializableDataTableConfig $newConfig, array $updates): void
    {
        // Build parameters in the correct order for withUpdates method
        $params = [
            $updates['filters'] ?? null,
            $updates['sortField'] ?? null,
            $updates['sortDirection'] ?? null,
            $updates['page'] ?? null,
            $updates['itemsPerPage'] ?? null,
            $updates['searchQuery'] ?? null,
        ];
        
        $this->component->config
            ->expects($this->once())
            ->method('withUpdates')
            ->with(...$params)
            ->willReturn($newConfig);
    }

    private function setupDataProviderMock(?PaginationInterface $pagination = null): void
    {
        $configuration = new DataTableConfiguration(User::class);

        $this->columnFactory
            ->expects($this->any())
            ->method('reconstructConfiguration')
            ->willReturn($configuration);

        if ($pagination) {
            $this->dataProvider
                ->expects($this->once())
                ->method('getData')
                ->willReturn($pagination);
        } else {
            $this->dataProvider
                ->expects($this->any())
                ->method('getData')
                ->willReturn($this->createMock(PaginationInterface::class));
        }
    }

    private function mockStaticMethod(string $class, string $method, $returnValue): void
    {
        // This is a simplified approach for testing static methods
        // In a real scenario, you might need to use tools like PHP-Mock or refactor the code
        // to make it more testable by avoiding static calls
    }
}
