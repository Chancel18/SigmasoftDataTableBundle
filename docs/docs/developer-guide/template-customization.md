---
sidebar_position: 3
title: Personnalisation des Templates
description: Guide complet pour personnaliser l'apparence des DataTables
---

# Personnalisation des Templates

Le SigmasoftDataTableBundle utilise une architecture de templates modulaire basée sur Twig qui permet une personnalisation complète et facile de l'apparence de vos DataTables.

## Architecture des Templates

### Template Principal

Le template principal `@SigmasoftDataTable/datatable.html.twig` est structuré avec des blocks Twig qui peuvent être surchargés individuellement :

```twig
{% extends '@SigmasoftDataTable/datatable.html.twig' %}

{% block datatable_search %}
    {# Votre propre implémentation de la recherche #}
{% endblock %}
```

### Structure des Blocks

#### Blocks Principaux

- `datatable_wrapper` : Conteneur principal de la DataTable
- `datatable_toolbar` : Barre d'outils (recherche, actions)
- `datatable_table` : Table HTML
- `datatable_pagination` : Composant de pagination
- `datatable_styles` : Styles CSS personnalisés

#### Blocks de Détail

- `datatable_search` : Composant de recherche
- `datatable_filters` : Zone des filtres personnalisés
- `datatable_actions` : Actions globales (export, etc.)
- `datatable_items_per_page` : Sélecteur d'éléments par page
- `datatable_headers` : En-têtes de colonnes
- `datatable_rows` : Lignes de données
- `datatable_empty` : Affichage quand aucune donnée

#### Blocks de Classes CSS

- `datatable_table_classes` : Classes CSS de la table
- `datatable_thead_classes` : Classes CSS de l'en-tête
- `datatable_tbody_classes` : Classes CSS du corps
- `datatable_row_classes` : Classes CSS des lignes

## Templates Partiels

### Composants Inclus

Le bundle utilise des templates partiels pour chaque composant :

```
templates/SigmasoftDataTable/components/
├── _search.html.twig          # Composant de recherche
├── _items_per_page.html.twig  # Sélecteur d'éléments par page
├── _header_cell.html.twig     # Cellule d'en-tête
├── _body_cell.html.twig       # Cellule de données
├── _pagination.html.twig      # Pagination complète
└── _alerts.html.twig          # Alertes et messages
```

### Personnalisation des Composants

Vous pouvez surcharger n'importe quel composant :

```twig
{# templates/bundles/SigmasoftDataTableBundle/components/_search.html.twig #}
<div class="my-custom-search">
    <input type="text" 
           class="form-control"
           placeholder="Recherche personnalisée..."
           value="{{ this.searchInput }}"
           data-model="searchInput"
           data-action="input->live#action:1000"
           data-live-action-param="search">
</div>
```

## Support des Thèmes

### Thèmes Disponibles

Le bundle supporte plusieurs thèmes prédéfinis :

- `bootstrap5` (par défaut) : Compatible Bootstrap 5
- `minimal` : Version minimaliste sans framework CSS
- `custom` : Thème personnalisé

### Configuration du Thème

```php
// Dans votre contrôleur
$config = $this->dataTableBuilder
    ->createDataTable(Product::class)
    ->setTheme('bootstrap5'); // ou 'minimal', 'custom'
```

Ou via la configuration globale :

```yaml
# config/packages/sigmasoft_data_table.yaml
sigmasoft_data_table:
    defaults:
        theme: 'bootstrap5'
```

### Classes CSS Automatiques

Le template ajoute automatiquement des classes CSS basées sur le thème :

```html
<div class="sigmasoft-datatable sigmasoft-datatable--bootstrap5" data-entity="product">
    <!-- Contenu de la DataTable -->
</div>
```

## Exemples de Personnalisation

### 1. Thème Personnalisé Complet

```twig
{# templates/my_datatable.html.twig #}
{% extends '@SigmasoftDataTable/datatable.html.twig' %}

{% block datatable_wrapper %}
    <div class="custom-datatable-wrapper">
        <div class="custom-header">
            <h3>{{ config.getEntityClass()|split('\\')|last }} Management</h3>
            {{ block('datatable_toolbar') }}
        </div>
        
        <div class="custom-body">
            {{ block('datatable_alerts') }}
            {{ block('datatable_content') }}
        </div>
        
        {% if config.isPaginationEnabled() %}
            <div class="custom-footer">
                {{ block('datatable_pagination') }}
            </div>
        {% endif %}
    </div>
{% endblock %}

{% block datatable_theme_styles %}
    .custom-datatable-wrapper {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .custom-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
    }
    
    .custom-body {
        background: white;
    }
    
    .custom-footer {
        background: #f8fafc;
        padding: 1rem;
        border-top: 1px solid #e2e8f0;
    }
{% endblock %}
```

### 2. Recherche Avancée

```twig
{% extends '@SigmasoftDataTable/datatable.html.twig' %}

{% block datatable_search %}
    <div class="advanced-search">
        <div class="input-group">
            <select class="form-select" style="max-width: 120px;">
                <option value="">Tous les champs</option>
                <option value="name">Nom</option>
                <option value="email">Email</option>
                <option value="status">Statut</option>
            </select>
            <input type="text"
                   class="form-control"
                   placeholder="Recherche avancée..."
                   value="{{ this.searchInput }}"
                   data-model="searchInput"
                   data-action="input->live#action:1000"
                   data-live-action-param="search">
            <button class="btn btn-primary" type="button">
                <i class="bi bi-funnel"></i> Filtres
            </button>
        </div>
    </div>
{% endblock %}
```

### 3. Actions Personnalisées

```twig
{% extends '@SigmasoftDataTable/datatable.html.twig' %}

{% block datatable_actions %}
    <div class="d-flex gap-2">
        <button class="btn btn-success btn-sm">
            <i class="bi bi-plus"></i> Nouveau
        </button>
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                    type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> Exporter
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-excel"></i> Excel</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-pdf"></i> PDF</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-text"></i> CSV</a></li>
            </ul>
        </div>
    </div>
{% endblock %}
```

### 4. Version Minimaliste

```twig
{% extends '@SigmasoftDataTable/datatable.html.twig' %}

{% block datatable_wrapper %}
    <div class="minimal-datatable">
        {% if config.isSearchEnabled() %}
            <div class="minimal-search">
                {{ block('datatable_search') }}
            </div>
        {% endif %}
        
        {{ block('datatable_table') }}
        
        {% if config.isPaginationEnabled() %}
            <div class="minimal-pagination">
                {{ block('datatable_pagination') }}
            </div>
        {% endif %}
    </div>
{% endblock %}

{% block datatable_table_classes %}minimal-table{% endblock %}

{% block datatable_theme_styles %}
    .minimal-datatable {
        font-family: system-ui, sans-serif;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .minimal-search {
        padding: 1rem;
        background: #f9f9f9;
        border-bottom: 1px solid #ddd;
    }
    
    .minimal-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    
    .minimal-table th,
    .minimal-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .minimal-table th {
        background: #f5f5f5;
        font-weight: 600;
    }
    
    .minimal-pagination {
        padding: 1rem;
        text-align: center;
        background: #f9f9f9;
        border-top: 1px solid #ddd;
    }
{% endblock %}
```

## Accessibilité

### ARIA et Sémantique

Les templates incluent automatiquement les attributs ARIA appropriés :

```twig
<button type="button"
        class="btn btn-link sort-button"
        data-action="live#action"
        data-live-action-param="sort"
        data-live-field-param="{{ column.getName() }}"
        aria-label="{{ 'datatable.sort.by'|trans({'%field%': column.getLabel()}) }}">
    <i class="bi bi-arrow-down-up" aria-hidden="true"></i>
</button>
```

### Navigation au Clavier

La pagination supporte la navigation au clavier :

```twig
<ul class="pagination" role="navigation" aria-label="Navigation par page">
    <li class="page-item">
        <button class="page-link" 
                aria-label="Page précédente"
                data-action="live#action"
                data-live-action-param="changePage"
                data-live-page-param="{{ current - 1 }}">
            <span aria-hidden="true">&laquo;</span>
        </button>
    </li>
</ul>
```

## Internationalisation

### Traductions Automatiques

Le bundle utilise le système de traduction Symfony :

```twig
<p>{{ 'datatable.no_data'|trans({}, 'SigmasoftDataTable') }}</p>
<small>{{ 'datatable.no_results_for'|trans({'%search%': searchQuery}, 'SigmasoftDataTable') }}</small>
```

### Langues Supportées

- Français (`fr`) - par défaut
- Anglais (`en`)

Vous pouvez ajouter vos propres traductions en créant des fichiers dans `translations/SigmasoftDataTable.{locale}.yaml`.

## Bonnes Pratiques

### 1. Structure Cohérente

Gardez une structure cohérente lors de la personnalisation :

```twig
{% block datatable_custom_section %}
    <div class="my-section">
        <div class="my-section__header">
            <!-- En-tête -->
        </div>
        <div class="my-section__body">
            <!-- Contenu -->
        </div>
        <div class="my-section__footer">
            <!-- Pied -->
        </div>
    </div>
{% endblock %}
```

### 2. Préfixage CSS

Utilisez des préfixes pour éviter les conflits CSS :

```css
.my-app-datatable {
    /* Vos styles personnalisés */
}

.my-app-datatable .sort-button {
    /* Surcharge des styles du bundle */
}
```

### 3. Responsive Design

Assurez-vous que vos personnalisations restent responsive :

```twig
{% block datatable_toolbar %}
    <div class="datatable-toolbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="datatable-toolbar__start d-flex align-items-center gap-3 flex-wrap">
            {{ block('datatable_search') }}
        </div>
        <div class="datatable-toolbar__end d-flex align-items-center gap-3 flex-wrap">
            {{ block('datatable_items_per_page') }}
        </div>
    </div>
{% endblock %}
```

## Débogage

### Mode Debug

En mode développement, des informations de debug sont automatiquement affichées :

```twig
{% if app.debug %}
    <div class="alert alert-info small m-3">
        <details>
            <summary>Debug Info</summary>
            <ul class="mb-0">
                <li>Page: {{ this.config.page }}/{{ data.getPageCount() }}</li>
                <li>Total: {{ data.getTotalItemCount() }} éléments</li>
                <li>Recherche: "{{ this.config.searchQuery }}"</li>
                <li>Tri: {{ this.config.sortField }} {{ this.config.sortDirection }}</li>
            </ul>
        </details>
    </div>
{% endif %}
```

### Variables Disponibles

Dans vos templates personnalisés, vous avez accès à :

- `config` : Configuration de la DataTable
- `data` : Données paginées
- `this` : Instance du LiveComponent
- `attributes` : Attributs HTML du conteneur

## Prochaines Étapes

- [Création de Renderers personnalisés](./custom-renderers.md)
- [Système d'événements](../user-guide/events.md)
- [Configuration avancée](../user-guide/configuration.md)