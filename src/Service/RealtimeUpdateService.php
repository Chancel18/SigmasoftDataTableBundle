<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;

class RealtimeUpdateService
{
    public function __construct(
        private readonly ConfigurationManager $configurationManager,
        private readonly ?HubInterface $hub = null,
        private readonly ?Environment $twig = null,
        private readonly bool $mercureEnabled = false,
        private readonly bool $turboEnabled = false
    ) {}

    public function broadcastDataTableUpdate(
        string $entityClass,
        string $eventType,
        mixed $data,
        array $context = []
    ): void {
        $entityConfig = $this->configurationManager->getEntityConfig($entityClass);
        $realtimeConfig = $entityConfig->getRealtimeConfig();

        if (!$realtimeConfig['enabled']) {
            return;
        }

        // Générer l'update Turbo Stream
        if ($realtimeConfig['turbo_streams'] && $this->turboEnabled && $this->twig !== null) {
            $this->broadcastTurboStream($entityClass, $eventType, $data, $context);
        }

        // Publier via Mercure
        if ($realtimeConfig['mercure'] && $this->mercureEnabled && $this->hub !== null) {
            $this->publishMercureUpdate($entityClass, $eventType, $data, $context, $realtimeConfig);
        }
    }

    public function broadcastTableRefresh(string $entityClass, array $context = []): void
    {
        $this->broadcastDataTableUpdate($entityClass, 'table.refresh', null, $context);
    }

    public function broadcastRowUpdate(string $entityClass, object $entity, string $action = 'update'): void
    {
        $this->broadcastDataTableUpdate($entityClass, "row.{$action}", $entity);
    }

    public function broadcastBulkUpdate(string $entityClass, array $entityIds, string $action): void
    {
        $this->broadcastDataTableUpdate($entityClass, "bulk.{$action}", ['ids' => $entityIds]);
    }

    private function broadcastTurboStream(
        string $entityClass,
        string $eventType,
        mixed $data,
        array $context
    ): void {
        $streamName = $this->generateStreamName($entityClass);

        match ($eventType) {
            'row.create' => $this->broadcastRowCreate($streamName, $data, $context),
            'row.update' => $this->broadcastRowUpdateToStream($streamName, $data, $context),
            'row.delete' => $this->broadcastRowDelete($streamName, $data, $context),
            'table.refresh' => $this->broadcastTableRefresh($streamName, $context),
            'bulk.update' => $this->broadcastBulkUpdate($streamName, $data, $context),
            default => $this->broadcastCustomUpdate($streamName, $eventType, $data, $context)
        };
    }

    private function publishMercureUpdate(
        string $entityClass,
        string $eventType,
        mixed $data,
        array $context,
        array $realtimeConfig
    ): void {
        $topics = $realtimeConfig['topics'] ?? [$this->generateDefaultTopic($entityClass)];

        $updateData = [
            'type' => $eventType,
            'entityClass' => $entityClass,
            'data' => $this->serializeData($data),
            'context' => $context,
            'timestamp' => time()
        ];

        $update = new Update(
            topics: $topics,
            data: json_encode($updateData),
            private: $this->shouldBePrivate($realtimeConfig, $context),
            id: uniqid('datatable_', true),
            type: 'datatable-update'
        );

        $this->hub->publish($update);
    }

    private function broadcastRowCreate(string $streamName, object $entity, array $context): void
    {
        $html = $this->renderRowHtml($entity, $context);

        TurboBundle::broadcastAction(
            stream: $streamName,
            action: 'append',
            target: 'datatable-tbody',
            content: $html
        );
    }

    private function broadcastRowUpdateToStream(string $streamName, object $entity, array $context): void
    {
        $html = $this->renderRowHtml($entity, $context);
        $rowId = "datatable-row-{$entity->getId()}";

        TurboBundle::broadcastAction(
            stream: $streamName,
            action: 'replace',
            target: $rowId,
            content: $html
        );
    }

    private function broadcastRowDelete(string $streamName, object $entity, array $context): void
    {
        $rowId = "datatable-row-{$entity->getId()}";

        TurboBundle::broadcastAction(
            stream: $streamName,
            action: 'remove',
            target: $rowId
        );
    }

//    private function broadcastTableRefresh(string $streamName, array $context): void
//    {
//        TurboBundle::broadcastAction(
//            stream: $streamName,
//            action: 'refresh'
//        );
//    }

    private function renderRowHtml(object $entity, array $context): string
    {
        $entityClass = get_class($entity);
        $entityConfig = $this->configurationManager->getEntityConfig($entityClass);

        return $this->twig->render('@SigmasoftDataTable/components/datatable/row.html.twig', [
            'item' => $entity,
            'config' => $entityConfig,
            'context' => $context
        ]);
    }

    private function generateStreamName(string $entityClass): string
    {
        return 'datatable-' . strtolower(str_replace('\\', '-', $entityClass));
    }

    private function generateDefaultTopic(string $entityClass): string
    {
        return 'datatable/' . strtolower(str_replace('\\', '/', $entityClass));
    }

    private function serializeData(mixed $data): mixed
    {
        if (is_object($data) && method_exists($data, 'getId')) {
            return [
                'id' => $data->getId(),
                'class' => get_class($data)
            ];
        }

        return $data;
    }

    private function shouldBePrivate(array $realtimeConfig, array $context): bool
    {
        return $realtimeConfig['private'] ?? true;
    }
}
