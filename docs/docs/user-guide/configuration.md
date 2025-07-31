---
sidebar_position: 4
title: Configuration YAML
description: Guide de configuration complète du bundle via YAML
---

# Configuration YAML

Le SigmasoftDataTableBundle peut être entièrement configuré via YAML pour définir des comportements par défaut et personnaliser l'apparence de vos DataTables.

## Configuration Complète

Créez ou éditez le fichier `config/packages/sigmasoft_data_table.yaml` :

```yaml
sigmasoft_data_table:
    # Configuration par défaut pour toutes les DataTables
    defaults:
        items_per_page: 10          # Nombre d'éléments par page (min: 1, max: 500)
        enable_search: true         # Activer la recherche
        enable_pagination: true     # Activer la pagination
        enable_sorting: true        # Activer le tri des colonnes
        table_class: 'table table-striped table-hover align-middle'  # Classes CSS
        date_format: 'd/m/Y'        # Format d'affichage des dates
        pagination_sizes: [5, 10, 25, 50, 100]  # Options du sélecteur de pagination
    
    # Configuration des templates
    templates:
        datatable: '@SigmasoftDataTable/datatable.html.twig'  # Template principal
    
    # Configuration du cache (optionnel)
    caching:
        enabled: false              # Activer le cache des configurations
        ttl: 3600                  # Durée de vie du cache en secondes
    
    # Configuration du Maker (génération de code)
    maker:
        # Mapping des types Doctrine vers les types de colonnes
        default_column_types:
            string: 'text'
            text: 'text'
            integer: 'text'
            float: 'text'
            decimal: 'text'
            boolean: 'badge'        # Affichage en badge coloré
            datetime: 'date'
            datetime_immutable: 'date'
            date: 'date'
            date_immutable: 'date'
            time: 'date'
            time_immutable: 'date'
        
        # Propriétés à exclure lors de la génération
        excluded_properties: 
            - 'password'
            - 'plainPassword'
            - 'salt'
            - 'token'
            - 'resetToken'
        
        # Génération automatique des boutons d'action
        auto_add_actions: true
        
        # Configuration des actions par défaut
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

## Configuration par Environnement

Vous pouvez avoir des configurations différentes selon l'environnement :

### Développement
`config/packages/dev/sigmasoft_data_table.yaml`
```yaml
sigmasoft_data_table:
    defaults:
        items_per_page: 5    # Moins d'éléments pour tester la pagination
    caching:
        enabled: false       # Pas de cache en dev
```

### Production
`config/packages/prod/sigmasoft_data_table.yaml`
```yaml
sigmasoft_data_table:
    defaults:
        items_per_page: 25
    caching:
        enabled: true
        ttl: 86400          # Cache de 24h
```

## Options de Configuration Détaillées

### Defaults

| Option | Type | Défaut | Description |
|--------|------|--------|-------------|
| `items_per_page` | integer | 10 | Nombre d'éléments affichés par page |
| `enable_search` | boolean | true | Active/désactive la barre de recherche |
| `enable_pagination` | boolean | true | Active/désactive la pagination |
| `enable_sorting` | boolean | true | Active/désactive le tri des colonnes |
| `table_class` | string | 'table table-striped...' | Classes CSS de la table |
| `date_format` | string | 'd/m/Y' | Format PHP pour l'affichage des dates |
| `pagination_sizes` | array | [5,10,25,50,100] | Options du sélecteur de taille |

### Templates

Permet de personnaliser les templates utilisés :

```yaml
templates:
    datatable: 'data_table/custom_table.html.twig'
```

### Caching

Configuration du cache pour améliorer les performances :

```yaml
caching:
    enabled: true           # Active le cache
    ttl: 3600              # Durée de vie en secondes (1 heure)
```

### Maker

Configure la génération automatique de code avec `make:datatable` :

#### default_column_types
Définit quel type de colonne utiliser pour chaque type Doctrine :

```yaml
default_column_types:
    string: 'editable'      # Utilise EditableColumn pour les strings
    boolean: 'badge'        # Utilise BadgeColumn pour les booléens
    datetime: 'date'        # Utilise DateColumn pour les dates
```

#### excluded_properties
Liste des propriétés à ignorer lors de la génération :

```yaml
excluded_properties:
    - 'password'
    - 'apiToken'
    - '__initializer__'    # Propriétés Doctrine
    - '__cloner__'
```

#### default_actions
Configure les actions générées automatiquement :

```yaml
default_actions:
    show:
        icon: 'fas fa-eye'          # Font Awesome
        class: 'btn btn-sm btn-primary'
        title: 'Consulter'
    custom_action:
        route: 'app_entity_custom'
        icon: 'bi bi-gear'
        class: 'btn btn-sm btn-secondary'
        title: 'Action personnalisée'
```

## Surcharge dans le Code

La configuration YAML peut être surchargée dans le code :

```php
$dataTable = $builder
    ->createDataTable(Product::class)
    ->setItemsPerPage(20)  // Surcharge items_per_page
    ->enableSearch(false)  // Désactive la recherche
    ->setTableClass('table table-dark');  // Change les classes CSS
```

## Variables d'Environnement

Certaines options peuvent utiliser des variables d'environnement :

```yaml
sigmasoft_data_table:
    caching:
        enabled: '%env(bool:DATATABLE_CACHE_ENABLED)%'
        ttl: '%env(int:DATATABLE_CACHE_TTL)%'
```

Dans `.env` :
```env
DATATABLE_CACHE_ENABLED=true
DATATABLE_CACHE_TTL=7200
```

## Configuration Minimale

Si vous êtes satisfait des valeurs par défaut, aucune configuration n'est nécessaire. Le bundle fonctionnera avec ses paramètres par défaut.

## Validation de la Configuration

Pour vérifier votre configuration :

```bash
php bin/console config:dump-reference sigmasoft_data_table
```

Pour voir la configuration active :

```bash
php bin/console debug:config sigmasoft_data_table
```

## Exemples de Configurations

### Configuration Minimaliste
```yaml
sigmasoft_data_table:
    defaults:
        items_per_page: 25
```

### Configuration E-commerce
```yaml
sigmasoft_data_table:
    defaults:
        items_per_page: 50
        table_class: 'table table-sm table-hover'
        pagination_sizes: [25, 50, 100, 200]
    maker:
        default_column_types:
            decimal: 'badge'    # Prix en badge
            boolean: 'text'     # Stock oui/non
        excluded_properties:
            - 'deletedAt'
            - 'updatedBy'
```

### Configuration Admin
```yaml
sigmasoft_data_table:
    defaults:
        enable_search: true
        enable_sorting: true
        table_class: 'table table-bordered table-responsive'
    caching:
        enabled: true
        ttl: 600    # 10 minutes
    maker:
        auto_add_actions: true
        default_actions:
            show:
                icon: 'fas fa-eye'
                class: 'btn btn-sm btn-outline-info'
            edit:
                icon: 'fas fa-edit'
                class: 'btn btn-sm btn-outline-warning'
            delete:
                icon: 'fas fa-trash-alt'
                class: 'btn btn-sm btn-outline-danger'
                confirm: 'Cette action est irréversible. Continuer ?'
```

## Prochaines Étapes

- [Personnalisation avancée](./customization.md)
- [Édition inline](./inline-editing.md)
- [API Reference](../api/overview.md)