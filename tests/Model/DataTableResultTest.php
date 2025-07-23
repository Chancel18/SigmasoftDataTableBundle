<?php
declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Model;

use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sigmasoft\DataTableBundle\Model\DataTableResult;

class DataTableResultTest extends TestCase
{
    public function testConstructorWithValidData(): void
    {
        $items = ['item1', 'item2', 'item3'];
        $totalCount = 25;
        $currentPage = 2;
        $itemsPerPage = 10;
        $metadata = ['key' => 'value'];

        $result = new DataTableResult($items, $totalCount, $currentPage, $itemsPerPage, $metadata);

        $this->assertSame($items, $result->getItems());
        $this->assertSame($totalCount, $result->getTotalCount());
        $this->assertSame($currentPage, $result->getCurrentPage());
        $this->assertSame($itemsPerPage, $result->getItemsPerPage());
        $this->assertSame($metadata, $result->getMetadata());
        $this->assertSame(3, $result->getPageCount()); // ceil(25/10) = 3
    }

    public function testFromPaginationFactory(): void
    {
        $items = ['item1', 'item2'];
        $pagination = $this->createMock(PaginationInterface::class);
        $metadata = ['test' => 'data'];

        $pagination->method('getItems')->willReturn($items);
        $pagination->method('getTotalItemCount')->willReturn(15);
        $pagination->method('getCurrentPageNumber')->willReturn(1);
        $pagination->method('getItemNumberPerPage')->willReturn(5);

        $result = DataTableResult::fromPagination($pagination, $metadata);

        $this->assertSame($items, $result->getItems());
        $this->assertSame(15, $result->getTotalCount());
        $this->assertSame(1, $result->getCurrentPage());
        $this->assertSame(5, $result->getItemsPerPage());
        $this->assertSame($metadata, $result->getMetadata());
        $this->assertSame(3, $result->getPageCount()); // ceil(15/5) = 3
    }

    public function testEmptyFactory(): void
    {
        $result = DataTableResult::empty();

        $this->assertSame([], $result->getItems());
        $this->assertSame(0, $result->getTotalCount());
        $this->assertSame(1, $result->getCurrentPage());
        $this->assertSame(10, $result->getItemsPerPage());
        $this->assertSame([], $result->getMetadata());
        $this->assertSame(1, $result->getPageCount());
    }

    public function testPageCountCalculation(): void
    {
        // Test avec des divisions exactes
        $result1 = new DataTableResult([], 20, 1, 10);
        $this->assertSame(2, $result1->getPageCount());

        // Test avec des divisions non exactes
        $result2 = new DataTableResult([], 25, 1, 10);
        $this->assertSame(3, $result2->getPageCount());

        // Test avec zéro élément
        $result3 = new DataTableResult([], 0, 1, 10);
        $this->assertSame(1, $result3->getPageCount());
    }

    public function testNavigationMethods(): void
    {
        $result = new DataTableResult([], 30, 2, 10);

        $this->assertTrue($result->hasPreviousPage());
        $this->assertTrue($result->hasNextPage());
        $this->assertSame(1, $result->getPreviousPage());
        $this->assertSame(3, $result->getNextPage());
    }

    public function testNavigationMethodsFirstPage(): void
    {
        $result = new DataTableResult([], 30, 1, 10);

        $this->assertFalse($result->hasPreviousPage());
        $this->assertTrue($result->hasNextPage());
        $this->assertSame(1, $result->getPreviousPage()); // Reste à 1
        $this->assertSame(2, $result->getNextPage());
    }

    public function testNavigationMethodsLastPage(): void
    {
        $result = new DataTableResult([], 30, 3, 10);

        $this->assertTrue($result->hasPreviousPage());
        $this->assertFalse($result->hasNextPage());
        $this->assertSame(2, $result->getPreviousPage());
        $this->assertSame(3, $result->getNextPage()); // Reste à 3
    }

    public function testGetMetadataValue(): void
    {
        $metadata = ['key1' => 'value1', 'key2' => 'value2'];
        $result = new DataTableResult([], 10, 1, 10, $metadata);

        $this->assertSame('value1', $result->getMetadataValue('key1'));
        $this->assertSame('value2', $result->getMetadataValue('key2'));
        $this->assertNull($result->getMetadataValue('nonexistent'));
        $this->assertSame('default', $result->getMetadataValue('nonexistent', 'default'));
    }

    public function testToArray(): void
    {
        $items = ['item1', 'item2'];
        $metadata = ['test' => 'data'];
        $result = new DataTableResult($items, 25, 2, 10, $metadata);

        $array = $result->toArray();

        $expected = [
            'data' => $items,
            'pagination' => [
                'total' => 25,
                'page' => 2,
                'perPage' => 10,
                'pages' => 3,
                'hasPrevious' => true,
                'hasNext' => true,
                'previousPage' => 1,
                'nextPage' => 3,
            ],
            'metadata' => $metadata,
        ];

        $this->assertSame($expected, $array);
    }

    public function testConstructorValidationNegativeTotalCount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Total count cannot be negative');

        new DataTableResult([], -1, 1, 10);
    }

    public function testConstructorValidationZeroCurrentPage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Current page must be at least 1');

        new DataTableResult([], 10, 0, 10);
    }

    public function testConstructorValidationZeroItemsPerPage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Items per page must be at least 1');

        new DataTableResult([], 10, 1, 0);
    }

    public function testConstructorValidationNegativeItemsPerPage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Items per page must be at least 1');

        new DataTableResult([], 10, 1, -5);
    }

    public function testReadonlyBehavior(): void
    {
        $result = new DataTableResult(['item1'], 10, 1, 5);
        
        // Vérifier que les propriétés sont bien en lecture seule
        $this->assertSame(['item1'], $result->getItems());
        $this->assertSame(10, $result->getTotalCount());
        $this->assertSame(1, $result->getCurrentPage());
        $this->assertSame(5, $result->getItemsPerPage());
        $this->assertSame(2, $result->getPageCount());
    }

    public function testFromPaginationWithoutMetadata(): void
    {
        $items = ['a', 'b', 'c'];
        $pagination = $this->createMock(PaginationInterface::class);

        $pagination->method('getItems')->willReturn($items);
        $pagination->method('getTotalItemCount')->willReturn(50);
        $pagination->method('getCurrentPageNumber')->willReturn(3);
        $pagination->method('getItemNumberPerPage')->willReturn(20);

        $result = DataTableResult::fromPagination($pagination);

        $this->assertSame($items, $result->getItems());
        $this->assertSame(50, $result->getTotalCount());
        $this->assertSame(3, $result->getCurrentPage());
        $this->assertSame(20, $result->getItemsPerPage());
        $this->assertSame([], $result->getMetadata());
        $this->assertSame(3, $result->getPageCount()); // ceil(50/20) = 3
    }

    public function testLargeDataSetCalculations(): void
    {
        $result = new DataTableResult(
            array_fill(0, 100, 'item'),
            1000000,
            5000,
            100
        );

        $this->assertSame(1000000, $result->getTotalCount());
        $this->assertSame(10000, $result->getPageCount());
        $this->assertSame(5000, $result->getCurrentPage());
        $this->assertTrue($result->hasPreviousPage());
        $this->assertTrue($result->hasNextPage());
        $this->assertSame(4999, $result->getPreviousPage());
        $this->assertSame(5001, $result->getNextPage());
    }

    public function testComplexMetadata(): void
    {
        $complexMetadata = [
            'statistics' => [
                'total_revenue' => 15000.75,
                'average_order' => 125.50
            ],
            'filters' => [
                'status' => 'active',
                'date_range' => '2024-01-01 to 2024-12-31'
            ],
            'export_info' => [
                'formats' => ['pdf', 'csv', 'xlsx'],
                'last_export' => '2024-01-15 14:30:00'
            ]
        ];

        $result = new DataTableResult([], 0, 1, 10, $complexMetadata);

        $this->assertSame($complexMetadata, $result->getMetadata());
        $this->assertSame(15000.75, $result->getMetadataValue('statistics')['total_revenue']);
        $this->assertSame('active', $result->getMetadataValue('filters')['status']);
        $this->assertSame(['pdf', 'csv', 'xlsx'], $result->getMetadataValue('export_info')['formats']);
        $this->assertNull($result->getMetadataValue('nonexistent'));
    }

    public function testEdgeCaseSinglePage(): void
    {
        $result = new DataTableResult(['single_item'], 1, 1, 10);

        $this->assertSame(1, $result->getPageCount());
        $this->assertFalse($result->hasPreviousPage());
        $this->assertFalse($result->hasNextPage());
        $this->assertSame(1, $result->getPreviousPage());
        $this->assertSame(1, $result->getNextPage());
    }

    public function testEdgeCaseExactPageBoundary(): void
    {
        // Teste 20 éléments avec 10 par page = exactement 2 pages
        $result = new DataTableResult([], 20, 2, 10);

        $this->assertSame(2, $result->getPageCount());
        $this->assertTrue($result->hasPreviousPage());
        $this->assertFalse($result->hasNextPage());
        $this->assertSame(1, $result->getPreviousPage());
        $this->assertSame(2, $result->getNextPage());
    }

    public function testToArrayWithComplexData(): void
    {
        $items = [
            ['id' => 1, 'name' => 'Product A', 'price' => 99.99],
            ['id' => 2, 'name' => 'Product B', 'price' => 149.99]
        ];
        
        $metadata = [
            'total_value' => 249.98,
            'currency' => 'USD'
        ];

        $result = new DataTableResult($items, 55, 3, 20, $metadata);
        $array = $result->toArray();

        $this->assertSame($items, $array['data']);
        $this->assertSame(55, $array['pagination']['total']);
        $this->assertSame(3, $array['pagination']['page']);
        $this->assertSame(20, $array['pagination']['perPage']);
        $this->assertSame(3, $array['pagination']['pages']); // ceil(55/20) = 3
        $this->assertTrue($array['pagination']['hasPrevious']);
        $this->assertFalse($array['pagination']['hasNext']); // Page 3 sur 3
        $this->assertSame(2, $array['pagination']['previousPage']);
        $this->assertSame(3, $array['pagination']['nextPage']);
        $this->assertSame($metadata, $array['metadata']);
    }
}