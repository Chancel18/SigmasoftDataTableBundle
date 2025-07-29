# SigmasoftDataTableBundle

[![Latest Stable Version](https://poser.pugx.org/sigmasoft/datatable-bundle/v/stable)](https://packagist.org/packages/sigmasoft/datatable-bundle)
[![License](https://poser.pugx.org/sigmasoft/datatable-bundle/license)](https://packagist.org/packages/sigmasoft/datatable-bundle)
[![PHP Version Require](https://poser.pugx.org/sigmasoft/datatable-bundle/require/php)](https://packagist.org/packages/sigmasoft/datatable-bundle)

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

## 📦 Installation

```bash
composer require sigmasoft/datatable-bundle
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

Créez le fichier `config/packages/sigmasoft_data_table.yaml` :

```yaml
sigmasoft_data_table:
    defaults:
        items_per_page: 10
        enable_search: true
        table_class: 'table table-striped table-hover'
        date_format: 'd/m/Y'
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
        $config = $this->dataTableBuilder
            ->createDataTable(User::class)
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
                    'Y' => 'Actif',
                    'N' => 'Inactif'
                ])
            )
            ->addDateColumn('createdAt', 'createdAt', 'Créé le')
            ->addActionColumn([
                'show' => ['route' => 'user_show', 'icon' => 'bi bi-eye'],
                'edit' => ['route' => 'user_edit', 'icon' => 'bi bi-pencil'],
                'delete' => ['type' => 'delete', 'icon' => 'bi bi-trash']
            ])
            ->configureSearch(true, ['name', 'email'])
            ->configurePagination(true, 10);

        return $this->render('user/index.html.twig', [
            'datatableConfig' => $config,
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

Pour une documentation complète avec exemples avancés, consultez :

- [Guide d'installation](docs/installation.md)
- [Utilisation de base](docs/basic-usage.md)
- [Édition inline](docs/inline-editing.md)
- [Renderers personnalisés](docs/custom-renderers.md)
- [Export de données](docs/export.md)
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
