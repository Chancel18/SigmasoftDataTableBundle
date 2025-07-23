<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Twig\Components;

use Sigmasoft\DataTableBundle\Service\ConfigurationManager;
use Sigmasoft\DataTableBundle\Service\DataTableServiceInterface;
use Sigmasoft\DataTableBundle\Model\DataTableRequest;
use Sigmasoft\DataTableBundle\Service\EntityConfiguration;
use Sigmasoft\DataTableBundle\Service\RealtimeUpdateService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class SigmasoftDataTableComponent
{
    use DefaultActionTrait;

    #[LiveProp] public string $entityClass;
    #[LiveProp] public array $overrideConfig = [];

    // État dynamique
    #[LiveProp(writable: true)] public string $inputSearch = '';
    #[LiveProp(writable: true)] public int $page = 1;
    #[LiveProp(writable: true)] public array $filters = [];
    #[LiveProp(writable: true)] public array $selectedItems = [];
    #[LiveProp(writable: true)] public bool $showAlertMessage = false;
    #[LiveProp(writable: true)] public string $alertMessage = '';
    #[LiveProp(writable: true)] public string $alertType = 'success';

    #[LiveProp] public bool $realtimeEnabled = false;
    #[LiveProp] public bool $autoRefresh = false;
    #[LiveProp] public int $refreshInterval = 30000;

    private array $mercureTopics = [];

    private ?EntityConfiguration $entityConfig = null;
    private mixed $dataResult = null;
    private array $errors = [];

    public function __construct(
        private readonly DataTableServiceInterface $dataTableService,
        private readonly ConfigurationManager $configurationManager,
        private readonly RealtimeUpdateService $realtimeService,
    ) {}

    public function mount(string $entityClass, array $overrideConfig = []): void
    {
        try {
            $this->entityClass = $entityClass;
            $this->overrideConfig = $overrideConfig;

            if (!$this->configurationManager->hasEntityConfig($entityClass)) {
                throw new \InvalidArgumentException("No configuration found for entity: $entityClass");
            }

            $this->entityConfig = $this->configurationManager->getEntityConfig($entityClass);

            $this->setupRealtimeConfig();
            $this->initializeDefaults();
            $this->loadData();
        } catch (\Exception $e) {
            $this->addError('Erreur lors de l\'initialisation: ' . $e->getMessage());
        }
    }

    #[ExposeInTemplate('config')]
    public function getConfig(): EntityConfiguration
    {
        return $this->entityConfig;
    }

    #[ExposeInTemplate('items')]
    public function getItems(): mixed
    {
        return $this->dataResult;
    }

    #[ExposeInTemplate('hasErrors')]
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    #[ExposeInTemplate('errors')]
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getValue(mixed $item, string $field): mixed
    {
        $fieldConfig = $this->entityConfig->getField($field);
        $formatConfig = array_merge(
            $this->entityConfig->getFormatNumber(),
            $this->entityConfig->getFormatDate(),
            $fieldConfig['options'] ?? []
        );

        return $this->dataTableService->getValue($item, $field, $formatConfig);
    }

    #[LiveAction]
    public function sort(#[LiveArg] string $field): void
    {
        $fieldConfig = $this->entityConfig->getField($field);
        if (!$fieldConfig || !($fieldConfig['sortable'] ?? true)) {
            $this->addError('Ce champ n\'est pas triable');
            return;
        }

        $currentSort = $this->getCurrentSort();
        if ($field === $currentSort['field']) {
            $direction = $currentSort['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $direction = 'asc';
        }

        $this->filters['_sort'] = ['field' => $field, 'direction' => $direction];
        $this->page = 1;
        $this->loadData();
    }

    #[LiveAction]
    public function changePage(#[LiveArg] int $page): void
    {
        if ($page < 1) {
            return;
        }

        $this->page = $page;
        $this->loadData();
    }

    #[LiveAction]
    public function search(): void
    {
        $this->page = 1;
        $this->loadData();
    }

    #[LiveAction]
    public function clearSearch(): void
    {
        $this->inputSearch = '';
        $this->page = 1;
        $this->loadData();
    }

    #[LiveAction]
    public function applyFilter(#[LiveArg] string $field, #[LiveArg] mixed $value): void
    {
        if (empty($value)) {
            unset($this->filters[$field]);
        } else {
            $this->filters[$field] = $value;
        }

        $this->page = 1;
        $this->loadData();
    }

    #[LiveAction]
    public function clearFilters(): void
    {
        $this->filters = [];
        $this->page = 1;
        $this->loadData();
    }

    #[LiveAction]
    public function deleteItem(#[LiveArg] int $id): void
    {
        try {
            // Récupérer l'entité avant suppression pour le broadcast temps réel
            $entity = $this->dataTableService->findEntity($this->entityClass, $id);
            $success = $this->dataTableService->deleteEntity($this->entityClass, $id);

            if ($success) {
                $this->showAlert('Élément supprimé avec succès', 'success');
                $this->loadData();

                // Broadcast de la suppression en temps réel si entité trouvée
                if ($entity && $this->realtimeService) {
                    $this->realtimeService->broadcastRowUpdate(
                        $this->entityClass,
                        $entity,
                        'delete'
                    );
                }
            } else {
                $this->addError('Impossible de supprimer l\'élément');
            }
        } catch (\Exception $e) {
            $this->addError('Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    #[LiveAction]
    public function bulkAction(#[LiveArg] string $action): void
    {
        if (empty($this->selectedItems)) {
            $this->addError('Aucun élément sélectionné');
            return;
        }

        try {
            $result = $this->dataTableService->executeBulkAction(
                $this->entityClass,
                $action,
                $this->selectedItems
            );

            if ($result['success']) {
                $this->showAlert($result['message'], 'success');
                $this->selectedItems = [];
                $this->loadData();
            } else {
                $this->addError($result['message']);
            }
        } catch (\Exception $e) {
            $this->addError('Erreur lors de l\'action groupée: ' . $e->getMessage());
        }
    }

    #[LiveAction]
    public function export(#[LiveArg] string $format): void
    {
        try {
            $request = $this->buildDataTableRequest();
            $exportData = $this->dataTableService->exportData($request, $format);

            // Déclencher le téléchargement
            $this->showAlert('Export en cours...', 'info');
        } catch (\Exception $e) {
            $this->addError('Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    private function initializeDefaults(): void
    {
        $defaultSort = $this->entityConfig->getDefaultSort();
        $this->filters['_sort'] = $defaultSort;
    }

    private function loadData(): void
    {
        try {
            $request = $this->buildDataTableRequest();
            $this->dataResult = $this->dataTableService->getData($request);
            $this->clearErrors();
        } catch (\Exception $e) {
            $this->addError('Erreur lors du chargement: ' . $e->getMessage());
        }
    }

    private function buildDataTableRequest(): DataTableRequest
    {
        $sort = $this->getCurrentSort();

        return new DataTableRequest(
            entityClass: $this->entityClass,
            page: $this->page,
            itemsPerPage: $this->getEffectiveItemsPerPage(),
            sortField: $sort['field'],
            sortDirection: $sort['direction'],
            search: $this->inputSearch,
            searchFields: $this->entityConfig->getSearchFields(),
            filters: $this->getCleanFilters()
        );
    }

    private function getCurrentSort(): array
    {
        return $this->filters['_sort'] ?? $this->entityConfig->getDefaultSort();
    }

    private function getEffectiveItemsPerPage(): int
    {
        return $this->overrideConfig['items_per_page'] ?? $this->entityConfig->getItemsPerPage();
    }

    private function getCleanFilters(): array
    {
        $filters = $this->filters;
        unset($filters['_sort']);
        return $filters;
    }

    private function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    private function clearErrors(): void
    {
        $this->errors = [];
    }

    private function showAlert(string $message, string $type = 'success'): void
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
        $this->showAlertMessage = true;
    }

    #[ExposeInTemplate('streamName')]
    public function getStreamName(): string
    {
        return 'datatable-' . strtolower(str_replace('\\', '-', $this->entityClass));
    }

    #[ExposeInTemplate('mercureTopics')]
    public function getMercureTopics(): array
    {
        return $this->mercureTopics;
    }

    #[ExposeInTemplate('realtimeConfig')]
    public function getRealtimeConfig(): array
    {
        $config = $this->entityConfig->getRealtimeConfig();

        return [
            'enabled' => $this->realtimeEnabled,
            'autoRefresh' => $this->autoRefresh,
            'refreshInterval' => $this->refreshInterval,
            'mercureTopics' => $this->getMercureTopics(),
            'streamName' => $this->getStreamName()
        ];
    }

    #[ExposeInTemplate('currentSort')]
    public function getCurrentSortForTemplate(): array
    {
        return $this->getCurrentSort();
    }

    #[LiveAction]
    public function refreshData(): void
    {
        $this->loadData();

        // Notifier que la table a été rafraîchie manuellement
        $this->realtimeService->broadcastTableRefresh(
            $this->entityClass,
            ['manual_refresh' => true, 'user_id' => $this->getCurrentUserId()]
        );
    }

    #[LiveAction]
    public function toggleAutoRefresh(): void
    {
        $this->autoRefresh = !$this->autoRefresh;
    }


    private function setupRealtimeConfig(): void
    {
        $realtimeConfig = $this->entityConfig->getRealtimeConfig();

        $this->realtimeEnabled = $realtimeConfig['enabled'] ?? false;
        $this->autoRefresh = $realtimeConfig['auto_refresh'] ?? false;
        $this->refreshInterval = $realtimeConfig['refresh_interval'] ?? 30000;

        if ($this->realtimeEnabled && $realtimeConfig['mercure']) {
            $this->mercureTopics = $realtimeConfig['topics'] ?? [
                'datatable/' . strtolower(str_replace('\\', '/', $this->entityClass))
            ];
        }
    }

    private function getCurrentUserId(): ?int
    {
        // Implementation pour récupérer l'ID de l'utilisateur connecté
        return null;
    }
}
