# SigmasoftDataTableBundle

Un bundle Symfony moderne et flexible pour créer des tables de données interactives avec LiveComponents.

## 🚀 Fonctionnalités

- ✅ **Architecture modulaire** - Séparation claire des responsabilités avec interfaces
- ✅ **Types de colonnes extensibles** - Text, Date, Badge, Action et colonnes personnalisées
- ✅ **Recherche intégrée** - Recherche en temps réel sur plusieurs champs avec délai configurable
- ✅ **Tri dynamique** - Tri sur toutes les colonnes configurées sans blocage JavaScript
- ✅ **Pagination intelligente** - Avec navigation et sélection d'éléments par page
- ✅ **Actions CRUD** - Support intégré pour les actions Show, Edit, Delete avec confirmation
- ✅ **Responsive** - Interface adaptée à tous les écrans
- ✅ **Testable** - Architecture découplée facilitant les tests
- ✅ **PSR conformes** - Respect des standards PHP et Symfony
- ✅ **Validation robuste** - Exceptions typées et validation des paramètres
- ✅ **Logging PSR-3** - Gestion d'erreurs avec logging structuré
- ✅ **Production Ready** - Code nettoyé sans logs de débogage
- ✅ **LiveComponent optimisé** - Template avec élément racine unique, actions sécurisées
- ✅ **Confirmation sécurisée** - Système de confirmation robuste pour les suppressions
- ✅ **Configuration YAML complète** - Tous les aspects configurables via YAML
- ✅ **Configuration PHP orientée objet** - Classes de configuration réutilisables et typées
- ✅ **Résolution automatique de configuration** - Choix automatique entre PHP, YAML et manuel
- ✅ **Commande Maker intégrée** - Génération automatique avec `make:datatable`
- ✅ **Détection automatique de types** - Mapping intelligent Doctrine → DataTable
- ✅ **Édition inline avancée** - Modification directe des cellules avec sauvegarde automatique
- ✅ **Export complet** - Export CSV et Excel avec PhpSpreadsheet
- ✅ **Actions groupées** - Sauvegarde et annulation en lot pour l'édition inline
- ✅ **Renderers extensibles** - Système modulaire pour créer des types de champs personnalisés
- ✅ **Types avancés** - Support natif pour color picker, textarea, email, etc.

## 📦 Installation

1. Ajoutez le bundle à votre application Symfony :

```php
// config/bundles.php
return [
    // ... autres bundles
    Sigmasoft\DataTableBundle\SigmasoftDataTableBundle::class => ['all' => true],
];
```

2. Configurez le bundle (optionnel) :

```yaml
# config/packages/sigmasoft_data_table.yaml
sigmasoft_data_table:
    defaults:
        items_per_page: 15
        enable_search: true
        enable_pagination: true
        enable_sorting: true
        table_class: 'table table-striped table-hover'
        date_format: 'd/m/Y H:i'
    templates:
        datatable: '@SigmasoftDataTable/datatable.html.twig'
```

## ⚙️ Méthodes de Configuration

Le bundle propose **3 approches** pour configurer vos DataTables :

| Approche | Complexité | Flexibilité | Maintenance | Cas d'usage |
|----------|------------|--------------|-------------|-------------|
| **Configuration Manuelle** | Simple | Limitée | Directe | Prototypes, cas simples |
| **Configuration PHP** | Moyenne | Élevée | Excellente | Projets complexes, logique conditionnelle |
| **Configuration YAML** | Simple | Moyenne | Excellente | Configuration déclarative, équipes mixtes |

### 🔄 Résolution Automatique

Le système suit cet **ordre de priorité** :

1. **Configuration PHP enregistrée** (service container)  
2. **Fichier YAML** (`config/datatable/{entity}.yaml`)  
3. **Configuration manuelle** (par défaut)

```php
// Cette méthode essaie automatiquement :
// 1. Chercher une classe UserDataTableConfig enregistrée
// 2. Chercher config/datatable/user.yaml  
// 3. Permettre la configuration manuelle
$config = $builder->createDataTableFromConfig(User::class);
```

## 🎯 1. Configuration Manuelle (Traditionnelle)

### Dans votre contrôleur :

```php
<?php

namespace App\Controller;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Column\ActionColumn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'app_user_index')]
    public function index(DataTableBuilder $builder): Response
    {
        // Configuration basique
        $config = $builder->createDataTable(User::class);
        
        // API fluide améliorée - chaque méthode retourne la configuration
        $config = $builder
            ->addTextColumn($config, 'id', 'id', 'ID', ['sortable' => true, 'searchable' => false])
            ->addTextColumn($config, 'email', 'email', 'Email')
            ->addTextColumn($config, 'name', 'name', 'Nom', ['truncate' => true, 'truncate_length' => 30])
            ->addDateColumn($config, 'createdAt', 'Créé le', ['format' => 'd/m/Y H:i'])
            ->addBadgeColumn($config, 'status', 'status', 'Statut', [
                'badge_class' => 'bg-success',
                'value_mapping' => [
                    'active' => 'Actif',
                    'inactive' => 'Inactif'
                ]
            ]);
        
        // Actions configurées séparément pour plus de clarté
        $config = $builder->addActionColumn($config, [
                'show' => [
                    'route' => 'app_user_show',
                    'icon' => 'bi bi-eye',
                    'class' => 'btn btn-sm btn-info',
                    'title' => 'Voir'
                ],
                'edit' => [
                    'route' => 'app_user_edit',
                    'icon' => 'bi bi-pencil',
                    'class' => 'btn btn-sm btn-warning'
                ],
                'delete' => [
                    'type' => 'delete',
                    'icon' => 'bi bi-trash',
                    'class' => 'btn btn-sm btn-danger',
                    'confirm' => 'Êtes-vous sûr de vouloir supprimer cet utilisateur ?'
                ]
            ]);
        
        // Configuration finale
        $config = $builder
            ->configureSearch($config, true, ['email', 'name'])
            ->configurePagination($config, true, 10)
            ->configureSorting($config, true, 'createdAt', 'desc');

        return $this->render('user/index.html.twig', [
            'datatableConfig' => $config,
        ]);
    }
}
```

### Dans votre template :

```twig
{# templates/user/index.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}Liste des utilisateurs{% endblock %}

{% block body %}
<div class="container-fluid">
    <h1>Gestion des utilisateurs</h1>
    
    {{ component('sigmasoft_datatable', {
        configuration: datatableConfig
    }) }}
</div>
{% endblock %}
```

## 🏗️ 2. Configuration PHP (Classes)

### Avantages
- ✅ **Type Safety** avec auto-complétion IDE
- ✅ **Logique conditionnelle** (permissions, contexte)  
- ✅ **Réutilisabilité** et héritage
- ✅ **Tests unitaires** faciles
- ✅ **Injection de dépendances** possible

### Créer une classe de configuration :

```php
<?php
// src/SigmasoftDataTableBundle/Configuration/DataTableConfig/UserDataTableConfig.php

namespace Sigmasoft\DataTableBundle\Configuration\DataTableConfig;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Column\DateColumn;
use Sigmasoft\DataTableBundle\Column\BadgeColumn;
use Sigmasoft\DataTableBundle\Configuration\AbstractDataTableConfiguration;

class UserDataTableConfig extends AbstractDataTableConfiguration
{
    public function getEntityClass(): string
    {
        return User::class;
    }
    
    public function configure(): void
    {
        // Colonnes
        $this->addColumn(new TextColumn('id', 'id', 'ID'));
        $this->addColumn(new TextColumn('firstName', 'firstName', 'Prénom'));
        $this->addColumn(new TextColumn('lastName', 'lastName', 'Nom'));
        $this->addColumn(new TextColumn('email', 'email', 'Email'));
        $this->addColumn(new DateColumn('createdAt', 'createdAt', 'Créé le'));
        $this->addColumn(new BadgeColumn('isActive', 'isActive', 'Statut', true, false, [
            'value_mapping' => [
                true => 'Actif',
                false => 'Inactif'
            ],
            'badge_class' => 'bg-success'
        ]));
        
        // Configuration
        $this->setSearchEnabled(true);
        $this->setSearchableFields(['firstName', 'lastName', 'email']);
        $this->setPaginationEnabled(true);
        $this->setItemsPerPage(15);
        $this->setSortField('lastName');
        $this->setSortDirection('asc');
        $this->setExportEnabled(true);
        $this->setExportFormats(['csv', 'excel']);
    }
}
```

### Utilisation dans le contrôleur :

```php
<?php

class UserController extends AbstractController
{
    #[Route('/users', name: 'users_list')]
    public function list(DataTableBuilder $builder): Response
    {
        // Méthode 1: Configuration explicite
        $userConfig = new UserDataTableConfig();
        $configurator = new PhpConfigurator($userConfig);
        
        $config = $builder->createDataTable(User::class);
        $builder->setCurrentConfig($config);
        $configurator->configure($builder);
        
        return $this->render('user/list.html.twig', [
            'datatableConfig' => $config
        ]);
    }
    
    #[Route('/users/auto', name: 'users_list_auto')]
    public function listAuto(DataTableBuilder $builder): Response
    {
        // Méthode 2: Configuration automatique
        $config = $builder->createDataTableFromConfig(User::class);
        
        return $this->render('user/list.html.twig', [
            'datatableConfig' => $config
        ]);
    }
}
```

## 📄 3. Configuration YAML (Fichiers)

### Avantages
- ✅ **Syntaxe déclarative** simple
- ✅ **Modifications sans recompilation**
- ✅ **Séparation** configuration/code
- ✅ **Accessible** aux non-développeurs

### Structure des fichiers :

```
config/datatable/
├── global.yaml      # Configuration globale (optionnel)
├── user.yaml       # Configuration pour User
├── product.yaml    # Configuration pour Product
└── order.yaml      # Configuration pour Order
```

### Exemple de configuration YAML :

```yaml
# config/datatable/user.yaml
datatable:
  entity: App\Entity\User
  
  columns:
    - field: id
      property: id
      label: ID
      type: text
      sortable: true
      searchable: false
      
    - field: firstName
      property: firstName
      label: Prénom
      type: text
      
    - field: lastName
      property: lastName
      label: Nom
      type: text
      
    - field: email
      property: email
      label: Email
      type: text
      
    - field: createdAt
      property: createdAt
      label: Créé le
      type: date
      format: d/m/Y H:i
      
    - field: isActive
      property: isActive
      label: Statut
      type: badge
      options:
        "true":
          label: Actif
          class: badge-success
        "false":
          label: Inactif
          class: badge-danger
  
  search:
    enabled: true
    fields: [firstName, lastName, email]
    
  pagination:
    enabled: true
    items_per_page: 10
    items_per_page_options: [10, 25, 50, 100]
    
  sorting:
    field: lastName
    direction: asc
    
  table_class: table table-striped table-hover
  date_format: d/m/Y
  
  export:
    enabled: true
    formats: [csv, excel]
```

### Configuration globale :

```yaml
# config/datatable/global.yaml
datatable:
  defaults:
    table_class: table table-striped table-hover
    date_format: d/m/Y
    
    search:
      enabled: true
      
    pagination:
      enabled: true
      items_per_page: 10
      items_per_page_options: [10, 25, 50, 100]
      
    sorting:
      direction: asc
      
    export:
      enabled: false
      formats: [csv]
```

### Utilisation dans le contrôleur :

```php
<?php

class UserController extends AbstractController
{
    #[Route('/users', name: 'users_list')]
    public function list(DataTableBuilder $builder): Response
    {
        // Le builder va automatiquement chercher config/datatable/user.yaml
        $config = $builder->createDataTableFromConfig(User::class);
        
        return $this->render('user/list.html.twig', [
            'datatableConfig' => $config
        ]);
    }
}
```

## 🔄 Migration entre Approches

### De Manuelle vers PHP
```php
// Avant (Manuelle)
$builder
    ->addColumn(new TextColumn('name', 'name', 'Nom'))
    ->configureSearch(true, ['name']);

// Après (PHP)
class UserConfig extends AbstractDataTableConfiguration {
    public function configure(): void {
        $this->addColumn(new TextColumn('name', 'name', 'Nom'));
        $this->setSearchEnabled(true);
        $this->setSearchableFields(['name']);
    }
}
```

### De Manuelle vers YAML
```php
// Avant (Manuelle)
$builder->addColumn(new TextColumn('name', 'name', 'Nom'));

// Après (YAML)
# config/datatable/user.yaml
datatable:
  columns:
    - field: name
      property: name
      label: Nom
      type: text
```

## 🛡️ Gestion d'Erreurs et Validation

### Validation automatique :

```php
// Les paramètres sont validés automatiquement
try {
    $config = $builder->createDataTable('InvalidEntityClass'); // Lève DataTableException
} catch (DataTableException $e) {
    // Gestion de l'erreur
}

// Configuration avec validation
$config->setPage(-1); // Lève DataTableException::invalidPage
$config->setSortDirection('invalid'); // Lève DataTableException::invalidSortDirection
```

### Logging intégré :

```php
// Le composant utilise automatiquement le logger PSR-3
// En cas d'erreur, les détails sont loggés avec contexte
```

## 🎨 Utilisation Avancée

### Colonnes personnalisées :

```php
use Sigmasoft\DataTableBundle\Column\AbstractColumn;

class CustomColumn extends AbstractColumn 
{
    protected function doRender(mixed $value, object $entity): string
    {
        // Votre logique de rendu personnalisée
        return sprintf('<span class="custom-class">%s</span>', $value);
    }
}

// Utilisation
$builder->addCustomColumn($config, new CustomColumn('custom', 'customProperty', 'Label'));
```

### DataProvider personnalisé :

```php
use Sigmasoft\DataTableBundle\DataProvider\DataProviderInterface;

class CustomDataProvider implements DataProviderInterface
{
    public function getData(DataTableConfiguration $configuration): PaginationInterface
    {
        // Votre logique de récupération de données
    }
    
    // ... autres méthodes
}
```

### Configuration via Factory :

```php
use Sigmasoft\DataTableBundle\Factory\DataTableFactory;

class UserController extends AbstractController
{
    public function index(DataTableFactory $factory): Response
    {
        $config = $factory->createForEntity(User::class, [], [
            'items_per_page' => 25,
            'enable_search' => true,
            'sort_field' => 'name',
            'sort_direction' => 'asc'
        ]);
        
        // Ajout des colonnes...
        
        return $this->render('user/index.html.twig', [
            'datatableConfig' => $config,
        ]);
    }
}
```

## 🎯 Types de Colonnes

### TextColumn
```php
$builder->addTextColumn($config, 'name', 'name', 'Nom', [
    'truncate' => true,
    'truncate_length' => 50,
    'escape' => true,
    'empty_value' => 'N/A'
]);
```

### DateColumn
```php
$builder->addDateColumn($config, 'createdAt', 'createdAt', 'Date de création', [
    'format' => 'd/m/Y H:i:s',
    'empty_value' => 'Non définie'
]);
```

### BadgeColumn
```php
$builder->addBadgeColumn($config, 'status', 'status', 'Statut', [
    'badge_class' => 'bg-primary',
    'value_mapping' => [
        'draft' => 'Brouillon',
        'published' => 'Publié'
    ]
]);
```

### ActionColumn
```php
$builder->addActionColumn($config, [
    'view' => [
        'route' => 'app_item_show',
        'route_params' => ['id' => '{{ item.id }}'],
        'icon' => 'bi bi-eye',
        'class' => 'btn btn-sm btn-info'
    ],
    'delete' => [
        'type' => 'delete',
        'confirm' => 'Confirmer la suppression ?'
    ]
]);
```

## ⚙️ Configuration Complète

```yaml
# config/packages/sigmasoft_data_table.yaml
sigmasoft_data_table:
    defaults:
        items_per_page: 10          # Nombre d'éléments par page par défaut
        enable_search: true         # Activer la recherche
        enable_pagination: true     # Activer la pagination  
        enable_sorting: true        # Activer le tri
        table_class: 'table table-striped table-hover align-middle'  # Classes CSS de la table
        date_format: 'd/m/Y'       # Format de date par défaut
        pagination_sizes: [5, 10, 25, 50, 100]  # Options de pagination
    
    templates:
        datatable: 'bundles/SigmasoftDataTableBundle/datatable.html.twig'
    
    caching:
        enabled: false              # Cache des résultats (à implémenter)
        ttl: 3600                  # Durée de vie du cache
    
    # 🆕 Configuration pour la commande Maker
    maker:
        # Mapping automatique des types Doctrine vers les types de colonnes
        default_column_types:
            string: 'text'
            text: 'text'
            integer: 'text'
            float: 'text'
            decimal: 'text'
            boolean: 'badge'           # Affichage en badge pour Oui/Non
            datetime: 'date'
            datetime_immutable: 'date'
            date: 'date'
            date_immutable: 'date'
            time: 'date'
            time_immutable: 'date'
        
        # Propriétés exclues automatiquement de la génération
        excluded_properties: ['password', 'plainPassword', 'salt', 'token', 'resetToken']
        
        # Générer automatiquement les actions CRUD
        auto_add_actions: true
        
        # Configuration par défaut des actions
        default_actions:
            show:
                icon: 'bi bi-eye'
                class: 'btn btn-sm btn-info'
                title: 'Voir'
            edit:
                icon: 'bi bi-pencil-square'
                class: 'btn btn-sm btn-warning'
                title: 'Modifier'
            delete:
                type: 'delete'
                icon: 'bi bi-trash'
                class: 'btn btn-sm btn-danger'
                title: 'Supprimer'
                confirm: 'Êtes-vous sûr de vouloir supprimer cet élément ?'
```

## 🏗️ Architecture

Le bundle suit une architecture modulaire avec :

- **Configuration** : Gestion centralisée des paramètres avec validation
- **Columns** : Types de colonnes extensibles avec rendu personnalisé  
- **DataProvider** : Abstraction de la source de données (Doctrine, API, etc.)
- **Builder** : API véritablement fluide pour la construction des tables
- **Component** : LiveComponent pour l'interactivité avec logging PSR-3
- **Factory** : Création simplifiée des configurations
- **Services** : Interfaces pour l'injection de dépendances (DataTableRegistryInterface)
- **Exceptions** : Gestion d'erreurs typée avec DataTableException

## 🧪 Bonnes Pratiques

1. **Séparez la logique métier** - Utilisez des services dédiés pour la configuration complexe
2. **Réutilisez les configurations** - Créez des classes de configuration pour vos entités courantes
3. **Optimisez les performances** - Limitez le nombre de colonnes et utilisez la pagination
4. **Testez vos colonnes** - Créez des tests unitaires pour vos colonnes personnalisées
5. **Gérez les erreurs** - Capturez les DataTableException pour une gestion d'erreurs robuste
6. **Utilisez les interfaces** - Injectez DataTableRegistryInterface au lieu de la classe concrète
7. **Surveillez les logs** - Le composant log automatiquement les erreurs avec contexte

## 🔧 Corrections Récentes et Améliorations

### ✅ Correction du Template LiveComponent (v1.2.0)

**Problème résolu** : Template avec éléments multiples causant l'erreur "Component HTML contains 2 elements, but only 1 root element is allowed"

**Solution** : Restructuration du template pour avoir un seul élément racine :

```twig
<div{{ attributes }} class="sigmasoft-datatable">
    {% embed '@components/card_default.html.twig' %}
        <!-- Contenu du composant -->
    {% endembed %}
    
    <!-- Styles intégrés dans le conteneur principal -->
    <style>
        .sigmasoft-datatable .sortable-header { ... }
    </style>
</div>
```

**Impact** : Le tri et toutes les actions LiveComponent fonctionnent maintenant sans bloquer JavaScript.

### ✅ Correction de la Confirmation de Suppression (v1.2.1)

**Problème résolu** : La suppression s'exécutait même lorsque l'utilisateur cliquait "Annuler" dans la confirmation.

**Solution** : Amélioration du JavaScript de confirmation pour bloquer l'action LiveComponent :

```php
// Avant (défaillant)
onclick="return confirm('...');"

// Après (corrigé)
onclick="if(!confirm('...')) { event.preventDefault(); event.stopPropagation(); return false; }"
```

**Impact** : La suppression ne s'exécute maintenant que si l'utilisateur confirme explicitement avec "OK".

### ✅ Amélioration de la Recherche (v1.2.0)

**Ajout** : Délai configurable sur la recherche pour éviter les requêtes excessives :

```twig
<input data-action="input->live#action:1000" ... >
```

**Impact** : Recherche déclenchée après 1000ms d'inactivité au lieu de chaque frappe.

### ✅ Optimisation de la Pagination (v1.2.0)

**Correction** : Affichage conditionnel de la pagination basé sur la configuration plutôt que le nombre de pages :

```twig
{% if config.isPaginationEnabled() %}
    <!-- Pagination toujours visible si activée -->
{% endif %}
```

**Impact** : Interface plus cohérente, pagination visible même avec peu d'éléments.

### 🐛 Débogage et Tests

Pour tester les corrections, utilisez la route `/example/users` :

1. **Test du tri** : Cliquez sur les en-têtes de colonnes - doit trier sans erreur JavaScript
2. **Test de la recherche** : Tapez dans le champ de recherche - délai de 1000ms avant exécution
3. **Test de la suppression** : Cliquez sur supprimer, puis "Annuler" - aucune suppression ne doit avoir lieu
4. **Test de la pagination** : Navigation entre les pages sans erreur

### 📊 Informations de Débogage

En mode debug (`app.debug = true`), le composant affiche :

```twig
<div class="alert alert-info small">
    <strong>Debug:</strong> Page 1/3 | 25 éléments | 
    Recherche: "test" | Tri: name asc
</div>
```

Cette information aide au diagnostic des problèmes de fonctionnement.

## 🚀 Commande Maker - Génération Automatique

### 🎯 Commande `make:datatable`

Le bundle propose une commande Maker pour générer automatiquement des DataTables :

```bash
# Génération automatique pour une entité
php bin/console make:datatable User

# Avec spécification du contrôleur
php bin/console make:datatable User --controller=UserController

# Forcer la génération même si une configuration existe
php bin/console make:datatable User --force
```

### ✅ Conditions Requises

La commande vérifie automatiquement :

1. **Entité existe** - L'entité doit être définie avec Doctrine
2. **Contrôleur existe** - Détection automatique ou spécification manuelle
3. **Méthode `index()` existe** - Le contrôleur doit avoir une méthode index
4. **Template `index.html.twig` existe** - Le template doit être présent dans `templates/entity_name/index.html.twig`

### 🔍 Détection Automatique

La commande détecte automatiquement :

**Types de colonnes** basés sur les types Doctrine :
- `string`, `text` → `TextColumn`
- `integer`, `float`, `decimal` → `TextColumn` (formatage numérique)
- `boolean` → `BadgeColumn` (Oui/Non avec style)
- `datetime`, `date`, `time` → `DateColumn` (formatage automatique)

**Propriétés de colonnes** :
- **Searchable** : Automatique pour les champs texte
- **Sortable** : Automatique sauf pour les champs `text` (longs)
- **Options spéciales** : Format de date, mapping de valeurs pour boolean

**Actions CRUD** :
- Routes générées automatiquement : `app_{entity}_show`, `app_{entity}_edit`
- Actions configurables via YAML
- Confirmation automatique pour la suppression

### 📝 Exemple de Code Généré

Pour l'entité `User`, la commande génère :

```php
// Dans UserController::index()
public function index(DataTableBuilder $builder): Response
{
    // Génération automatique DataTable pour User
    $config = $builder->createDataTable(User::class);

    $config = $builder->addTextColumn($config, 'id', 'id', 'Id', ['sortable' => true, 'searchable' => false]);
    $config = $builder->addTextColumn($config, 'code', 'code', 'Code', ['sortable' => true, 'searchable' => true]);
    $config = $builder->addTextColumn($config, 'name', 'name', 'Name', ['sortable' => true, 'searchable' => true]);
    $config = $builder->addTextColumn($config, 'email', 'email', 'Email', ['sortable' => true, 'searchable' => true]);
    $config = $builder->addBadgeColumn($config, 'status', 'status', 'Status', [
        'value_mapping' => ['1' => 'Oui', '0' => 'Non'],
        'sortable' => true
    ]);
    $config = $builder->addDateColumn($config, 'createdAt', 'createdAt', 'Created At', ['format' => 'd/m/Y H:i']);

    // Actions CRUD
    $config = $builder->addActionColumn($config, [
        'show' => [
            'route' => 'app_user_show',
            'icon' => 'bi bi-eye',
            'class' => 'btn btn-sm btn-info',
            'title' => 'Voir'
        ],
        'edit' => [
            'route' => 'app_user_edit',
            'icon' => 'bi bi-pencil-square',
            'class' => 'btn btn-sm btn-warning',
            'title' => 'Modifier'
        ],
        'delete' => [
            'type' => 'delete',
            'icon' => 'bi bi-trash',
            'class' => 'btn btn-sm btn-danger',
            'title' => 'Supprimer',
            'confirm' => 'Êtes-vous sûr de vouloir supprimer cet élément ?'
        ]
    ]);

    // Configuration finale
    $config = $builder->configureSearch($config, true, ['code', 'name', 'email']);
    $config = $builder->configurePagination($config, true, 10);
    $config = $builder->configureSorting($config, true, 'id', 'desc');

    return $this->render('user/index.html.twig', [
        'datatableConfig' => $config,
    ]);
}
```

### ⚙️ Personnalisation via Configuration

Modifiez le comportement de la commande Maker :

```yaml
# config/packages/sigmasoft_data_table.yaml
sigmasoft_data_table:
    maker:
        # Modifier le mapping des types
        default_column_types:
            boolean: 'text'  # Utiliser text au lieu de badge
            decimal: 'badge' # Utiliser badge pour les décimales
        
        # Exclure d'autres propriétés
        excluded_properties: ['password', 'secret', 'internalId']
        
        # Désactiver la génération automatique d'actions
        auto_add_actions: false
        
        # Personnaliser les actions par défaut
        default_actions:
            show:
                icon: 'fas fa-eye'      # Utiliser Font Awesome
                class: 'btn btn-info'   # Classes Bootstrap différentes
```

### 🎯 Cas d'Usage Avancés

```bash
# Pour une entité avec contrôleur personnalisé
php bin/console make:datatable Product --controller=Admin\\ProductController

# Regénérer une configuration existante
php bin/console make:datatable User --force

# Générer pour une entité complexe
php bin/console make:datatable FinancialStatement
```

## 📋 Migration depuis l'ancien composant

Pour migrer depuis l'ancien `SigmasoftDataTableComponent` :

1. Remplacez l'ancien composant par le nouveau bundle
2. Convertissez la configuration des champs en colonnes typées
3. Utilisez le Builder au lieu de la configuration manuelle
4. Adaptez vos templates pour utiliser le nouveau composant

Exemple de migration :

```php
// Ancien code
{{ component('SigmasoftDataTableComponent', {
    entityClass: 'App\\Entity\\User',
    fields: {
        'id': 'ID',
        'email': 'Email',  
        'createdAt': 'Créé le'
    },
    // ... autres options
}) }}

// Nouveau code  
{{ component('sigmasoft_datatable', {
    configuration: datatableConfig
}) }}
```

## ✏️ Édition Inline et Export

### 🎯 Édition Inline Avancée

Le bundle propose un système complet d'édition inline permettant de modifier les données directement dans les cellules du tableau.

#### EditableColumn - Nouvelle colonne éditable

```php
use Sigmasoft\DataTableBundle\Column\EditableColumn;

// Champ texte éditable
$builder->addColumn(new EditableColumn('name', 'name', 'Nom', true, true, [
    'field_type' => 'text',
    'required' => true,
    'max_length' => 100,
    'placeholder' => 'Entrez le nom...'
]));

// Champ email avec validation
$builder->addColumn(new EditableColumn('email', 'email', 'Email', true, true, [
    'field_type' => 'email',
    'required' => true,
    'placeholder' => 'utilisateur@exemple.com',
    'validation' => [
        'pattern' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}'
    ]
]));

// Select avec options prédéfinies
$builder->addColumn(new EditableColumn('status', 'status', 'Statut', true, false, [
    'field_type' => 'select',
    'options' => [
        'active' => 'Actif',
        'inactive' => 'Inactif',
        'pending' => 'En attente',
        'suspended' => 'Suspendu'
    ]
]));
```

#### Options disponibles pour EditableColumn

| Option | Type | Description | Exemple |
|--------|------|-------------|---------|
| `field_type` | string | Type de champ (text, email, number, select) | `'text'` |
| `required` | bool | Champ obligatoire | `true` |
| `max_length` | int | Longueur maximale pour les champs texte | `100` |
| `placeholder` | string | Texte d'aide | `'Entrez le nom...'` |
| `options` | array | Options pour les selects | `['active' => 'Actif']` |
| `validation` | array | Règles de validation côté client | `['pattern' => '...']` |

#### Contrôleur pour l'édition inline

```php
<?php

namespace App\Controller;

use Sigmasoft\DataTableBundle\Service\InlineEditService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InlineEditController extends AbstractController
{
    #[Route('/update-field', name: 'update_field', methods: ['POST'])]
    public function updateField(Request $request, InlineEditService $editService): JsonResponse
    {
        $entityId = $request->request->getInt('entity_id');
        $fieldName = $request->request->get('field_name');
        $newValue = $request->request->get('new_value');
        
        $result = $editService->updateField(User::class, $entityId, $fieldName, $newValue);
        
        return new JsonResponse($result, 
            $result['success'] ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST
        );
    }
    
    #[Route('/bulk-update', name: 'bulk_update', methods: ['POST'])]
    public function bulkUpdate(Request $request, InlineEditService $editService): JsonResponse
    {
        $updates = $request->request->get('updates', []);
        $result = $editService->bulkUpdate(User::class, $updates);
        
        return new JsonResponse($result);
    }
}
```

#### JavaScript intégré

Le système d'édition inline inclut :

- ✅ **Sauvegarde automatique** - Délai de 1 seconde après modification
- ✅ **Indicateurs visuels** - États de sauvegarde, succès et erreur
- ✅ **Actions groupées** - Sauvegarde ou annulation en lot
- ✅ **Validation** - Côté client et serveur
- ✅ **Notifications** - Toast messages pour les actions

### 📊 Export de Données

#### Export CSV et Excel

```php
use Sigmasoft\DataTableBundle\Service\ExportService;

#[Route('/export/{format}', name: 'export', methods: ['GET'])]
public function export(string $format, DataTableBuilder $builder, ExportService $exportService): Response
{
    $config = $builder->createDataTable(User::class);
    // ... configuration des colonnes
    
    return match ($format) {
        'csv' => $exportService->exportToCsv($config, 'users_' . date('Y-m-d') . '.csv'),
        'excel' => $exportService->exportToExcel($config, 'users_' . date('Y-m-d') . '.xlsx'),
        default => throw $this->createNotFoundException('Format non supporté')
    };
}
```

#### Boutons d'export dans les templates

```twig
<!-- Boutons d'export -->
<div class="export-buttons mb-3">
    <h5><i class="fas fa-download"></i> Export</h5>
    <a href="{{ path('export_route', {format: 'csv'}) }}" 
       class="btn btn-outline-success me-2">
        <i class="fas fa-file-csv"></i> CSV
    </a>
    <a href="{{ path('export_route', {format: 'excel'}) }}" 
       class="btn btn-outline-primary">
        <i class="fas fa-file-excel"></i> Excel
    </a>
</div>
```

#### Configuration de l'export

L'ExportService supporte :

- ✅ **Filtrage** - Export des données filtrées/recherchées
- ✅ **Formatage** - Dates, nombres et textes formatés
- ✅ **Nettoyage** - Suppression des balises HTML
- ✅ **En-têtes** - Noms de colonnes personnalisés
- ✅ **Optimisation** - Gestion de gros volumes de données

### 🚀 Exemple Complet d'Implémentation

Consultez l'exemple complet dans `/example/inline-edit` qui démontre :

1. **DataTable avec colonnes éditables** - Nom, email, statut
2. **Sauvegarde automatique** - Avec indicateurs visuels
3. **Actions groupées** - Pour plusieurs modifications
4. **Export CSV et Excel** - Des données complètes
5. **Interface utilisateur complète** - Avec notifications et aide

```php
// Accédez à l'exemple via :
// GET /example/inline-edit/

// Routes API disponibles :
// POST /example/inline-edit/update-field
// POST /example/inline-edit/bulk-update  
// GET /example/inline-edit/export/{format}
```

### 🎨 Styles CSS Intégrés

Le système inclut des styles CSS optimisés :

```css
.editable-field {
    border: 1px solid transparent;
    background: transparent;
    transition: all 0.2s ease;
}

.editable-field:hover {
    border-color: #dee2e6;
    background: #f8f9fa;
}

.editable-field.saving {
    background: #fff3cd;
    border-color: #ffc107;
}

.editable-field.success {
    background: #d1e7dd;
    border-color: #198754;
}

.editable-field.error {
    background: #f8d7da;
    border-color: #dc3545;
}
```

### 🔒 Sécurité et Validation

#### Validation côté serveur

L'InlineEditService inclut :

- ✅ **Validation Symfony** - Utilisation du composant Validator
- ✅ **Vérification d'existence** - Entité et champ doivent exister
- ✅ **Logging des erreurs** - Tentatives suspectes loggées
- ✅ **Gestion d'exceptions** - Erreurs capturées et formatées

#### Sécurisation des routes

```php
// Ajoutez des vérifications de sécurité
#[Route('/update-field', name: 'update_field', methods: ['POST'])]
#[IsGranted('ROLE_EDITOR')] // Exemple avec Symfony Security
public function updateField(Request $request, InlineEditService $editService): JsonResponse
{
    // Vérifications additionnelles...
    if (!$this->isGranted('EDIT', $entity)) {
        return new JsonResponse(['success' => false, 'error' => 'Accès refusé'], 403);
    }
    
    // Logique de mise à jour...
}
```

## 🎨 Renderers Personnalisés et Extensibilité

### 🔧 Architecture Modulaire des Renderers

Le système d'édition inline utilise le **Strategy Pattern** pour permettre l'ajout facile de nouveaux types de champs. Chaque type de champ a son propre renderer qui gère le rendu HTML et la validation.

#### Interface FieldRendererInterface

```php
interface FieldRendererInterface
{
    public function supports(EditableFieldConfiguration $config): bool;
    public function render(EditableFieldConfiguration $config, mixed $value, object $entity, string $fieldName): string;
    public function getPriority(): int;
}
```

### 🎯 Exemple : ColorFieldRenderer

Voici un exemple complet de renderer personnalisé pour un sélecteur de couleur :

```php
<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

class ColorFieldRenderer extends AbstractFieldRenderer
{
    public const FIELD_TYPE_COLOR = 'color';
    
    private const PRESET_COLORS = [
        '#FF0000' => 'Rouge',
        '#00FF00' => 'Vert', 
        '#0000FF' => 'Bleu',
        '#FFFF00' => 'Jaune',
        // ... autres couleurs
    ];

    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->getFieldType() === self::FIELD_TYPE_COLOR;
    }

    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        $colorValue = $this->normalizeColorValue($value) ?: '#000000';
        $attributes = $this->generateBaseAttributes($config, $value, $entity, $fieldName);
        $attributes['type'] = 'color';
        $attributes['value'] = $colorValue;
        
        // HTML avec color picker + preview + presets
        $html = '<div class="color-field-wrapper d-flex align-items-center gap-2">';
        $html .= sprintf('<input %s class="color-picker-field">', $this->buildAttributesString($attributes));
        $html .= $this->renderColorPreview($colorValue);
        $html .= $this->renderColorPresets($attributes['data-entity-id'], $attributes['data-field-name']);
        $html .= '</div>';
        
        return $this->wrapWithIndicators($html);
    }
    
    private function renderColorPreview(string $color): string
    {
        return sprintf(
            '<div class="color-preview" style="width: 30px; height: 30px; background-color: %s; border: 2px solid #dee2e6; border-radius: 4px;"></div>',
            $this->escapeValue($color)
        );
    }
    
    private function renderColorPresets(string $entityId, string $fieldName): string
    {
        $html = '<div class="color-presets dropdown">';
        $html .= '<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">';
        $html .= '<i class="fas fa-palette"></i></button>';
        
        $html .= '<div class="dropdown-menu p-2"><div class="d-grid gap-1" style="grid-template-columns: repeat(4, 1fr);">';
        foreach (self::PRESET_COLORS as $color => $name) {
            $html .= sprintf(
                '<button type="button" class="preset-color-btn" style="background-color: %s; width: 40px; height: 30px; border: 2px solid #dee2e6;" data-color="%s" data-entity-id="%s" data-field-name="%s" title="%s"></button>',
                $color, $color, $entityId, $fieldName, $name
            );
        }
        $html .= '</div></div></div>';
        
        return $html;
    }
}
```

### 🔧 Étapes d'Intégration

#### 1. Ajouter le Type à la Configuration

```php
// Dans EditableFieldConfiguration.php
public const FIELD_TYPE_COLOR = 'color';

private array $validFieldTypes = [
    // ... autres types
    self::FIELD_TYPE_COLOR,
];
```

#### 2. Enregistrer le Service

```php
// Dans SigmasoftDataTableExtension.php
$container->register(\Sigmasoft\DataTableBundle\InlineEdit\Renderer\ColorFieldRenderer::class)
    ->setArguments([new Reference('property_accessor')])
    ->addTag('sigmasoft_datatable.field_renderer');
```

#### 3. Ajouter la Méthode Factory

```php
// Dans EditableColumnFactory.php
public function color(
    string $name,
    string $propertyPath,
    string $label = '',
    bool $showPresets = true
): EditableColumnV2 {
    $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR)
        ->dataAttributes(['show_presets' => $showPresets]);
    return new EditableColumnV2($name, $propertyPath, $label, $config, true, true, [], $this->rendererRegistry);
}
```

#### 4. Utiliser dans un Contrôleur

```php
// Exemple d'utilisation
->addColumn(
    $editableColumnFactory->color('preferredColor', 'preferredColor', 'Couleur préférée')
)
```

### 🎯 JavaScript Intégré

Le système gère automatiquement les interactions JavaScript :

```javascript
// Gestion des couleurs prédéfinies
handleColorPresetClick(event) {
    if (!event.target.classList.contains('preset-color-btn')) return;
    
    const color = event.target.dataset.color;
    const field = document.querySelector(`[data-entity-id="${event.target.dataset.entityId}"][data-field-name="${event.target.dataset.fieldName}"]`);
    
    if (field) {
        field.value = color;
        this.updateColorPreview(field);
        this.scheduleFieldSave(field, 100);
    }
}

// Mise à jour en temps réel de la preview
updateColorPreview(field) {
    const preview = field.closest('.color-field-wrapper')?.querySelector('.color-preview');
    if (preview && /^#[0-9A-Fa-f]{6}$/.test(field.value)) {
        preview.style.backgroundColor = field.value;
    }
}
```

### 🎨 Styles CSS

```css
.color-field-wrapper {
    align-items: center;
}

.color-picker-field {
    width: 60px !important;
    height: 35px;
    padding: 0;
    border-radius: 4px;
    cursor: pointer;
}

.color-preview {
    transition: all 0.2s ease;
}

.color-preview:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.preset-color-btn {
    cursor: pointer;
    transition: transform 0.15s ease;
}

.preset-color-btn:hover {
    transform: scale(1.1);
}
```

### 📋 Autres Exemples de Renderers

#### DatePickerRenderer avec Flatpickr

```php
class DatePickerRenderer extends AbstractFieldRenderer
{
    public function render(...): string
    {
        $html = sprintf('<input %s class="flatpickr-input">', $this->buildAttributesString($attributes));
        $html .= '<script>flatpickr(".flatpickr-input", { dateFormat: "d/m/Y" });</script>';
        return $this->wrapWithIndicators($html);
    }
}
```

#### RichTextRenderer avec TinyMCE

```php
class RichTextRenderer extends AbstractFieldRenderer
{
    public function render(...): string
    {
        $html = sprintf('<div class="tinymce-wrapper"><textarea %s></textarea></div>', $attributes);
        $html .= '<script>tinymce.init({ selector: ".tinymce-wrapper textarea" });</script>';
        return $this->wrapWithIndicators($html);
    }
}
```

### 📚 Documentation Complète

Pour un guide détaillé sur la création de renderers personnalisés, consultez :

**📄 [Guide Complet des Renderers Personnalisés](Documentation/CUSTOM_RENDERERS.md)**

Ce guide contient :
- Architecture détaillée du système
- Exemples complets avec code source
- Bonnes pratiques de développement
- Tests unitaires et intégration
- Gestion de la sécurité et validation

### 🚀 Exemple en Action

L'exemple d'édition inline (`/example/inline-edit`) démontre le ColorFieldRenderer en action avec :

- ✅ **Color picker natif HTML5**
- ✅ **Preview de couleur en temps réel**
- ✅ **Palette de couleurs prédéfinies**
- ✅ **Sauvegarde automatique**
- ✅ **Validation du format hexadécimal**

## 🔧 Développement

Pour contribuer au bundle :

1. Clonez le repository
2. Installez les dépendances : `composer install`
3. Lancez les tests : `php bin/phpunit`
4. Respectez les standards PSR-12
5. Ajoutez des tests pour vos nouvelles fonctionnalités

## 👤 Auteur

**Gédeon Makela**  
📧 g.makela@sigmasoft-solution.com  
🏢 Sigmasoft Solution

## 📄 Licence

Ce bundle est distribué sous licence propriétaire.
