<?php

namespace Sigmasoft\DataTableBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\Model\DataTableRequest;

class DataTableRequestTest extends TestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $request = new DataTableRequest('App\Entity\User');

        $this->assertSame('App\Entity\User', $request->entityClass);
        $this->assertSame(1, $request->page);
        $this->assertSame(10, $request->itemsPerPage);
        $this->assertNull($request->sortField);
        $this->assertSame('ASC', $request->sortDirection);
        $this->assertNull($request->search);
        $this->assertSame([], $request->searchFields);
        $this->assertSame([], $request->filters);
        $this->assertSame([], $request->options);
    }

    public function testConstructorWithCustomValues(): void
    {
        $request = new DataTableRequest(
            entityClass: 'App\Entity\Product',
            page: 2,
            itemsPerPage: 20,
            sortField: 'name',
            sortDirection: 'DESC',
            search: 'test',
            searchFields: ['name', 'description'],
            filters: ['category' => 'electronics'],
            options: ['custom' => 'value']
        );

        $this->assertSame('App\Entity\Product', $request->entityClass);
        $this->assertSame(2, $request->page);
        $this->assertSame(20, $request->itemsPerPage);
        $this->assertSame('name', $request->sortField);
        $this->assertSame('DESC', $request->sortDirection);
        $this->assertSame('test', $request->search);
        $this->assertSame(['name', 'description'], $request->searchFields);
        $this->assertSame(['category' => 'electronics'], $request->filters);
        $this->assertSame(['custom' => 'value'], $request->options);
    }

    public function testFromArrayWithMinimalParams(): void
    {
        $params = ['entityClass' => 'App\Entity\User'];
        $request = DataTableRequest::fromArray($params);

        $this->assertSame('App\Entity\User', $request->entityClass);
        $this->assertSame(1, $request->page);
        $this->assertSame(10, $request->itemsPerPage);
    }

    public function testFromArrayWithAllParams(): void
    {
        $params = [
            'entityClass' => 'App\Entity\Product',
            'page' => 3,
            'itemsPerPage' => 25,
            'sortField' => 'price',
            'sortDirection' => 'DESC',
            'search' => 'laptop',
            'searchFields' => ['name', 'brand'],
            'filters' => ['inStock' => true],
            'options' => ['includeDeleted' => false]
        ];

        $request = DataTableRequest::fromArray($params);

        $this->assertSame('App\Entity\Product', $request->entityClass);
        $this->assertSame(3, $request->page);
        $this->assertSame(25, $request->itemsPerPage);
        $this->assertSame('price', $request->sortField);
        $this->assertSame('DESC', $request->sortDirection);
        $this->assertSame('laptop', $request->search);
        $this->assertSame(['name', 'brand'], $request->searchFields);
        $this->assertSame(['inStock' => true], $request->filters);
        $this->assertSame(['includeDeleted' => false], $request->options);
    }

    public function testFromArrayThrowsExceptionWhenEntityClassMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le paramètre "entityClass" est obligatoire');

        DataTableRequest::fromArray([]);
    }

    public function testFromRequest(): void
    {
        $requestData = [
            'page' => '2',
            'limit' => '15',
            'sort' => 'createdAt',
            'direction' => 'DESC',
            'search' => 'john',
            'searchFields' => ['firstName', 'lastName'],
            'filters' => ['role' => 'admin'],
            'options' => ['showInactive' => true]
        ];

        $request = DataTableRequest::fromRequest('App\Entity\User', $requestData);

        $this->assertSame('App\Entity\User', $request->entityClass);
        $this->assertSame(2, $request->page);
        $this->assertSame(15, $request->itemsPerPage);
        $this->assertSame('createdAt', $request->sortField);
        $this->assertSame('DESC', $request->sortDirection);
        $this->assertSame('john', $request->search);
        $this->assertSame(['firstName', 'lastName'], $request->searchFields);
        $this->assertSame(['role' => 'admin'], $request->filters);
        $this->assertSame(['showInactive' => true], $request->options);
    }

    public function testFromRequestWithDefaultValues(): void
    {
        $request = DataTableRequest::fromRequest('App\Entity\User', []);

        $this->assertSame('App\Entity\User', $request->entityClass);
        $this->assertSame(1, $request->page);
        $this->assertSame(10, $request->itemsPerPage);
        $this->assertNull($request->sortField);
        $this->assertSame('ASC', $request->sortDirection);
    }

    public function testWithPage(): void
    {
        $request = new DataTableRequest('App\Entity\User');
        $newRequest = $request->withPage(5);

        // Vérifier que l'objet original n'a pas changé
        $this->assertSame(1, $request->page);
        
        // Vérifier que le nouvel objet a la bonne page
        $this->assertSame(5, $newRequest->page);
        
        // Vérifier que les autres propriétés sont conservées
        $this->assertSame($request->entityClass, $newRequest->entityClass);
        $this->assertSame($request->itemsPerPage, $newRequest->itemsPerPage);
        $this->assertSame($request->sortField, $newRequest->sortField);
        $this->assertSame($request->sortDirection, $newRequest->sortDirection);
    }

    public function testWithFilters(): void
    {
        $request = new DataTableRequest(
            entityClass: 'App\Entity\Product',
            filters: ['category' => 'books']
        );
        
        $newFilters = ['category' => 'electronics', 'inStock' => true];
        $newRequest = $request->withFilters($newFilters);

        // Vérifier que l'objet original n'a pas changé
        $this->assertSame(['category' => 'books'], $request->filters);
        
        // Vérifier que le nouvel objet a les bons filtres
        $this->assertSame($newFilters, $newRequest->filters);
        
        // Vérifier que les autres propriétés sont conservées
        $this->assertSame($request->entityClass, $newRequest->entityClass);
        $this->assertSame($request->page, $newRequest->page);
    }

    public function testWithSort(): void
    {
        $request = new DataTableRequest(
            entityClass: 'App\Entity\User',
            sortField: 'name',
            sortDirection: 'ASC'
        );
        
        $newRequest = $request->withSort('email', 'DESC');

        // Vérifier que l'objet original n'a pas changé
        $this->assertSame('name', $request->sortField);
        $this->assertSame('ASC', $request->sortDirection);
        
        // Vérifier que le nouvel objet a le bon tri
        $this->assertSame('email', $newRequest->sortField);
        $this->assertSame('DESC', $newRequest->sortDirection);
    }

    public function testWithSortDefaultDirection(): void
    {
        $request = new DataTableRequest('App\Entity\User');
        $newRequest = $request->withSort('name');

        $this->assertSame('name', $newRequest->sortField);
        $this->assertSame('ASC', $newRequest->sortDirection);
    }

    public function testImmutability(): void
    {
        $originalFilters = ['status' => 'active'];
        $request = new DataTableRequest(
            entityClass: 'App\Entity\User',
            page: 1,
            filters: $originalFilters
        );

        // Modifier les filtres originaux
        $originalFilters['status'] = 'inactive';

        // Vérifier que l'objet request n'a pas été affecté
        $this->assertSame(['status' => 'active'], $request->filters);
    }

    /**
     * @dataProvider sortDirectionProvider
     */
    public function testSortDirectionValues(string $direction): void
    {
        $request = new DataTableRequest(
            entityClass: 'App\Entity\User',
            sortDirection: $direction
        );

        $this->assertSame($direction, $request->sortDirection);
    }

    public function sortDirectionProvider(): array
    {
        return [
            'ascending' => ['ASC'],
            'descending' => ['DESC'],
        ];
    }

    public function testComplexSearchFields(): void
    {
        $searchFields = [
            'name',
            'description',
            'category.name',
            'tags.label'
        ];

        $request = new DataTableRequest(
            entityClass: 'App\Entity\Product',
            searchFields: $searchFields
        );

        $this->assertSame($searchFields, $request->searchFields);
    }

    public function testComplexFilters(): void
    {
        $filters = [
            'status' => 'active',
            'price_min' => 10.50,
            'price_max' => 100,
            'categories' => [1, 2, 3],
            'hasStock' => true,
            'createdAfter' => '2023-01-01'
        ];

        $request = new DataTableRequest(
            entityClass: 'App\Entity\Product',
            filters: $filters
        );

        $this->assertSame($filters, $request->filters);
    }
}