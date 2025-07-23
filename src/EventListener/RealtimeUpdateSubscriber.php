<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\EventListener;

use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Sigmasoft\DataTableBundle\Service\ConfigurationManager;
use Sigmasoft\DataTableBundle\Service\RealtimeUpdateService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
class RealtimeUpdateSubscriber
{
    public function __construct(
        private readonly RealtimeUpdateService $realtimeService,
        private readonly ConfigurationManager $configurationManager
    ) {}

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityClass = get_class($entity);

        if ($this->shouldBroadcast($entityClass)) {
            $this->realtimeService->broadcastRowUpdate($entityClass, $entity, 'create');
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityClass = get_class($entity);

        if ($this->shouldBroadcast($entityClass)) {
            $this->realtimeService->broadcastRowUpdate($entityClass, $entity, 'update');
        }
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityClass = get_class($entity);

        if ($this->shouldBroadcast($entityClass)) {
            $this->realtimeService->broadcastRowUpdate($entityClass, $entity, 'delete');
        }
    }

    private function shouldBroadcast(string $entityClass): bool
    {
        if (!$this->configurationManager->hasEntityConfig($entityClass)) {
            return false;
        }

        $entityConfig = $this->configurationManager->getEntityConfig($entityClass);
        return $entityConfig->isRealtimeEnabled();
    }
}
