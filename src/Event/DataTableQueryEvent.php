<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Événement lié aux requêtes DataTable
 */
class DataTableQueryEvent extends DataTableEvent
{
    private QueryBuilder $queryBuilder;
    private ?string $searchTerm = null;
    private ?string $sortField = null;
    private ?string $sortDirection = null;
    private int $currentPage = 1;
    private int $itemsPerPage = 10;
    private ?PaginationInterface $results = null;
    
    public function __construct(
        string $entityClass,
        QueryBuilder $queryBuilder,
        array $context = []
    ) {
        parent::__construct($entityClass, $context);
        $this->queryBuilder = $queryBuilder;
    }
    
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
    
    public function setQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }
    
    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }
    
    public function setSearchTerm(?string $searchTerm): self
    {
        $this->searchTerm = $searchTerm;
        return $this;
    }
    
    public function getSortField(): ?string
    {
        return $this->sortField;
    }
    
    public function setSortField(?string $sortField): self
    {
        $this->sortField = $sortField;
        return $this;
    }
    
    public function getSortDirection(): ?string
    {
        return $this->sortDirection;
    }
    
    public function setSortDirection(?string $sortDirection): self
    {
        $this->sortDirection = $sortDirection;
        return $this;
    }
    
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }
    
    public function setCurrentPage(int $currentPage): self
    {
        $this->currentPage = $currentPage;
        return $this;
    }
    
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
    
    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;
        return $this;
    }
    
    public function getResults(): ?PaginationInterface
    {
        return $this->results;
    }
    
    public function setResults(PaginationInterface $results): self
    {
        $this->results = $results;
        return $this;
    }
}