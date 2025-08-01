# Guide de Migration vers SigmasoftDataTableBundle 3.0

> ⚠️ **VERSION BETA** - Testez dans un environnement de développement avant la migration en production.

## 🚨 Breaking Changes

### 1. Changement de Namespace

Le namespace principal a changé pour une meilleure organisation :

**Avant :**
```php
use App\SigmasoftDataTableBundle\...;
```

**Après :**
```php
use Sigmasoft\DataTableBundle\...;
```

### 2. Services Renommés

Les services d'édition inline ont été refactorisés :

**Avant :**
```php
use App\SigmasoftDataTableBundle\Column\EditableColumn;
use App\SigmasoftDataTableBundle\Service\InlineEditService;
```

**Après :**
```php
use Sigmasoft\DataTableBundle\Column\EditableColumnV2;
use Sigmasoft\DataTableBundle\Service\InlineEditServiceV2;
```

### 3. Configuration YAML

La configuration est maintenant automatiquement copiée lors de l'installation.

Si vous migrez depuis la v2.x, exécutez :
```bash
php bin/console sigmasoft:datatable:install-config
```

## 🎯 Nouvelles Fonctionnalités

### 1. Système d'Événements

Ajoutez des Event Listeners pour personnaliser le comportement :

```php
// src/EventListener/DataTableEventListener.php
namespace App\EventListener;

use Sigmasoft\DataTableBundle\Event\DataTableQueryEvent;
use Sigmasoft\DataTableBundle\Event\DataTableEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DataTableEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            DataTableEvents::PRE_QUERY => 'onPreQuery',
        ];
    }

    public function onPreQuery(DataTableQueryEvent $event): void
    {
        $queryBuilder = $event->getQueryBuilder();
        // Ajoutez vos filtres personnalisés
        $queryBuilder->andWhere('e.isActive = :active')
                    ->setParameter('active', true);
    }
}
```

### 2. Templates Personnalisables

Créez votre propre thème en étendant le template de base :

```twig
{# templates/datatable/my_theme.html.twig #}
{% extends '@SigmasoftDataTable/datatable.html.twig' %}

{% set theme = 'custom' %}

{% block datatable_table_class %}
    table table-dark table-striped
{% endblock %}

{% block datatable_search_input %}
    <input type="search" 
           class="form-control form-control-lg"
           placeholder="Rechercher dans la table..."
           {{ stimulus_action('live#action', 'input', 'search') }}
           value="{{ this.searchQuery }}">
{% endblock %}
```

### 3. Colonnes Numériques

Utilisez les nouvelles colonnes numériques avec formatage :

```php
use Sigmasoft\DataTableBundle\Column\NumberColumn;

// Dans votre configuration
$config->addColumn(NumberColumn::currency('price', 'price', 'Prix', 'EUR'));
$config->addColumn(NumberColumn::percentage('rate', 'rate', 'Taux'));
$config->addColumn(NumberColumn::integer('quantity', 'quantity', 'Quantité'));
```

## 🔧 Migration Pas à Pas

### Étape 1 : Mise à jour Composer

```bash
composer require sigmasoft/datatable-bundle:v3.0.0-beta.1
```

### Étape 2 : Mise à jour des Imports

Utilisez la recherche/remplacement dans votre IDE :
- Rechercher : `App\SigmasoftDataTableBundle`
- Remplacer : `Sigmasoft\DataTableBundle`

### Étape 3 : Configuration

1. Supprimez votre ancienne configuration
2. Exécutez : `php bin/console sigmasoft:datatable:install-config`
3. Personnalisez le fichier `config/packages/sigmasoft_data_table.yaml`

### Étape 4 : Services

Si vous avez étendu les services du bundle, mettez à jour les références :

```yaml
# config/services.yaml
services:
    App\DataTable\:
        resource: '../src/DataTable/'
        arguments:
            $dataTableBuilder: '@Sigmasoft\DataTableBundle\Builder\DataTableBuilder'
            $editableColumnFactory: '@Sigmasoft\DataTableBundle\Service\EditableColumnFactory'
```

### Étape 5 : Templates

Si vous avez personnalisé les templates :

1. Vérifiez que vos overrides utilisent les nouveaux blocks
2. Testez le rendu avec le nouveau système de thèmes
3. Migrez progressivement vers la nouvelle architecture

## 📝 Checklist de Test

Après la migration, vérifiez :

- [ ] Les DataTables s'affichent correctement
- [ ] La recherche fonctionne
- [ ] Le tri fonctionne
- [ ] La pagination fonctionne
- [ ] L'édition inline fonctionne (si utilisée)
- [ ] Les exports fonctionnent (si utilisés)
- [ ] Les événements personnalisés sont déclenchés
- [ ] Les templates personnalisés s'affichent correctement
- [ ] Les colonnes numériques sont bien formatées

## 🆘 Support

En cas de problème :
- 📧 Email : support@sigmasoft-solution.com
- 🐛 Issues : https://github.com/Chancel18/SigmasoftDataTableBundle/issues
- 📖 Documentation : https://chancel18.github.io/SigmasoftDataTableBundle/

## 🎉 Remerciements

Merci de tester cette version beta et de nous faire part de vos retours !