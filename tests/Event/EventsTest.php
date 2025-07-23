<?php
declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Event;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\Event\DataTableBeforeLoadEvent;
use Sigmasoft\DataTableBundle\Event\DataTableRowRenderEvent;
use Sigmasoft\DataTableBundle\Model\DataTableRequest;

class EventsTest extends TestCase
{
    private DataTableRequest $sampleRequest;

    protected function setUp(): void
    {
        $this->sampleRequest = new DataTableRequest(
            entityClass: 'App\\Entity\\User',
            page: 1,
            itemsPerPage: 10,
            search: 'test',
            searchFields: ['name', 'email'],
            sortField: 'name',
            sortDirection: 'ASC',
            filters: ['status' => 'active']
        );
    }

    public function testDataTableBeforeLoadEvent(): void
    {
        $entityClass = 'App\\Entity\\User';
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $context = ['test' => 'value'];

        $event = new DataTableBeforeLoadEvent(
            $entityClass,
            $this->sampleRequest,
            $queryBuilder,
            $context
        );

        $this->assertSame($entityClass, $event->getEntityClass());
        $this->assertSame($this->sampleRequest, $event->getRequest());
        $this->assertSame($queryBuilder, $event->getQueryBuilder());
        $this->assertSame($context, $event->getContext());

        // Test modification du QueryBuilder
        $newQueryBuilder = $this->createMock(QueryBuilder::class);
        $event->setQueryBuilder($newQueryBuilder);
        $this->assertSame($newQueryBuilder, $event->getQueryBuilder());
    }

    public function testDataTableRowRenderEvent(): void
    {
        $entityClass = 'App\\Entity\\User';
        $entity = new \stdClass();
        $entity->name = 'John Doe';
        $rowData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $rowAttributes = ['class' => 'user-row'];

        $event = new DataTableRowRenderEvent(
            $entityClass,
            $entity,
            $rowData,
            $rowAttributes
        );

        $this->assertSame($entityClass, $event->getEntityClass());
        $this->assertSame($entity, $event->getEntity());
        $this->assertSame($rowData, $event->getRowData());
        $this->assertSame($rowAttributes, $event->getRowAttributes());

        // Test modification des données de ligne
        $newRowData = ['name' => 'Jane Doe', 'email' => 'jane@example.com'];
        $event->setRowData($newRowData);
        $this->assertSame($newRowData, $event->getRowData());

        // Test ajout d'attribut
        $event->addRowAttribute('data-id', '123');
        $expectedAttributes = array_merge($rowAttributes, ['data-id' => '123']);
        $this->assertSame($expectedAttributes, $event->getRowAttributes());
    }

    public function testEventContext(): void
    {
        $entityClass = 'App\\Entity\\Test';
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $context = ['initial' => 'value'];

        $event = new DataTableBeforeLoadEvent(
            $entityClass,
            $this->sampleRequest,
            $queryBuilder,
            $context
        );

        // Test récupération de contexte
        $this->assertSame($context, $event->getContext());
        $this->assertSame('value', $event->getContextValue('initial'));
        $this->assertNull($event->getContextValue('nonexistent'));
        $this->assertSame('default', $event->getContextValue('nonexistent', 'default'));

        // Test modification du contexte
        $newContext = ['new' => 'context'];
        $event->setContext($newContext);
        $this->assertSame($newContext, $event->getContext());

        // Test ajout au contexte
        $event->addContext('added', 'value');
        $expectedContext = array_merge($newContext, ['added' => 'value']);
        $this->assertSame($expectedContext, $event->getContext());
    }

    public function testEventStopPropagation(): void
    {
        $event = new DataTableBeforeLoadEvent(
            'App\\Entity\\User',
            $this->sampleRequest,
            $this->createMock(QueryBuilder::class)
        );

        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testRowRenderEventWithEmptyData(): void
    {
        $entity = new \stdClass();
        $event = new DataTableRowRenderEvent('App\\Entity\\Empty', $entity, []);

        $this->assertSame([], $event->getRowData());
        $this->assertSame([], $event->getRowAttributes());

        // Test avec des données null/vides
        $event->setRowData(['field' => null]);
        $this->assertSame(['field' => null], $event->getRowData());
    }

    public function testEventInheritance(): void
    {
        $event = new DataTableBeforeLoadEvent(
            'App\\Entity\\Test',
            $this->sampleRequest,
            $this->createMock(QueryBuilder::class)
        );

        // Vérifier l'héritage correct
        $this->assertInstanceOf(\Sigmasoft\DataTableBundle\Event\DataTableEvent::class, $event);
        $this->assertInstanceOf(\Symfony\Contracts\EventDispatcher\Event::class, $event);
    }
}