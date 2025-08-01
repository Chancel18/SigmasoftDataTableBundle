---
sidebar_position: 6
title: Système d'événements
description: Guide complet du système d'événements du SigmasoftDataTableBundle
---

# Système d'événements

Le SigmasoftDataTableBundle fournit un système d'événements permettant d'intercepter et de modifier le comportement des DataTables à différents moments de leur cycle de vie.

## Événements disponibles

### Événements de requête

#### PRE_QUERY
Déclenché avant l'exécution de la requête Doctrine. Permet de modifier la requête avant son exécution.

```php
use Sigmasoft\DataTableBundle\Event\DataTableEvents;
use Sigmasoft\DataTableBundle\Event\DataTableQueryEvent;

public function onPreQuery(DataTableQueryEvent $event): void
{
    $queryBuilder = $event->getQueryBuilder();
    $entityClass = $event->getEntityClass();
    
    // Ajouter des filtres personnalisés
    if ($entityClass === Product::class) {
        $queryBuilder->andWhere('e.active = :active')
                     ->setParameter('active', true);
    }
}
```

#### POST_QUERY
Déclenché après l'exécution de la requête. Permet de traiter les résultats.

```php
public function onPostQuery(DataTableQueryEvent $event): void
{
    $results = $event->getResults();
    
    // Post-traitement des résultats
    foreach ($results->getItems() as $item) {
        // Charger des relations lazy
        if (method_exists($item, 'getImages')) {
            $item->getImages()->initialize();
        }
    }
}
```

### Événements d'édition inline

#### PRE_INLINE_EDIT
Déclenché avant la modification d'une valeur. Permet de valider ou transformer la valeur.

```php
use Sigmasoft\DataTableBundle\Event\InlineEditEvent;

public function onPreInlineEdit(InlineEditEvent $event): void
{
    $entity = $event->getEntity();
    $field = $event->getField();
    $newValue = $event->getNewValue();
    
    // Validation personnalisée
    if ($field === 'price' && $newValue < 0) {
        $event->addError('Le prix ne peut pas être négatif');
    }
    
    // Transformation de valeur
    if ($field === 'sku') {
        $event->setNewValue(strtoupper($newValue));
    }
}
```

#### POST_INLINE_EDIT
Déclenché après la modification réussie d'une valeur.

```php
public function onPostInlineEdit(InlineEditEvent $event): void
{
    $entity = $event->getEntity();
    $field = $event->getField();
    $oldValue = $event->getOldValue();
    $newValue = $event->getNewValue();
    
    // Actions post-édition
    if ($field === 'status' && $newValue === 'published') {
        $this->notificationService->notifyPublication($entity);
    }
    
    // Audit trail
    $this->auditLogger->log('inline_edit', [
        'entity' => get_class($entity),
        'id' => $entity->getId(),
        'field' => $field,
        'old_value' => $oldValue,
        'new_value' => $newValue,
        'user' => $this->security->getUser()->getUserIdentifier()
    ]);
}
```

## Créer un Event Listener

### 1. Créer la classe EventListener

```php title="src/EventListener/DataTableEventListener.php"
<?php

namespace App\EventListener;

use Sigmasoft\DataTableBundle\Event\DataTableEvents;
use Sigmasoft\DataTableBundle\Event\DataTableQueryEvent;
use Sigmasoft\DataTableBundle\Event\InlineEditEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;

class DataTableEventListener implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private YourCustomService $customService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DataTableEvents::PRE_QUERY => 'onPreQuery',
            DataTableEvents::POST_QUERY => 'onPostQuery',
            DataTableEvents::PRE_INLINE_EDIT => 'onPreInlineEdit',
            DataTableEvents::POST_INLINE_EDIT => 'onPostInlineEdit',
        ];
    }

    public function onPreQuery(DataTableQueryEvent $event): void
    {
        // Votre logique ici
    }

    public function onPostQuery(DataTableQueryEvent $event): void
    {
        // Votre logique ici
    }

    public function onPreInlineEdit(InlineEditEvent $event): void
    {
        // Votre logique ici
    }

    public function onPostInlineEdit(InlineEditEvent $event): void
    {
        // Votre logique ici
    }
}
```

### 2. Enregistrer le service

```yaml title="config/services.yaml"
services:
    App\EventListener\DataTableEventListener:
        tags:
            - { name: kernel.event_subscriber }
```

## Exemples d'utilisation

### Filtrage par tenant (multi-tenancy)

```php
public function onPreQuery(DataTableQueryEvent $event): void
{
    $queryBuilder = $event->getQueryBuilder();
    $currentTenant = $this->tenantContext->getCurrentTenant();
    
    if ($currentTenant) {
        $queryBuilder->andWhere('e.tenant = :tenant')
                     ->setParameter('tenant', $currentTenant);
    }
}
```

### Validation métier complexe

```php
public function onPreInlineEdit(InlineEditEvent $event): void
{
    $entity = $event->getEntity();
    $field = $event->getField();
    $newValue = $event->getNewValue();
    
    if ($entity instanceof Product && $field === 'stock') {
        // Vérifier les commandes en cours
        $pendingOrders = $this->orderRepository->countPendingForProduct($entity);
        
        if ($newValue < $pendingOrders) {
            $event->addError(sprintf(
                'Stock insuffisant : %d commandes en attente',
                $pendingOrders
            ));
        }
    }
}
```

### Synchronisation avec des services externes

```php
public function onPostInlineEdit(InlineEditEvent $event): void
{
    $entity = $event->getEntity();
    
    if ($entity instanceof Product) {
        // Synchroniser avec l'ERP
        try {
            $this->erpSync->updateProduct($entity);
        } catch (\Exception $e) {
            $this->logger->error('Erreur sync ERP', [
                'product_id' => $entity->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }
}
```

### Cache invalidation

```php
public function onPostInlineEdit(InlineEditEvent $event): void
{
    $entity = $event->getEntity();
    $field = $event->getField();
    
    // Invalider le cache pour certains champs critiques
    $criticalFields = ['name', 'slug', 'price', 'status'];
    
    if (in_array($field, $criticalFields)) {
        $this->cache->invalidateTags([
            'product_' . $entity->getId(),
            'product_list'
        ]);
    }
}
```

## Classes d'événements

### DataTableQueryEvent

```php
namespace Sigmasoft\DataTableBundle\Event;

class DataTableQueryEvent extends DataTableEvent
{
    public function getQueryBuilder(): QueryBuilder;
    public function setQueryBuilder(QueryBuilder $queryBuilder): self;
    
    public function getSearchTerm(): ?string;
    public function setSearchTerm(?string $searchTerm): self;
    
    public function getSortField(): ?string;
    public function setSortField(?string $sortField): self;
    
    public function getSortDirection(): ?string;
    public function setSortDirection(?string $sortDirection): self;
    
    public function getCurrentPage(): int;
    public function setCurrentPage(int $currentPage): self;
    
    public function getItemsPerPage(): int;
    public function setItemsPerPage(int $itemsPerPage): self;
    
    public function getResults(): ?PaginationInterface;
    public function setResults(PaginationInterface $results): self;
}
```

### InlineEditEvent

```php
namespace Sigmasoft\DataTableBundle\Event;

class InlineEditEvent extends DataTableEvent
{
    public function getEntity(): object;
    public function getField(): string;
    public function getOldValue(): mixed;
    public function getNewValue(): mixed;
    public function setNewValue(mixed $newValue): self;
    
    public function isValid(): bool;
    public function setValid(bool $valid): self;
    
    public function getErrors(): array;
    public function addError(string $error): self;
    public function setErrors(array $errors): self;
}
```

## Bonnes pratiques

### 1. Performance
- Évitez les opérations lourdes dans les listeners
- Utilisez des jobs asynchrones pour les traitements longs
- Mettez en cache les données fréquemment utilisées

### 2. Gestion d'erreurs
- Toujours capturer les exceptions dans les listeners
- Logger les erreurs pour le debugging
- Ne pas interrompre le flux principal

### 3. Testabilité
- Créez des listeners focalisés sur une seule responsabilité
- Injectez les dépendances via le constructeur
- Écrivez des tests unitaires pour vos listeners

### 4. Sécurité
- Vérifiez toujours les permissions dans les listeners
- Validez les données avant modification
- Évitez d'exposer des informations sensibles

## Prochaines étapes

- [Configuration avancée](./configuration.md)
- [Personnalisation](./customization.md)
- [API Reference](../api/overview.md)