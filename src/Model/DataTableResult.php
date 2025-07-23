<?php
declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Model;

use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Classe représentant le résultat d'une requête au DataTableService
 */
final readonly class DataTableResult
{
    private int $pageCount;
    
    /**
     * @param array $items Éléments de la page courante
     * @param int $totalCount Nombre total d'éléments
     * @param int $currentPage Numéro de la page courante
     * @param int $itemsPerPage Nombre d'éléments par page
     * @param array $metadata Métadonnées supplémentaires
     */
    public function __construct(
        private array $items,
        private int $totalCount,
        private int $currentPage,
        private int $itemsPerPage,
        private array $metadata = []
    ) {
        $this->validateInputs();
        $this->pageCount = $this->calculatePageCount();
    }

    /**
     * Crée une instance depuis un objet de pagination KnpPaginator
     */
    public static function fromPagination(PaginationInterface $pagination, array $metadata = []): self
    {
        return new self(
            items: $pagination->getItems(),
            totalCount: $pagination->getTotalItemCount(),
            currentPage: $pagination->getCurrentPageNumber(),
            itemsPerPage: $pagination->getItemNumberPerPage(),
            metadata: $metadata
        );
    }

    /**
     * Crée un résultat vide
     */
    public static function empty(): self
    {
        return new self([], 0, 1, 10);
    }

    /**
     * Retourne les éléments de la page courante
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Retourne le nombre total d'éléments
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * Retourne le nombre total de pages
     */
    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    /**
     * Retourne le numéro de la page précédente
     */
    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    /**
     * Retourne le numéro de la page suivante
     */
    public function getNextPage(): int
    {
        return min($this->pageCount, $this->currentPage + 1);
    }

    /**
     * Retourne le numéro de la page courante
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Retourne le nombre d'éléments par page
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * Indique s'il existe une page précédente
     */
    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Indique s'il existe une page suivante
     */
    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->pageCount;
    }

    /**
     * Retourne toutes les métadonnées
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Retourne une métadonnée spécifique
     */
    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }



    /**
     * Convertit le résultat en tableau
     */
    public function toArray(): array
    {
        return [
            'data' => $this->items,
            'pagination' => [
                'total' => $this->totalCount,
                'page' => $this->currentPage,
                'perPage' => $this->itemsPerPage,
                'pages' => $this->pageCount,
                'hasPrevious' => $this->hasPreviousPage(),
                'hasNext' => $this->hasNextPage(),
                'previousPage' => $this->getPreviousPage(),
                'nextPage' => $this->getNextPage(),
            ],
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Valide les paramètres d'entrée
     */
    private function validateInputs(): void
    {
        if ($this->totalCount < 0) {
            throw new \InvalidArgumentException('Total count cannot be negative');
        }
        
        if ($this->currentPage < 1) {
            throw new \InvalidArgumentException('Current page must be at least 1');
        }
        
        if ($this->itemsPerPage < 1) {
            throw new \InvalidArgumentException('Items per page must be at least 1');
        }
    }

    /**
     * Calcule le nombre total de pages
     */
    private function calculatePageCount(): int
    {
        if ($this->itemsPerPage <= 0) {
            return 1;
        }
        
        return max(1, (int) ceil($this->totalCount / $this->itemsPerPage));
    }
}