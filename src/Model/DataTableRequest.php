<?php
declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Model;

/**
 * Classe représentant une requête pour le DataTableService
 */
class DataTableRequest
{
    /**
     * @param string $entityClass Classe de l'entité à afficher
     * @param int $page Numéro de la page courante
     * @param int $itemsPerPage Nombre d'éléments par page
     * @param string|null $sortField Champ de tri
     * @param string $sortDirection Direction du tri (ASC ou DESC)
     * @param string|null $search Terme de recherche
     * @param array $searchFields Champs dans lesquels rechercher
     * @param array $filters Filtres à appliquer (champ => valeur)
     * @param array $options Options supplémentaires
     */
    public function __construct(
        public readonly string $entityClass,
        public readonly int $page = 1,
        public readonly int $itemsPerPage = 10,
        public readonly ?string $sortField = null,
        public readonly string $sortDirection = 'ASC',
        public readonly ?string $search = null,
        public readonly array $searchFields = [],
        public readonly array $filters = [],
        public readonly array $options = []
    ) {}

    /**
     * Crée une instance à partir d'un tableau de paramètres
     */
    public static function fromArray(array $params): self
    {
        if (!isset($params['entityClass'])) {
            throw new \InvalidArgumentException('Le paramètre "entityClass" est obligatoire');
        }

        return new self(
            entityClass: $params['entityClass'],
            page: $params['page'] ?? 1,
            itemsPerPage: $params['itemsPerPage'] ?? 10,
            sortField: $params['sortField'] ?? null,
            sortDirection: $params['sortDirection'] ?? 'ASC',
            search: $params['search'] ?? null,
            searchFields: $params['searchFields'] ?? [],
            filters: $params['filters'] ?? [],
            options: $params['options'] ?? []
        );
    }

    /**
     * Crée une instance à partir d'une requête HTTP
     */
    public static function fromRequest(string $entityClass, array $requestData): self
    {
        return new self(
            entityClass: $entityClass,
            page: (int) ($requestData['page'] ?? 1),
            itemsPerPage: (int) ($requestData['limit'] ?? 10),
            sortField: $requestData['sort'] ?? null,
            sortDirection: $requestData['direction'] ?? 'ASC',
            search: $requestData['search'] ?? null,
            searchFields: $requestData['searchFields'] ?? [],
            filters: $requestData['filters'] ?? [],
            options: $requestData['options'] ?? []
        );
    }

    /**
     * Retourne une copie avec la page spécifiée
     */
    public function withPage(int $page): self
    {
        return new self(
            entityClass: $this->entityClass,
            page: $page,
            itemsPerPage: $this->itemsPerPage,
            sortField: $this->sortField,
            sortDirection: $this->sortDirection,
            search: $this->search,
            searchFields: $this->searchFields,
            filters: $this->filters,
            options: $this->options
        );
    }

    /**
     * Retourne une copie avec les filtres spécifiés
     */
    public function withFilters(array $filters): self
    {
        return new self(
            entityClass: $this->entityClass,
            page: $this->page,
            itemsPerPage: $this->itemsPerPage,
            sortField: $this->sortField,
            sortDirection: $this->sortDirection,
            search: $this->search,
            searchFields: $this->searchFields,
            filters: $filters,
            options: $this->options
        );
    }

    /**
     * Retourne une copie avec le tri spécifié
     */
    public function withSort(string $field, string $direction = 'ASC'): self
    {
        return new self(
            entityClass: $this->entityClass,
            page: $this->page,
            itemsPerPage: $this->itemsPerPage,
            sortField: $field,
            sortDirection: $direction,
            search: $this->search,
            searchFields: $this->searchFields,
            filters: $this->filters,
            options: $this->options
        );
    }
}