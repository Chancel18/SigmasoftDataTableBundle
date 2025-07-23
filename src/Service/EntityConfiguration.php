<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

class EntityConfiguration
{
    public function __construct(
        private readonly string $entityClass,
        private readonly array $config,
        private readonly array $templates
    ) {}

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getLabel(): string
    {
        return $this->config['label'] ?? $this->getClassBasename($this->entityClass);
    }

    private function getClassBasename(string $className): string
    {
        return basename(str_replace('\\', '/', $className));
    }

    public function getFields(): array
    {
        return $this->config['fields'] ?? [];
    }

    public function getField(string $fieldName): ?array
    {
        return $this->config['fields'][$fieldName] ?? null;
    }

    public function getSearchFields(): array
    {
        $configured = $this->config['search_fields'] ?? [];

        if (empty($configured)) {
            // Auto-generate from searchable fields
            $searchable = [];
            foreach ($this->getFields() as $name => $field) {
                if ($field['searchable'] ?? true) {
                    $searchable[] = $name;
                }
            }
            return $searchable;
        }

        return $configured;
    }

    public function getSortableFields(): array
    {
        $sortable = [];
        foreach ($this->getFields() as $name => $field) {
            if ($field['sortable'] ?? false) {
                $sortable[] = $name;
            }
        }
        return $sortable;
    }

    public function getDefaultSort(): array
    {
        return $this->config['default_sort'] ?? [
            'field' => array_key_first($this->getFields()),
            'direction' => 'asc'
        ];
    }

    public function getItemsPerPage(): int
    {
        return $this->config['items_per_page'] ?? 10;
    }

    public function isSearchEnabled(): bool
    {
        return $this->config['enable_search'] ?? true;
    }

    public function isSortEnabled(): bool
    {
        return $this->config['enable_sort'] ?? true;
    }

    public function isPaginationEnabled(): bool
    {
        return $this->config['enable_pagination'] ?? true;
    }

    public function areActionsEnabled(): bool
    {
        return $this->config['enable_actions'] ?? true;
    }

    public function isExportEnabled(): bool
    {
        return $this->config['enable_export'] ?? false;
    }

    public function areBulkActionsEnabled(): bool
    {
        return $this->config['enable_bulk_actions'] ?? false;
    }

    public function getActions(): array
    {
        return $this->config['actions'] ?? [];
    }

    public function getFilters(): array
    {
        return $this->config['filters'] ?? [];
    }

    public function getBulkActions(): array
    {
        return $this->config['bulk_actions'] ?? [];
    }

    public function getPermissions(): array
    {
        return $this->config['permissions'] ?? [];
    }

    public function getFormatNumber(): array
    {
        return $this->config['format_number'] ?? [];
    }

    public function getFormatDate(): array
    {
        return $this->config['format_date'] ?? [];
    }

    public function getCssClasses(): array
    {
        return $this->config['css_classes'] ?? [];
    }

    public function getExportConfig(): array
    {
        return $this->config['export'] ?? [];
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function getTemplate(string $type): string
    {
        return $this->templates[$type] ?? "@SigmasoftDataTable/components/datatable/{$type}.html.twig";
    }

    public function getRealtimeConfig(): array
    {
        return $this->config['realtime'] ?? [
            'enabled' => false,
            'turbo_streams' => false,
            'mercure' => false,
            'auto_refresh' => false,
            'refresh_interval' => 30000,
            'topics' => [],
            'events' => [],
            'filters' => [],
            'private' => true
        ];
    }

    public function isRealtimeEnabled(): bool
    {
        $config = $this->getRealtimeConfig();
        return $config['enabled'] ?? false;
    }

    public function isTurboStreamsEnabled(): bool
    {
        $config = $this->getRealtimeConfig();
        return $config['turbo_streams'] ?? false;
    }

    public function isMercureEnabled(): bool
    {
        $config = $this->getRealtimeConfig();
        return $config['mercure'] ?? false;
    }

    public function getRealtimeTopics(): array
    {
        $config = $this->getRealtimeConfig();
        return $config['topics'] ?? [];
    }

    public function getRealtimeEvents(): array
    {
        $config = $this->getRealtimeConfig();
        return $config['events'] ?? [];
    }
}
