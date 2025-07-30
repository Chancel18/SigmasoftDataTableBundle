<?php

/**
 * DataTableComponent - Composant LiveComponent pour tables de données interactives
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package Sigmasoft\DataTableBundle\Component
 */

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Component;

use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Configuration\SerializableDataTableConfig;
use Sigmasoft\DataTableBundle\DataProvider\DataProviderInterface;
use Sigmasoft\DataTableBundle\Service\ColumnFactory;
use Sigmasoft\DataTableBundle\Service\DataTableRegistryInterface;
use Sigmasoft\DataTableBundle\Exception\DataTableException;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent(name: 'sigmasoft_datatable', template: '@SigmasoftDataTable/datatable.html.twig')]
final class DataTableComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public SerializableDataTableConfig $config;

    #[LiveProp(writable: true)]
    public bool $showAlert = false;

    #[LiveProp]
    public string $alertMessage = 'Opération réussie';

    #[LiveProp]
    public string $alertType = 'success';

    #[LiveProp(writable: true)]
    public string $searchInput = '';

    private ?PaginationInterface $data = null;

    public function __construct(
        private DataProviderInterface $dataProvider,
        private EntityManagerInterface $entityManager,
        private ColumnFactory $columnFactory,
        private DataTableRegistryInterface $registry,
        private ?LoggerInterface $logger = null
    ) {}

    public function mount(DataTableConfiguration $configuration): void
    {
        $configId = $this->registry->generateId();
        $this->registry->register($configId, $configuration);
        
        $this->config = SerializableDataTableConfig::fromConfiguration($configId, $configuration);
        $this->searchInput = $this->config->searchQuery;
        $this->itemsPerPageValue = $this->config->itemsPerPage;
        
        $this->loadData();
    }

    #[ExposeInTemplate('data')]
    public function getData(): PaginationInterface
    {
        if ($this->data === null) {
            $this->loadData();
        }

        return $this->data;
    }

    #[ExposeInTemplate('configuration')]
    public function getConfiguration(): DataTableConfiguration
    {
        return $this->columnFactory->reconstructConfiguration($this->config, $this->registry);
    }

    #[LiveAction]
    public function sort(#[LiveArg('field')] string $field): void
    {
        $sortDirection = 'asc';
        if ($field === $this->config->sortField) {
            $sortDirection = $this->config->sortDirection === 'asc' ? 'desc' : 'asc';
        }

        $this->config = $this->config->withUpdates(
            null, // filters
            $field, // sortField
            $sortDirection, // sortDirection
            1, // page
            null, // itemsPerPage
            null // searchQuery
        );

        $this->resetCache();
        $this->loadData();
    }

    #[LiveAction]
    public function search(): void
    {
        $this->config = $this->config->withUpdates(
            null, // filters
            null, // sortField
            null, // sortDirection
            1, // page
            null, // itemsPerPage
            $this->searchInput // searchQuery
        );

        $this->resetCache();
        $this->loadData();
    }

    #[LiveAction]
    public function clearSearch(): void
    {
        $this->searchInput = '';
        $this->config = $this->config->withUpdates(
            null, // filters
            null, // sortField
            null, // sortDirection
            1, // page
            null, // itemsPerPage
            '' // searchQuery
        );

        $this->resetCache();
        $this->loadData();
    }

    #[LiveAction]
    public function changePage(#[LiveArg('page')] int $page): void
    {
        $this->config = $this->config->withUpdates(
            null, // filters
            null, // sortField
            null, // sortDirection
            max(1, $page), // page
            null, // itemsPerPage
            null // searchQuery
        );

        $this->resetCache();
        $this->loadData();
    }

    #[LiveProp(writable: true)]
    public int $itemsPerPageValue = 10;

    #[LiveAction]
    public function changeItemsPerPage(): void
    {
        $this->config = $this->config->withUpdates(
            null, // filters
            null, // sortField
            null, // sortDirection
            1, // page
            max(1, $this->itemsPerPageValue), // itemsPerPage
            null // searchQuery
        );

        $this->resetCache();
        $this->loadData();
    }

    #[LiveAction]
    public function filter(#[LiveArg] string $field, #[LiveArg] string $value): void
    {
        $filters = $this->config->filters;
        if ($value === '') {
            unset($filters[$field]);
        } else {
            $filters[$field] = $value;
        }

        $this->config = $this->config->withUpdates(
            $filters, // filters
            null, // sortField
            null, // sortDirection
            1, // page
            null, // itemsPerPage
            null // searchQuery
        );

        $this->resetCache();
        $this->loadData();
    }

    #[LiveAction]
    public function deleteItem(#[LiveArg] int $id): void
    {
        try {
            $configuration = $this->getConfiguration();
            $repository = $this->entityManager->getRepository($configuration->getEntityClass());
            $entity = $repository->find($id);

            if ($entity === null) {
                $this->showAlert('Élément non trouvé', 'error');
                return;
            }

            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            $this->resetCache();
            $this->loadData();
            $this->showAlert('Élément supprimé avec succès', 'success');
        } catch (\Exception $e) {
            $this->logger?->error('Error deleting item', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showAlert('Erreur lors de la suppression', 'error');
        }
    }

    #[LiveAction]
    public function dismissAlert(): void
    {
        $this->showAlert = false;
    }

    private function resetCache(): void
    {
        $this->data = null;
    }

    private function loadData(): void
    {
        $configuration = $this->getConfiguration();
        $this->data = $this->dataProvider->getData($configuration);
    }

    private function showAlert(string $message, string $type = 'success'): void
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
        $this->showAlert = true;
    }

    public function renderColumn(string $columnName, mixed $value, object $entity): string
    {
        $configuration = $this->getConfiguration();
        $column = $configuration->getColumn($columnName);

        if ($column === null) {
            return (string) $value;
        }

        return $column->render($value, $entity);
    }
}

