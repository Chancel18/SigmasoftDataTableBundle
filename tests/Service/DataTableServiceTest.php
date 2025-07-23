<?php
declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sigmasoft\DataTableBundle\Model\DataTableRequest;
use Sigmasoft\DataTableBundle\Model\DataTableResult;
use Sigmasoft\DataTableBundle\Service\ConfigurationManager;
use Sigmasoft\DataTableBundle\Service\DataTableService;
use Sigmasoft\DataTableBundle\Service\ValueFormatter;
use Sigmasoft\DataTableBundle\Exception\EntityNotFoundException;

class DataTableServiceTest extends TestCase
{
    private DataTableService $service;
    private EntityManagerInterface|MockObject $entityManager;
    private PaginatorInterface|MockObject $paginator;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private ConfigurationManager|MockObject $configurationManager;
    private ValueFormatter|MockObject $valueFormatter;
    private EntityRepository|MockObject $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->configurationManager = $this->createMock(ConfigurationManager::class);
        $this->valueFormatter = $this->createMock(ValueFormatter::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->service = new DataTableService(
            $this->entityManager,
            $this->paginator,
            $this->eventDispatcher,
            $this->valueFormatter,
            $this->configurationManager
        );
    }

    public function testGetDataReturnsDataTableResult(): void
    {
        $request = $this->createDataTableRequest();
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with('stdClass')
            ->willReturn($this->repository);

        $this->repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with('e')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->paginator
            ->expects($this->once())
            ->method('paginate')
            ->with($this->isInstanceOf(Query::class), 1, 10)
            ->willReturn($pagination);

        $pagination
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $pagination
            ->expects($this->once())
            ->method('getTotalItemCount')
            ->willReturn(0);

        $pagination
            ->expects($this->once())
            ->method('getCurrentPageNumber')
            ->willReturn(1);

        $pagination
            ->expects($this->once())
            ->method('getItemNumberPerPage')
            ->willReturn(10);

        $result = $this->service->getData($request);

        $this->assertInstanceOf(DataTableResult::class, $result);
        $this->assertSame([], $result->getItems());
        $this->assertSame(0, $result->getTotalCount());
        $this->assertSame(1, $result->getCurrentPage());
        $this->assertSame(10, $result->getItemsPerPage());
    }

    public function testDeleteEntityReturnsTrueOnSuccess(): void
    {
        $entity = new \stdClass();
        $entity->id = 1;

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with('stdClass')
            ->willReturn($this->repository);

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($entity);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($entity);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->service->deleteEntity('stdClass', 1);

        $this->assertTrue($result);
    }

    public function testDeleteEntityReturnsFalseWhenEntityNotFound(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with('stdClass')
            ->willReturn($this->repository);

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $this->entityManager
            ->expects($this->never())
            ->method('remove');

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("L'entité stdClass avec l'identifiant 1 n'a pas été trouvée");

        $this->service->deleteEntity('stdClass', 1);
    }

    public function testFindEntityReturnsEntity(): void
    {
        $entity = new \stdClass();
        $entity->id = 1;

        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with('stdClass', 1)
            ->willReturn($entity);

        $result = $this->service->findEntity('stdClass', 1);

        $this->assertSame($entity, $result);
    }

    public function testFindEntityReturnsNullWhenNotFound(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with('stdClass', 1)
            ->willReturn(null);

        $result = $this->service->findEntity('stdClass', 1);

        $this->assertNull($result);
    }

    public function testGetValueUsesFormatter(): void
    {
        $item = new \stdClass();
        $item->name = 'John Doe';
        $field = 'name';
        $formatConfig = ['type' => 'string'];

        $this->valueFormatter
            ->expects($this->once())
            ->method('format')
            ->with('John Doe', $formatConfig)
            ->willReturn('Formatted: John Doe');

        $result = $this->service->getValue($item, $field, $formatConfig);

        $this->assertSame('Formatted: John Doe', $result);
    }

    public function testSearchValidationPreventsSqlInjection(): void
    {
        $request = new DataTableRequest(
            entityClass: 'stdClass',
            page: 1,
            itemsPerPage: 10,
            search: 'test',
            searchFields: ['malicious;DROP TABLE users;--', 'valid_field'],
            sortField: 'valid_field',
            sortDirection: 'ASC',
            filters: []
        );

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturn($queryBuilder);
        $queryBuilder->method('from')->willReturn($queryBuilder);
        $queryBuilder->method('getQuery')->willReturn($query);

        // Le champ malicieux ne doit pas être utilisé dans andWhere
        $queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with($this->stringContains('LOWER(e.valid_field)'))
            ->willReturn($queryBuilder);

        $queryBuilder->method('setParameter')->willReturn($queryBuilder);

        $this->paginator
            ->method('paginate')
            ->willReturn($pagination);

        $pagination->method('getItems')->willReturn([]);
        $pagination->method('getTotalItemCount')->willReturn(0);
        $pagination->method('getCurrentPageNumber')->willReturn(1);
        $pagination->method('getItemNumberPerPage')->willReturn(10);

        $this->service->getData($request);
    }

    public function testSortDirectionValidation(): void
    {
        $request = new DataTableRequest(
            entityClass: 'stdClass',
            page: 1,
            itemsPerPage: 10,
            search: '',
            searchFields: [],
            sortField: 'name',
            sortDirection: 'INVALID_DIRECTION',
            filters: []
        );

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->entityManager
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturn($queryBuilder);
        $queryBuilder->method('from')->willReturn($queryBuilder);
        $queryBuilder->method('getQuery')->willReturn($query);

        // La direction invalide doit être remplacée par 'ASC'
        $queryBuilder
            ->expects($this->once())
            ->method('orderBy')
            ->with('e.name', 'ASC')
            ->willReturn($queryBuilder);

        $this->paginator
            ->method('paginate')
            ->willReturn($pagination);

        $pagination->method('getItems')->willReturn([]);
        $pagination->method('getTotalItemCount')->willReturn(0);
        $pagination->method('getCurrentPageNumber')->willReturn(1);
        $pagination->method('getItemNumberPerPage')->willReturn(10);

        $this->service->getData($request);
    }

    public function testExecuteBulkActionDeletesMultipleEntities(): void
    {
        $entity1 = new \stdClass();
        $entity1->id = 1;
        $entity2 = new \stdClass();
        $entity2->id = 2;

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->with('stdClass')
            ->willReturn($this->repository);

        $this->repository
            ->expects($this->exactly(2))
            ->method('find')
            ->willReturnCallback(function ($id) use ($entity1, $entity2) {
                return $id === 1 ? $entity1 : ($id === 2 ? $entity2 : null);
            });

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('remove');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->service->executeBulkAction('stdClass', 'delete', [1, 2]);

        $this->assertSame(['success' => 2, 'errors' => []], $result);
    }

    public function testExportDataReturnsArray(): void
    {
        $request = $this->createDataTableRequest();
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $this->entityManager
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturn($queryBuilder);
        $queryBuilder->method('from')->willReturn($queryBuilder);
        $queryBuilder->method('getQuery')->willReturn($query);

        $query
            ->expects($this->once())
            ->method('getResult')
            ->willReturn([
                (object)['id' => 1, 'name' => 'John'],
                (object)['id' => 2, 'name' => 'Jane']
            ]);

        $result = $this->service->exportData($request, 'csv');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    private function createDataTableRequest(): DataTableRequest
    {
        return new DataTableRequest(
            entityClass: 'stdClass',
            page: 1,
            itemsPerPage: 10,
            search: '',
            searchFields: [],
            sortField: '',
            sortDirection: 'ASC',
            filters: []
        );
    }
}