# SigmasoftDataTableBundle (BETA)

[![Latest Stable Version](https://poser.pugx.org/sigmasoft/datatable-bundle/v/stable)](https://packagist.org/packages/sigmasoft/datatable-bundle)
[![License](https://poser.pugx.org/sigmasoft/datatable-bundle/license)](https://packagist.org/packages/sigmasoft/datatable-bundle)
[![PHP Version Require](https://poser.pugx.org/sigmasoft/datatable-bundle/require/php)](https://packagist.org/packages/sigmasoft/datatable-bundle)

> ⚠️ **VERSION BETA 3.0.0** - Cette version est en phase de test. Ne pas utiliser en production.
> 
> 📧 Merci de reporter tout problème à : support@sigmasoft-solution.com

**SigmasoftDataTableBundle** est un bundle Symfony moderne et puissant conçu pour créer facilement des tables de données interactives avec des fonctionnalités avancées de tri, recherche, pagination, édition inline et export.

## 🎯 Fonctionnalités Principales

### ✨ Interface Interactive
- **Tri dynamique** des colonnes avec indicateurs visuels
- **Recherche en temps réel** avec filtrage intelligent
- **Pagination** avec navigation intuitive
- **Actions personnalisées** (voir, éditer, supprimer)

### 🚀 Édition Inline V2 (Nouvelle Architecture)
- **Architecture modulaire** avec séparation des responsabilités
- **Renderers personnalisés** extensibles (Text, Email, Color, Select, Textarea)
- **Validation robuste** côté serveur et client
- **Transactions sécurisées** avec rollback automatique
- **JavaScript ES6** avec debouncing et retry automatique

### 🏗️ Architecture Moderne
- **Symfony UX LiveComponent** pour des interactions fluides
- **Patterns éprouvés** (Builder, Factory, Registry, Strategy)
- **15+ classes spécialisées** pour une extensibilité maximale
- **Configuration YAML** flexible et puissante

### 🛡️ Sécurité Intégrée
- **Validation SQL** automatique contre les injections
- **Échappement HTML** des données utilisateur
- **Contrôle des permissions** par rôle et propriétaire
- **Logging PSR-3** des erreurs et tentatives suspectes

## 🆕 Nouveautés v3.0.0-beta

### Configuration YAML Fonctionnelle
- ✅ Prise en compte correcte de la configuration bundle
- ✅ Application automatique des valeurs par défaut

### Système d'Événements Complet
- ✅ DataTableEvents : PRE_LOAD, POST_LOAD, PRE_QUERY
- ✅ InlineEditEvents : PRE_EDIT, POST_EDIT, EDIT_ERROR

### Templates Refactorisés
- ✅ Architecture modulaire avec blocks Twig
- ✅ Support de thèmes (Bootstrap 5, Minimal, Custom)
- ✅ Composants réutilisables

### Support des Colonnes Numériques
- ✅ NumberColumn avec formatage localisé
- ✅ 4 formats : integer, decimal, currency, percentage
- ✅ Édition inline avec validation

## 📦 Installation

```bash
composer require sigmasoft/datatable-bundle:v3.0.0-beta.1
```

### Configuration des bundles

Ajoutez le bundle dans `config/bundles.php` :

```php
<?php

return [
    // ...
    Sigmasoft\DataTableBundle\SigmasoftDataTableBundle::class => ['all' => true],
];
```

### Configuration YAML

#### Installation automatique (recommandée)

Après installation du bundle, utilisez la commande d'installation :

```bash
# Vider le cache Symfony d'abord
php bin/console cache:clear

# Installer la configuration
php bin/console sigmasoft:datatable:install-config
```

Si la commande n'est pas trouvée, utilisez notre **script de diagnostic** :

```bash
# Script de diagnostic automatique
php vendor/sigmasoft/datatable-bundle/bin/check-installation.php
```

**Ou vérifiez manuellement :**

1. **Bundle enregistré** dans `config/bundles.php` :
```php
<?php
return [
    // ...
    Sigmasoft\DataTableBundle\SigmasoftDataTableBundle::class => ['all' => true],
];
```

2. **Vider le cache** après installation :
```bash
php bin/console cache:clear
composer dump-autoload
```

3. **Lister les commandes** pour vérifier :
```bash
php bin/console list sigmasoft
```

#### Installation manuelle (alternative)

Si la commande ne fonctionne pas, créez manuellement le fichier `config/packages/sigmasoft_data_table.yaml` :

```yaml
sigmasoft_data_table:
    defaults:
        items_per_page: 10
        enable_search: true
        enable_pagination: true
        enable_sorting: true
        table_class: 'table table-striped table-hover'
        date_format: 'd/m/Y'
        pagination_sizes: [5, 10, 25, 50, 100]
    templates:
        datatable: '@SigmasoftDataTable/datatable.html.twig'
    caching:
        enabled: false
        ttl: 3600
    maker:
        default_column_types:
            string: 'text'
            integer: 'number'
            decimal: 'number'
            boolean: 'badge'
            datetime: 'date'
        excluded_properties: ['password', 'plainPassword', 'salt', 'token']
        auto_add_actions: true
```

## 🚀 Utilisation Rapide

### 1. Génération automatique avec Maker

```bash
php bin/console make:datatable User
```

### 2. Configuration manuelle

```php
<?php
// src/Controller/UserController.php

use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Service\EditableColumnFactory;

class UserController extends AbstractController
{
    public function __construct(
        private DataTableBuilder $dataTableBuilder,
        private EditableColumnFactory $editableColumnFactory
    ) {}

    #[Route('/users', name: 'app_user_index')]
    public function index(): Response
    {
        $datatableConfig = $this->dataTableBuilder
            ->createDataTable(User::class)
            
            // Colonnes éditables avec validation
            ->addColumn(
                $this->editableColumnFactory->text('name', 'name', 'Nom')
                    ->required(true)
                    ->maxLength(100)
            )
            ->addColumn(
                $this->editableColumnFactory->email('email', 'email', 'Email')
                    ->required(true)
            )
            ->addColumn(
                $this->editableColumnFactory->select('status', 'status', 'Statut', [
                    'active' => 'Actif',
                    'inactive' => 'Inactif'
                ])
            )
            ->addColumn(
                $this->editableColumnFactory->number('age', 'age', 'Âge')
                    ->min(18)
                    ->max(100)
            )
            
            // Colonnes de lecture seule
            ->addDateColumn('createdAt', 'createdAt', 'Créé le')
            ->addBadgeColumn('role', 'roles', 'Rôle', [
                'ROLE_USER' => 'Utilisateur',
                'ROLE_ADMIN' => 'Admin'
            ])
            
            // Actions personnalisées
            ->addActionColumn('actions', 'Actions', [
                'show' => ['route' => 'user_show', 'icon' => 'bi bi-eye'],
                'edit' => ['route' => 'user_edit', 'icon' => 'bi bi-pencil'],
                'delete' => ['route' => 'user_delete', 'icon' => 'bi bi-trash', 'confirm' => true]
            ])
            ->configureSearch(true, ['name', 'email'])
            ->configurePagination(true, 10)
            ->getConfiguration(); // Récupérer la configuration finale

        return $this->render('user/index.html.twig', [
            'datatableConfig' => $datatableConfig,
        ]);
    }
}
```

### 3. Template Twig

```twig
{# templates/user/index.html.twig #}

{% extends 'base.html.twig' %}

{% block body %}
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-people-fill me-2"></i>
                Liste des Utilisateurs
            </h3>
        </div>
        <div class="card-body" 
             data-update-field-url="{{ path('user_update_field') }}"
             data-bulk-update-url="{{ path('user_bulk_update') }}">
            {{ component('sigmasoft_datatable', {
                configuration: datatableConfig
            }) }}
        </div>
    </div>
</div>
{% endblock %}
```

## 📊 Types de Colonnes Disponibles

### Colonnes Éditables (avec EditableColumnFactory)

| Type | Description | Exemple |
|------|-------------|---------|
| **text** | Champ texte avec validation | `->text('name', 'name', 'Nom')->required()->maxLength(100)` |
| **email** | Email avec validation | `->email('email', 'email', 'Email')->required()` |
| **number** | Nombre avec contraintes | `->number('price', 'price', 'Prix')->min(0)->step(0.01)` |
| **select** | Liste déroulante | `->select('status', 'status', 'Statut', ['Y' => 'Actif'])` |
| **textarea** | Zone de texte | `->textarea('notes', 'notes', 'Notes')->rows(3)` |
| **color** | Sélecteur de couleur | `->color('color', 'color', 'Couleur')->showPresets()` |

### Colonnes de Lecture Seule

| Type | Description | Exemple |
|------|-------------|---------|
| **TextColumn** | Texte simple avec formatage | `new TextColumn('name', 'name', 'Nom')` |
| **DateColumn** | Date avec format personnalisé | `new DateColumn('createdAt', 'createdAt', 'Créé le')` |
| **BadgeColumn** | Badges colorés | `new BadgeColumn('status', 'status', 'Statut')` |
| **ActionColumn** | Boutons d'actions | `new ActionColumn($urlGenerator, 'actions', 'Actions')` |

### Exemple Complet E-commerce

```php
$config = $this->dataTableBuilder
    ->createDataTable(Product::class)
    
    // Informations produit éditables
    ->addColumn(
        $this->editableColumnFactory->text('name', 'name', 'Nom')
            ->required(true)
            ->minLength(3)
            ->maxLength(100)
    )
    ->addColumn(
        $this->editableColumnFactory->number('price', 'price', 'Prix')
            ->required(true)
            ->min(0.01)
            ->step(0.01)
            ->suffix(' €')
    )
    ->addColumn(
        $this->editableColumnFactory->select('status', 'status', 'Statut', [
            'active' => 'Actif',
            'inactive' => 'Inactif',
            'out_of_stock' => 'Rupture'
        ])
    )
    ->addColumn(
        $this->editableColumnFactory->color('color', 'color', 'Couleur')
            ->showPresets(true)
    )
    
    // Colonnes informatives
    ->addColumn(new BadgeColumn('category', 'category.name', 'Catégorie'))
    ->addDateColumn('createdAt', 'createdAt', 'Créé le')
    
    // Actions
    ->addActionColumn('actions', 'Actions', [
        'edit' => ['route' => 'product_edit', 'icon' => 'bi bi-pencil'],
        'delete' => ['route' => 'product_delete', 'icon' => 'bi bi-trash', 'confirm' => true]
    ]);
```

## 🎨 Renderers Personnalisés

### Créer un Renderer Personnalisé

```php
<?php
// src/Renderer/CustomFieldRenderer.php

namespace App\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Renderer\AbstractFieldRenderer;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

class CustomFieldRenderer extends AbstractFieldRenderer
{
    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->getFieldType() === 'custom';
    }

    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        return sprintf(
            '<input type="text" class="editable-field custom-field" data-field="%s" value="%s" />',
            htmlspecialchars($fieldName),
            htmlspecialchars((string) $value)
        );
    }

    public function getPriority(): int
    {
        return 100;
    }
}
```

Le renderer sera automatiquement enregistré grâce au **Compiler Pass**.

## 📊 Export de Données

```php
// Dans votre contrôleur
$config = $this->dataTableBuilder
    ->createDataTable(User::class)
    // ... configuration des colonnes
    ->enableExport(['csv', 'excel']);
```

## 🧪 Tests

```bash
# Tests unitaires
./vendor/bin/phpunit

# Tests spécifiques au bundle
./vendor/bin/phpunit tests/

# Avec couverture
./vendor/bin/phpunit --coverage-html coverage/
```

## 📚 Documentation Complète

### 🌐 Documentation en Ligne
**Consultez la documentation complète sur :** [https://chancel18.github.io/SigmasoftDataTableBundle/](https://chancel18.github.io/SigmasoftDataTableBundle/)

### 📖 Guides Principaux

| Guide | Description | Niveau |
|-------|-------------|--------|
| [Types de Colonnes](docs/docs/user-guide/column-types.md) | **Guide complet** de tous les types disponibles | 🟢 Débutant |
| [Édition Inline](docs/docs/user-guide/inline-editing.md) | Fonctionnalités d'édition en temps réel | 🟡 Intermédiaire |
| [Configuration YAML](docs/docs/user-guide/configuration.md) | Toutes les options de configuration | 🟡 Intermédiaire |
| [Exemples Avancés](docs/docs/examples/advanced-examples.md) | **Cas d'usage** E-commerce, CRM, Dashboard | 🔴 Avancé |
| [Renderers Personnalisés](docs/docs/developer-guide/custom-renderers.md) | Créer ses propres types de champs | 🔴 Avancé |

### 🚀 Démarrage Rapide
1. **Installation** : `composer require sigmasoft/datatable-bundle`
2. **Génération** : `php bin/console make:datatable MyEntity`
3. **Personnalisation** : Consultez le [Guide des Types de Colonnes](docs/docs/user-guide/column-types.md)
- [Configuration avancée](docs/configuration.md)

## 🛠️ Développement

### Prérequis

- PHP 8.1+
- Symfony 6.4+ ou 7.0+
- Composer
- Node.js (pour les assets)

### Installation pour développement

```bash
git clone https://github.com/sigmasoft-solution/datatable-bundle.git
cd datatable-bundle
composer install
```

### Contribution

Les contributions sont les bienvenues ! Merci de :

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push sur la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📋 Changelog

### Version 2.0.0 (2025-07-29)
- ✨ **Nouvelle architecture** modulaire pour l'édition inline
- 🎨 **Renderers personnalisés** extensibles avec Strategy Pattern
- 🔒 **Sécurité renforcée** avec validation et transactions
- 📱 **Export CSV/Excel** intégré
- 🚀 **JavaScript ES6** moderne avec retry et debouncing

[Voir le changelog complet](CHANGELOG.md)

## 📄 License

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Auteur

**Gédéon Makela** - [Sigmasoft Solution](https://sigmasoft-solution.com)
- Email: g.makela@sigmasoft-solution.com
- GitHub: [@gedeonmakela](https://github.com/gedeonmakela)

## 🙏 Remerciements

- L'équipe Symfony pour le framework exceptionnel
- Les contributeurs de Symfony UX pour les composants modernes
- La communauté open source PHP

---

⭐ **N'hésitez pas à mettre une étoile si ce bundle vous aide !**
# Force GitHub Pages deployment
