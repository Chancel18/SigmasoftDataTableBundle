---
sidebar_position: 2
---

# Personnalisation Avancée

Guide complet pour personnaliser et étendre le SigmasoftDataTableBundle selon vos besoins spécifiques. 🎨

## Vue d'ensemble

Le SigmasoftDataTableBundle offre plusieurs niveaux de personnalisation :

1. **Configuration YAML/PHP** : Personnalisation déclarative
2. **Templates Twig** : Surcharge des vues
3. **Renderers personnalisés** : Types de colonnes sur mesure
4. **Event Listeners** : Logique métier personnalisée
5. **Styles CSS** : Apparence visuelle
6. **JavaScript Stimulus** : Interactions avancées

## Personnalisation par Configuration

### Configuration YAML Avancée

```yaml title="config/packages/sigmasoft_data_table.yaml"
sigmasoft_data_table:
    # Thème global
    theme:
        variant: 'bootstrap5'  # bootstrap5, tailwind, custom
        color_scheme: 'light'  # light, dark, auto
        table_style: 'striped' # striped, bordered, hover, sm
        responsive: true
        sticky_header: true
    
    # Templates par défaut
    templates:
        table: '@SigmasoftDataTable/components/table.html.twig'
        pagination: '@SigmasoftDataTable/components/pagination.html.twig'
        search: '@SigmasoftDataTable/components/search.html.twig'
        actions: '@SigmasoftDataTable/components/actions.html.twig'
        filters: '@SigmasoftDataTable/components/filters.html.twig'
    
    # Configuration globale des colonnes
    column_defaults:
        text:
            css_class: 'text-start'
            searchable: true
            sortable: true
        date:
            format: 'd/m/Y H:i'
            timezone: 'Europe/Paris'
            css_class: 'text-nowrap'
        boolean:
            true_label: 'Oui'
            false_label: 'Non'
            true_class: 'badge bg-success'
            false_class: 'badge bg-danger'
        relation:
            limit: 50
            cache_ttl: 3600
    
    # Configuration des entités
    entities:
        'App\Entity\Product':
            # Métadonnées
            label: 'Catalogue Produits'
            description: 'Gestion complète du catalogue produits'
            icon: 'fas fa-box'
            
            # Sécurité
            security:
                view_role: 'ROLE_PRODUCT_VIEW'
                edit_role: 'ROLE_PRODUCT_EDIT'
                delete_role: 'ROLE_PRODUCT_DELETE'
                owner_field: 'user'  # Pour la sécurité par propriétaire
            
            # Pagination avancée
            pagination:
                items_per_page: 25
                items_per_page_choices: [10, 25, 50, 100]
                max_items: 1000
                strategy: 'sliding'  # sliding, simple
                page_range: 5
            
            # Recherche configurée
            search:
                enabled: true
                placeholder: 'Rechercher un produit...'
                min_length: 2
                highlight_results: true
                fields: ['name', 'description', 'sku']
                boost: # Pondération des champs
                    name: 2.0
                    sku: 1.5
                    description: 1.0
            
            # Tri par défaut
            default_sort:
                field: 'createdAt'
                direction: 'desc'
            
            # Templates personnalisés
            templates:
                row: 'admin/product/_row.html.twig'
                empty: 'admin/product/_empty.html.twig'
                loading: 'admin/product/_loading.html.twig'
            
            # CSS et JS personnalisés
            assets:
                stylesheets:
                    - 'css/admin/product-table.css'
                javascripts:
                    - 'js/admin/product-table.js'
            
            # Configuration des champs
            fields:
                # Image du produit
                image:
                    type: 'image'
                    label: 'Image'
                    width: '80px'
                    sortable: false
                    searchable: false
                    options:
                        thumbnail_size: [60, 60]
                        default_image: '/images/no-image.png'
                        link_to_full: true
                        css_class: 'text-center'
                
                # SKU avec formatage
                sku:
                    type: 'string'
                    label: 'SKU'
                    searchable: true
                    sortable: true
                    width: '120px'
                    css_class: 'font-monospace fw-bold'
                    transform: 'strtoupper'
                
                # Nom avec lien
                name:
                    type: 'link'
                    label: 'Nom du Produit'
                    searchable: true
                    sortable: true
                    options:
                        route: 'product_show'
                        route_params: ['id']
                        target: '_blank'
                        css_class: 'fw-bold text-primary'
                
                # Prix avec formatage
                price:
                    type: 'currency'
                    label: 'Prix'
                    sortable: true
                    searchable: false
                    width: '100px'
                    options:
                        currency: 'EUR'
                        locale: 'fr_FR'
                        precision: 2
                        css_class: 'text-end fw-bold'
                
                # Stock avec badge coloré
                stock:
                    type: 'number'
                    label: 'Stock'
                    sortable: true
                    searchable: false
                    width: '80px'
                    renderer: 'stock_badge'
                    css_class: 'text-center'
                
                # Catégorie avec relation
                category:
                    type: 'relation'
                    label: 'Catégorie'
                    relation:
                        entity: 'App\Entity\Category'
                        field: 'name'
                        route: 'category_show'
                        route_params: ['id']
                    searchable: true
                    sortable: true
                    css_class: 'text-muted'
                
                # Tags multiples
                tags:
                    type: 'collection'
                    label: 'Tags'
                    relation:
                        entity: 'App\Entity\Tag'
                        field: 'name'
                    renderer: 'tag_list'
                    searchable: false
                    sortable: false
                
                # Statut avec badge
                status:
                    type: 'choice'
                    label: 'Statut'
                    sortable: true
                    searchable: true
                    width: '120px'
                    choices:
                        draft: 'Brouillon'
                        published: 'Publié'
                        archived: 'Archivé'
                    choice_options:
                        draft: 
                            badge_class: 'bg-secondary'
                            icon: 'fas fa-edit'
                        published: 
                            badge_class: 'bg-success'
                            icon: 'fas fa-check'
                        archived: 
                            badge_class: 'bg-warning'
                            icon: 'fas fa-archive'
                
                # Date de création formatée
                createdAt:
                    type: 'datetime'
                    label: 'Créé le'
                    format: 'd/m/Y à H:i'
                    timezone: 'Europe/Paris'
                    sortable: true
                    searchable: false
                    width: '140px'
                    css_class: 'text-muted small'
            
            # Actions personnalisées
            actions:
                # Action de vue
                view:
                    label: 'Voir'
                    icon: 'fas fa-eye'
                    route: 'product_show'
                    variant: 'info'
                    size: 'sm'
                    condition: '@security.isGranted("PRODUCT_VIEW", object)'
                
                # Action d'édition
                edit:
                    label: 'Modifier'
                    icon: 'fas fa-edit'
                    route: 'product_edit'
                    variant: 'warning'
                    size: 'sm'
                    condition: '@security.isGranted("PRODUCT_EDIT", object)'
                
                # Action de duplication
                duplicate:
                    label: 'Dupliquer'
                    icon: 'fas fa-copy'
                    route: 'product_duplicate'
                    variant: 'secondary'
                    size: 'sm'
                    condition: '@security.isGranted("PRODUCT_CREATE")'
                    confirm: true
                    confirm_message: 'Dupliquer ce produit ?'
                
                # Action de suppression
                delete:
                    label: 'Supprimer'
                    icon: 'fas fa-trash'
                    route: 'product_delete'
                    variant: 'danger'
                    size: 'sm'
                    condition: '@security.isGranted("PRODUCT_DELETE", object)'
                    confirm: true
                    confirm_message: 'Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.'
                    confirm_button: 'Oui, supprimer'
                    cancel_button: 'Annuler'
            
            # Actions groupées
            bulk_actions:
                enabled: true
                actions:
                    publish:
                        label: 'Publier'
                        icon: 'fas fa-check'
                        variant: 'success'
                        route: 'product_bulk_publish'
                        confirm: true
                        confirm_message: 'Publier les produits sélectionnés ?'
                    
                    archive:
                        label: 'Archiver'
                        icon: 'fas fa-archive'
                        variant: 'warning'
                        route: 'product_bulk_archive'
                        confirm: true
                    
                    delete:
                        label: 'Supprimer'
                        icon: 'fas fa-trash'
                        variant: 'danger'
                        route: 'product_bulk_delete'
                        confirm: true
                        confirm_message: 'Supprimer définitivement les produits sélectionnés ?'
            
            # Filtres avancés
            filters:
                category:
                    type: 'entity'
                    label: 'Catégorie'
                    entity: 'App\Entity\Category'
                    choice_label: 'name'
                    placeholder: 'Toutes les catégories'
                    multiple: true
                
                price_range:
                    type: 'number_range'
                    label: 'Fourchette de prix'
                    options:
                        min_placeholder: 'Prix min'
                        max_placeholder: 'Prix max'
                        currency: '€'
                
                status:
                    type: 'select'
                    label: 'Statut'
                    choices:
                        '': 'Tous les statuts'
                        draft: 'Brouillons'
                        published: 'Publiés'
                        archived: 'Archivés'
                
                has_image:
                    type: 'boolean'
                    label: 'Avec image'
                    choices:
                        '': 'Tous'
                        '1': 'Avec image'
                        '0': 'Sans image'
                
                created_at:
                    type: 'date_range'
                    label: 'Période de création'
                    options:
                        start_placeholder: 'Date de début'
                        end_placeholder: 'Date de fin'
            
            # Export personnalisé
            export:
                enabled: true
                formats: ['csv', 'excel', 'pdf']
                filename_pattern: 'produits_{date}_{time}'
                options:
                    csv:
                        delimiter: ';'
                        enclosure: '"'
                        encoding: 'UTF-8'
                        include_bom: true
                    excel:
                        sheet_name: 'Produits'
                        auto_filter: true
                        freeze_first_row: true
                        column_widths:
                            sku: 15
                            name: 30
                            price: 12
                    pdf:
                        orientation: 'landscape'
                        format: 'A4'
                        title: 'Catalogue Produits'
                        header: true
                        footer: true
```

### Configuration PHP Programmatique

```php title="src/Config/DataTable/ProductDataTableConfig.php"
<?php

declare(strict_types=1);

namespace App\Config\DataTable;

use App\Entity\Product;
use App\Security\ProductVoter;
use Sigmasoft\DataTableBundle\Configuration\AbstractDataTableConfiguration;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Symfony\Component\Security\Core\Security;

class ProductDataTableConfig extends AbstractDataTableConfiguration
{
    public function __construct(
        private readonly Security $security
    ) {}

    public function configure(DataTableConfiguration $config): void
    {
        $config
            ->setEntityClass(Product::class)
            ->setLabel('Catalogue Produits')
            ->setDescription('Gestion complète du catalogue produits')
            ->setIcon('fas fa-box')
            
            // Configuration de base
            ->setItemsPerPage(25)
            ->setEnableSearch(true)
            ->setEnableSort(true)
            ->setEnablePagination(true)
            ->setTableClass('table table-striped table-hover')
            
            // Sécurité
            ->setViewRole('ROLE_PRODUCT_VIEW')
            ->setEditRole('ROLE_PRODUCT_EDIT')
            ->setDeleteRole('ROLE_PRODUCT_DELETE')
            
            // Configuration des champs avec logique conditionnelle
            ->addField('image', [
                'type' => 'image',
                'label' => 'Image',
                'width' => '80px',
                'options' => [
                    'thumbnail_size' => [60, 60],
                    'default_image' => '/images/no-image.png'
                ]
            ])
            
            ->addField('sku', [
                'type' => 'string',
                'label' => 'SKU',
                'css_class' => 'font-monospace fw-bold',
                'transform' => 'strtoupper'
            ])
            
            ->addField('name', [
                'type' => 'link',
                'label' => 'Nom du Produit',
                'options' => [
                    'route' => 'product_show',
                    'route_params' => ['id'],
                    'css_class' => 'fw-bold text-primary'
                ]
            ])
            
            ->addField('price', [
                'type' => 'currency',
                'label' => 'Prix',
                'width' => '100px',
                'options' => [
                    'currency' => 'EUR',
                    'locale' => 'fr_FR',
                    'css_class' => 'text-end fw-bold'
                ]
            ])
            
            ->addField('stock', [
                'type' => 'number',
                'label' => 'Stock',
                'renderer' => 'stock_badge',
                'css_class' => 'text-center'
            ])
            
            ->addField('category', [
                'type' => 'relation',
                'label' => 'Catégorie',
                'relation' => [
                    'entity' => 'App\Entity\Category',
                    'field' => 'name'
                ]
            ])
            
            ->addField('status', [
                'type' => 'choice',
                'label' => 'Statut',
                'choices' => [
                    'draft' => 'Brouillon',
                    'published' => 'Publié',
                    'archived' => 'Archivé'
                ],
                'choice_options' => [
                    'draft' => ['badge_class' => 'bg-secondary'],
                    'published' => ['badge_class' => 'bg-success'],
                    'archived' => ['badge_class' => 'bg-warning']
                ]
            ])
            
            ->addField('createdAt', [
                'type' => 'datetime',
                'label' => 'Créé le',
                'format' => 'd/m/Y à H:i'
            ]);
        
        // Actions conditionnelles basées sur les permissions
        if ($this->security->isGranted('ROLE_PRODUCT_VIEW')) {
            $config->addAction('view', [
                'label' => 'Voir',
                'icon' => 'fas fa-eye',
                'route' => 'product_show',
                'variant' => 'info'
            ]);
        }
        
        if ($this->security->isGranted('ROLE_PRODUCT_EDIT')) {
            $config->addAction('edit', [
                'label' => 'Modifier',
                'icon' => 'fas fa-edit',
                'route' => 'product_edit',
                'variant' => 'warning'
            ]);
        }
        
        if ($this->security->isGranted('ROLE_PRODUCT_DELETE')) {
            $config->addAction('delete', [
                'label' => 'Supprimer',
                'icon' => 'fas fa-trash',
                'route' => 'product_delete',
                'variant' => 'danger',
                'confirm' => true
            ]);
        }
        
        // Filtres basés sur le contexte utilisateur
        $config
            ->addFilter('category', [
                'type' => 'entity',
                'entity' => 'App\Entity\Category',
                'choice_label' => 'name'
            ])
            ->addFilter('status', [
                'type' => 'select',
                'choices' => [
                    '' => 'Tous les statuts',
                    'draft' => 'Brouillons',
                    'published' => 'Publiés',
                    'archived' => 'Archivés'
                ]
            ]);
    }
}
```

## Renderers Personnalisés

### Création d'un Renderer de Badge de Stock

```php title="src/DataTable/Renderer/StockBadgeRenderer.php"
<?php

declare(strict_types=1);

namespace App\DataTable\Renderer;

use Sigmasoft\DataTableBundle\Column\ColumnInterface;
use Sigmasoft\DataTableBundle\Renderer\AbstractColumnRenderer;

class StockBadgeRenderer extends AbstractColumnRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'stock_badge';
    }

    public function render(mixed $value, object $entity, ColumnInterface $column): string
    {
        $stock = (int) $value;
        
        // Déterminer la classe CSS basée sur le niveau de stock
        [$badgeClass, $icon, $text] = match (true) {
            $stock <= 0 => ['bg-danger', 'fas fa-times-circle', 'Rupture'],
            $stock <= 5 => ['bg-warning text-dark', 'fas fa-exclamation-triangle', 'Faible'],
            $stock <= 20 => ['bg-info', 'fas fa-info-circle', 'Moyen'],
            default => ['bg-success', 'fas fa-check-circle', 'Bon']
        };
        
        return sprintf(
            '<span class="badge %s" title="Stock: %d unités" data-bs-toggle="tooltip">
                <i class="%s me-1"></i>
                %s (%d)
            </span>',
            $badgeClass,
            $stock,
            $icon,
            $text,
            $stock
        );
    }
}
```

### Renderer pour Liste de Tags

```php title="src/DataTable/Renderer/TagListRenderer.php"
<?php

declare(strict_types=1);

namespace App\DataTable\Renderer;

use Doctrine\Common\Collections\Collection;
use Sigmasoft\DataTableBundle\Column\ColumnInterface;
use Sigmasoft\DataTableBundle\Renderer\AbstractColumnRenderer;

class TagListRenderer extends AbstractColumnRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'tag_list';
    }

    public function render(mixed $value, object $entity, ColumnInterface $column): string
    {
        if (!$value instanceof Collection || $value->isEmpty()) {
            return '<span class="text-muted small">Aucun tag</span>';
        }
        
        $tags = [];
        $maxTags = 3; // Limiter l'affichage
        $count = 0;
        
        foreach ($value as $tag) {
            if ($count >= $maxTags) {
                $remaining = $value->count() - $maxTags;
                $tags[] = sprintf(
                    '<span class="badge bg-secondary" data-bs-toggle="tooltip" title="et %d autres">
                        +%d
                    </span>',
                    $remaining,
                    $remaining
                );
                break;
            }
            
            $color = $tag->getColor() ?: '#6c757d';
            $tags[] = sprintf(
                '<span class="badge" style="background-color: %s" data-bs-toggle="tooltip" title="%s">
                    %s
                </span>',
                htmlspecialchars($color),
                htmlspecialchars($tag->getDescription() ?: ''),
                htmlspecialchars($tag->getName())
            );
            
            $count++;
        }
        
        return '<div class="d-flex flex-wrap gap-1">' . implode(' ', $tags) . '</div>';
    }
}
```

### Renderer d'Image avec Lightbox

```php title="src/DataTable/Renderer/ImageRenderer.php"
<?php

declare(strict_types=1);

namespace App\DataTable\Renderer;

use Sigmasoft\DataTableBundle\Column\ColumnInterface;
use Sigmasoft\DataTableBundle\Renderer\AbstractColumnRenderer;
use Symfony\Component\Asset\Packages;

class ImageRenderer extends AbstractColumnRenderer
{
    public function __construct(
        private readonly Packages $packages
    ) {}

    public function supports(string $type): bool
    {
        return $type === 'image';
    }

    public function render(mixed $value, object $entity, ColumnInterface $column): string
    {
        $options = $column->getOptions();
        $thumbnailSize = $options['thumbnail_size'] ?? [60, 60];
        $defaultImage = $options['default_image'] ?? '/images/no-image.png';
        $linkToFull = $options['link_to_full'] ?? true;
        
        // Chemin de l'image
        $imagePath = $value ? $this->packages->getUrl($value) : $defaultImage;
        
        // Générer la miniature
        $thumbnail = $this->generateThumbnail($imagePath, $thumbnailSize);
        
        $imgTag = sprintf(
            '<img src="%s" alt="Image" class="img-thumbnail" style="width: %dpx; height: %dpx; object-fit: cover;" loading="lazy">',
            htmlspecialchars($thumbnail),
            $thumbnailSize[0],
            $thumbnailSize[1]
        );
        
        // Ajouter le lien vers l'image complète si demandé
        if ($linkToFull && $value) {
            return sprintf(
                '<a href="%s" data-bs-toggle="modal" data-bs-target="#imageModal" data-image-src="%s" class="text-decoration-none">
                    %s
                </a>',
                htmlspecialchars($imagePath),
                htmlspecialchars($imagePath),
                $imgTag
            );
        }
        
        return $imgTag;
    }
    
    private function generateThumbnail(string $imagePath, array $size): string
    {
        // Ici vous pourriez intégrer LiipImagineBundle ou un autre système de miniatures
        // Pour cet exemple, on retourne l'image originale
        return $imagePath;
    }
}
```

## Templates Personnalisés

### Template de Ligne Personnalisé

```twig title="templates/admin/product/_row.html.twig"
{# Template personnalisé pour les lignes de produits #}
<tr class="product-row {{ product.isActive ? '' : 'table-warning' }}" 
    data-product-id="{{ product.id }}"
    data-product-status="{{ product.status }}">
    
    {% if config.bulkActionsEnabled %}
        <td class="text-center">
            <input type="checkbox" 
                   class="form-check-input bulk-select" 
                   value="{{ product.id }}"
                   data-bs-toggle="tooltip" 
                   title="Sélectionner ce produit">
        </td>
    {% endif %}
    
    {% for column in config.columns %}
        <td class="{{ column.cssClass|default('') }} {{ column.align|default('start') }}"
            {% if column.width %}style="width: {{ column.width }}"{% endif %}>
            
            {% set value = attribute(product, column.property) %}
            
            {# Logique de rendu personnalisée par type de colonne #}
            {% if column.type == 'image' %}
                {% include 'admin/product/_cell_image.html.twig' with {
                    'product': product,
                    'value': value,
                    'options': column.options
                } %}
                
            {% elseif column.type == 'price' %}
                {% include 'admin/product/_cell_price.html.twig' with {
                    'product': product,
                    'value': value
                } %}
                
            {% elseif column.type == 'stock' %}
                {% include 'admin/product/_cell_stock.html.twig' with {
                    'product': product,
                    'value': value
                } %}
                
            {% elseif column.type == 'status' %}
                {% include 'admin/product/_cell_status.html.twig' with {
                    'product': product,
                    'value': value
                } %}
                
            {% else %}
                {# Rendu par défaut #}
                {{ datatable_cell_render(value, product, column) }}
            {% endif %}
        </td>
    {% endfor %}
    
    {# Colonne d'actions #}
    {% if config.actions is not empty %}
        <td class="text-end text-nowrap">
            <div class="btn-group btn-group-sm" role="group">
                {% for action in config.actions %}
                    {% if action.condition is not defined or action.condition %}
                        <a href="{{ path(action.route, action.routeParams|default({})) }}"
                           class="btn btn-outline-{{ action.variant|default('primary') }}"
                           {% if action.confirm %}
                               data-bs-toggle="modal"
                               data-bs-target="#confirmModal"
                               data-action-url="{{ path(action.route, action.routeParams|default({})) }}"
                               data-confirm-message="{{ action.confirmMessage|default('Êtes-vous sûr ?') }}"
                           {% endif %}
                           data-bs-toggle="tooltip"
                           title="{{ action.label }}">
                            {% if action.icon %}
                                <i class="{{ action.icon }}"></i>
                            {% else %}
                                {{ action.label }}
                            {% endif %}
                        </a>
                    {% endif %}
                {% endfor %}
            </div>
        </td>
    {% endif %}
</tr>
```

### Cellules Spécialisées

```twig title="templates/admin/product/_cell_price.html.twig"
{# Cellule de prix avec indicateurs visuels #}
{% set isOnSale = product.salePrice and product.salePrice < product.price %}

<div class="price-cell">
    {% if isOnSale %}
        <div class="d-flex flex-column">
            <span class="text-decoration-line-through text-muted small">
                {{ product.price|format_currency('EUR', locale='fr') }}
            </span>
            <span class="fw-bold text-danger">
                {{ product.salePrice|format_currency('EUR', locale='fr') }}
                <span class="badge bg-danger ms-1">PROMO</span>
            </span>
        </div>
    {% else %}
        <span class="fw-bold">
            {{ value|format_currency('EUR', locale='fr') }}
        </span>
    {% endif %}
    
    {% if product.costPrice %}
        <div class="text-muted small">
            Coût: {{ product.costPrice|format_currency('EUR', locale='fr') }}
            {% set margin = ((value - product.costPrice) / value * 100)|round(1) %}
            <span class="badge bg-{{ margin > 50 ? 'success' : (margin > 30 ? 'warning' : 'danger') }}">
                {{ margin }}%
            </span>
        </div>
    {% endif %}
</div>
```

```twig title="templates/admin/product/_cell_stock.html.twig"
{# Cellule de stock avec indicateur de niveau #}
{% set stockLevel = value %}
{% set stockClass = stockLevel <= 0 ? 'danger' : (stockLevel <= 5 ? 'warning' : (stockLevel <= 20 ? 'info' : 'success')) %}
{% set stockIcon = stockLevel <= 0 ? 'times-circle' : (stockLevel <= 5 ? 'exclamation-triangle' : (stockLevel <= 20 ? 'info-circle' : 'check-circle')) %}

<div class="stock-cell text-center">
    <span class="badge bg-{{ stockClass }}" 
          data-bs-toggle="tooltip" 
          title="Stock actuel: {{ stockLevel }} unités">
        <i class="fas fa-{{ stockIcon }} me-1"></i>
        {{ stockLevel }}
    </span>
    
    {% if product.reservedStock > 0 %}
        <div class="text-muted small mt-1">
            <i class="fas fa-lock me-1"></i>
            {{ product.reservedStock }} réservé{{ product.reservedStock > 1 ? 's' : '' }}
        </div>
    {% endif %}
    
    {% if product.stockAlert and stockLevel <= product.stockAlert %}
        <div class="text-warning small mt-1">
            <i class="fas fa-bell me-1"></i>
            Seuil d'alerte
        </div>
    {% endif %}
</div>
```

## Event Listeners Personnalisés

### Listener pour Actions Automatiques

```php title="src/EventListener/DataTableEventListener.php"
<?php

declare(strict_types=1);

namespace App\EventListener;

use Sigmasoft\DataTableBundle\Event\DataTableEvent;
use Sigmasoft\DataTableBundle\Event\DataTableEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;

class DataTableEventListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DataTableEvents::PRE_QUERY => 'onPreQuery',
            DataTableEvents::POST_QUERY => 'onPostQuery',
            DataTableEvents::PRE_RENDER => 'onPreRender',
            DataTableEvents::POST_RENDER => 'onPostRender',
            DataTableEvents::INLINE_EDIT => 'onInlineEdit',
            DataTableEvents::BULK_ACTION => 'onBulkAction',
        ];
    }

    public function onPreQuery(DataTableEvent $event): void
    {
        $this->logger->info('DataTable query started', [
            'entity' => $event->getEntityClass(),
            'search' => $event->getSearchTerm(),
            'sort' => $event->getSortField(),
            'page' => $event->getCurrentPage()
        ]);
        
        // Modifier la requête si nécessaire
        $queryBuilder = $event->getQueryBuilder();
        
        // Exemple: filtrer par utilisateur connecté
        if ($event->getEntityClass() === 'App\Entity\Product') {
            $queryBuilder
                ->andWhere('e.owner = :current_user')
                ->setParameter('current_user', $this->getCurrentUser());
        }
    }

    public function onPostQuery(DataTableEvent $event): void
    {
        $results = $event->getResults();
        
        $this->logger->info('DataTable query completed', [
            'entity' => $event->getEntityClass(),
            'total_items' => $results->getTotalItemCount(),
            'current_page_items' => count($results->getItems())
        ]);
        
        // Post-traitement des résultats
        foreach ($results->getItems() as $item) {
            // Exemple: lazy loading de relations
            if (method_exists($item, 'getImages')) {
                $item->getImages()->initialize();
            }
        }
    }

    public function onPreRender(DataTableEvent $event): void
    {
        // Ajouter des variables globales au template
        $event->addTemplateVariable('current_user', $this->getCurrentUser());
        $event->addTemplateVariable('app_name', 'Mon Application');
        
        // Modifier la configuration à la volée
        $config = $event->getConfiguration();
        
        // Exemple: masquer certaines colonnes selon les permissions
        if (!$this->isGranted('ROLE_ADMIN')) {
            $config->hideColumn('price');
            $config->hideColumn('cost');
        }
    }

    public function onPostRender(DataTableEvent $event): void
    {
        $this->logger->debug('DataTable rendered', [
            'entity' => $event->getEntityClass(),
            'render_time' => $event->getRenderTime()
        ]);
    }

    public function onInlineEdit(DataTableEvent $event): void
    {
        $entity = $event->getEntity();
        $field = $event->getField();
        $oldValue = $event->getOldValue();
        $newValue = $event->getNewValue();
        
        $this->logger->info('Inline edit performed', [
            'entity_class' => get_class($entity),
            'entity_id' => method_exists($entity, 'getId') ? $entity->getId() : null,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue
        ]);
        
        // Logique métier personnalisée
        if ($field === 'status' && $newValue === 'published') {
            // Envoyer une notification
            $this->notifyStatusChange($entity, $newValue);
        }
    }

    public function onBulkAction(DataTableEvent $event): void
    {
        $action = $event->getBulkAction();
        $entities = $event->getEntities();
        
        $this->logger->info('Bulk action performed', [
            'action' => $action,
            'entity_count' => count($entities)
        ]);
        
        // Actions personnalisées après une action groupée
        if ($action === 'delete') {
            $this->cleanupRelatedData($entities);
        }
    }

    private function getCurrentUser(): ?object
    {
        // Implémentation pour récupérer l'utilisateur actuel
        return null;
    }

    private function isGranted(string $role): bool
    {
        // Implémentation pour vérifier les permissions
        return false;
    }

    private function notifyStatusChange(object $entity, mixed $newValue): void
    {
        // Implémentation des notifications
    }

    private function cleanupRelatedData(array $entities): void
    {
        // Nettoyage des données liées
    }
}
```

## Assets Personnalisés

### CSS Personnalisé

```css title="assets/styles/datatable-custom.css"
/* Styles personnalisés pour SigmasoftDataTableBundle */

:root {
    --datatable-primary: #0d6efd;
    --datatable-success: #198754;
    --datatable-warning: #ffc107;
    --datatable-danger: #dc3545;
    --datatable-info: #0dcaf0;
    --datatable-border: #dee2e6;
    --datatable-hover: #f8f9fa;
}

/* Table principale */
.sigmasoft-datatable {
    --bs-table-hover-color: var(--bs-emphasis-color);
    --bs-table-hover-bg: var(--datatable-hover);
}

.sigmasoft-datatable .table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.sigmasoft-datatable .table thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Colonnes triables */
.sigmasoft-datatable .table thead th.sortable {
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
    position: relative;
}

.sigmasoft-datatable .table thead th.sortable:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6b4190 100%);
    transform: translateY(-1px);
}

.sigmasoft-datatable .table thead th.sortable::after {
    content: '\f0dc';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    float: right;
    margin-left: 8px;
    opacity: 0.5;
    transition: opacity 0.2s ease;
}

.sigmasoft-datatable .table thead th.sortable:hover::after {
    opacity: 1;
}

.sigmasoft-datatable .table thead th.sortable.sorted-asc::after {
    content: '\f0de';
    color: var(--datatable-warning);
    opacity: 1;
}

.sigmasoft-datatable .table thead th.sortable.sorted-desc::after {
    content: '\f0dd';
    color: var(--datatable-warning);
    opacity: 1;
}

/* Lignes de données */
.sigmasoft-datatable .table tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--datatable-border);
}

.sigmasoft-datatable .table tbody tr:hover {
    background-color: var(--datatable-hover);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.sigmasoft-datatable .table tbody tr.selected {
    background-color: rgba(13, 110, 253, 0.1);
    border-color: var(--datatable-primary);
}

/* Cellules */
.sigmasoft-datatable .table td {
    vertical-align: middle;
    padding: 12px 8px;
    border: none;
}

/* Badges et indicateurs */
.sigmasoft-datatable .badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 4px;
}

.sigmasoft-datatable .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.sigmasoft-datatable .stock-indicator {
    position: relative;
    display: inline-block;
}

.sigmasoft-datatable .stock-indicator::before {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--datatable-success);
}

.sigmasoft-datatable .stock-indicator.low::before {
    background: var(--datatable-warning);
}

.sigmasoft-datatable .stock-indicator.empty::before {
    background: var(--datatable-danger);
}

/* Actions */
.sigmasoft-datatable .btn-group .btn {
    padding: 4px 8px;
    font-size: 0.75rem;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.sigmasoft-datatable .btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Édition inline */
.sigmasoft-datatable .inline-edit-field {
    border: 2px dashed transparent;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
}

.sigmasoft-datatable .inline-edit-field:hover {
    border-color: var(--datatable-primary);
    background-color: rgba(13, 110, 253, 0.05);
}

.sigmasoft-datatable .inline-edit-field.editing {
    border-color: var(--datatable-warning);
    background-color: rgba(255, 193, 7, 0.1);
    cursor: default;
}

.sigmasoft-datatable .inline-edit-field.saving {
    border-color: var(--datatable-info);
    background-color: rgba(13, 202, 240, 0.1);
}

.sigmasoft-datatable .inline-edit-field.success {
    border-color: var(--datatable-success);
    background-color: rgba(25, 135, 84, 0.1);
    animation: success-pulse 0.6s ease;
}

.sigmasoft-datatable .inline-edit-field.error {
    border-color: var(--datatable-danger);
    background-color: rgba(220, 53, 69, 0.1);
    animation: error-shake 0.6s ease;
}

/* Animations */
@keyframes success-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes error-shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Recherche */
.sigmasoft-datatable .search-container {
    position: relative;
}

.sigmasoft-datatable .search-container .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 5;
}

.sigmasoft-datatable .search-container input {
    padding-left: 40px;
    border: 2px solid var(--datatable-border);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.sigmasoft-datatable .search-container input:focus {
    border-color: var(--datatable-primary);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}

/* Pagination */
.sigmasoft-datatable .pagination {
    margin: 0;
}

.sigmasoft-datatable .pagination .page-link {
    border: none;
    color: var(--datatable-primary);
    padding: 8px 12px;
    margin: 0 2px;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.sigmasoft-datatable .pagination .page-link:hover {
    background-color: var(--datatable-primary);
    color: white;
    transform: translateY(-1px);
}

.sigmasoft-datatable .pagination .page-item.active .page-link {
    background-color: var(--datatable-primary);
    color: white;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .sigmasoft-datatable .table {
        font-size: 0.875rem;
    }
    
    .sigmasoft-datatable .table td,
    .sigmasoft-datatable .table th {
        padding: 8px 4px;
    }
    
    .sigmasoft-datatable .btn-group .btn {
        padding: 2px 6px;
        font-size: 0.7rem;
    }
    
    .sigmasoft-datatable .search-container input {
        font-size: 0.875rem;
    }
}

/* Mode sombre */
@media (prefers-color-scheme: dark) {
    :root {
        --datatable-border: #495057;
        --datatable-hover: #343a40;
    }
    
    .sigmasoft-datatable .table thead th {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    }
    
    .sigmasoft-datatable .table tbody tr:hover {
        background-color: var(--datatable-hover);
    }
}

/* Impression */
@media print {
    .sigmasoft-datatable .btn,
    .sigmasoft-datatable .pagination,
    .sigmasoft-datatable .search-container {
        display: none !important;
    }
    
    .sigmasoft-datatable .table {
        border-collapse: collapse !important;
    }
    
    .sigmasoft-datatable .table,
    .sigmasoft-datatable .table th,
    .sigmasoft-datatable .table td {
        border: 1px solid #000 !important;
    }
}
```

### JavaScript Avancé

```javascript title="assets/controllers/advanced_datatable_controller.js"
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = [
        "table",
        "searchInput", 
        "tableBody", 
        "pagination", 
        "loading",
        "bulkSelect",
        "bulkActions",
        "selectedCount"
    ]
    
    static values = {
        url: String,
        entityClass: String,
        autoRefresh: { type: Number, default: 0 },
        debounceDelay: { type: Number, default: 500 }
    }

    connect() {
        console.log("Advanced DataTable controller connected")
        
        // État du composant
        this.currentPage = 1
        this.currentSort = null
        this.currentSearch = ""
        this.selectedItems = new Set()
        this.searchTimeout = null
        this.refreshInterval = null
        
        // Configuration
        this.isLoading = false
        this.lastRequestTime = 0
        
        // Initialiser les événements
        this.initializeEventListeners()
        
        // Charger les données initiales
        this.loadData()
        
        // Auto-refresh si configuré
        if (this.autoRefreshValue > 0) {
            this.startAutoRefresh()
        }
        
        // Initialiser les tooltips
        this.initializeTooltips()
    }

    disconnect() {
        console.log("Advanced DataTable controller disconnected")
        
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval)
        }
        
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout)
        }
    }

    // Initialisation des événements
    initializeEventListeners() {
        // Gestion du redimensionnement
        window.addEventListener('resize', this.debounce(() => {
            this.adjustTableLayout()
        }, 250))
        
        // Gestion des raccourcis clavier
        document.addEventListener('keydown', (event) => {
            if (event.target.closest('.sigmasoft-datatable') === this.element) {
                this.handleKeyboardShortcuts(event)
            }
        })
        
        // Gestion de la visibilité de la page
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAutoRefresh()
            } else {
                this.resumeAutoRefresh()
            }
        })
    }

    // Recherche avec debouncing amélioré
    search(event) {
        const searchTerm = event.target.value.trim()
        
        // Annuler la recherche précédente
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout)
        }
        
        // Recherche immédiate si le champ est vide
        if (searchTerm === '') {
            this.currentSearch = ''
            this.currentPage = 1
            this.loadData()
            return
        }
        
        // Debouncing pour les autres cas
        this.searchTimeout = setTimeout(() => {
            if (searchTerm !== this.currentSearch) {
                this.currentSearch = searchTerm
                this.currentPage = 1
                this.loadData()
                
                // Analytics/tracking
                this.trackSearchEvent(searchTerm)
            }
        }, this.debounceDelayValue)
    }

    // Tri avancé avec indicateurs visuels
    sort(event) {
        event.preventDefault()
        
        const field = event.currentTarget.dataset.field
        const currentSort = this.currentSort
        
        // Calculer la nouvelle direction
        let direction = 'asc'
        if (currentSort && currentSort.field === field) {
            direction = currentSort.direction === 'asc' ? 'desc' : 'asc'
        }
        
        this.currentSort = { field, direction }
        
        // Mettre à jour l'UI immédiatement
        this.updateSortIndicators(field, direction)
        
        // Charger les nouvelles données
        this.loadData()
        
        // Animation de feedback
        this.animateSortChange(event.currentTarget)
    }

    // Sélection groupée avancée
    selectAll(event) {
        const isChecked = event.target.checked
        const checkboxes = this.element.querySelectorAll('.bulk-select:not([disabled])')
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked
            const id = parseInt(checkbox.value)
            
            if (isChecked) {
                this.selectedItems.add(id)
            } else {
                this.selectedItems.delete(id)
            }
        })
        
        this.updateBulkActionsUI()
        this.updateSelectedRowsUI()
    }

    selectItem(event) {
        const checkbox = event.target
        const id = parseInt(checkbox.value)
        
        if (checkbox.checked) {
            this.selectedItems.add(id)
        } else {
            this.selectedItems.delete(id)
        }
        
        this.updateBulkActionsUI()
        this.updateSelectedRowsUI()
        
        // Mettre à jour le checkbox "tout sélectionner"
        const selectAllCheckbox = this.element.querySelector('.bulk-select-all')
        if (selectAllCheckbox) {
            const totalCheckboxes = this.element.querySelectorAll('.bulk-select:not([disabled])').length
            selectAllCheckbox.checked = this.selectedItems.size === totalCheckboxes
            selectAllCheckbox.indeterminate = this.selectedItems.size > 0 && this.selectedItems.size < totalCheckboxes
        }
    }

    // Actions groupées
    async executeBulkAction(event) {
        event.preventDefault()
        
        const action = event.currentTarget.dataset.action
        const selectedIds = Array.from(this.selectedItems)
        
        if (selectedIds.length === 0) {
            this.showAlert('Veuillez sélectionner au moins un élément.', 'warning')
            return
        }
        
        // Confirmation si nécessaire
        const confirmMessage = event.currentTarget.dataset.confirmMessage
        if (confirmMessage && !confirm(confirmMessage)) {
            return
        }
        
        try {
            this.showLoading()
            
            const response = await fetch(event.currentTarget.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: action,
                    ids: selectedIds
                })
            })
            
            const result = await response.json()
            
            if (result.success) {
                this.showAlert(result.message || 'Action exécutée avec succès.', 'success')
                this.selectedItems.clear()
                this.loadData() // Recharger les données
            } else {
                this.showAlert(result.message || 'Une erreur est survenue.', 'danger')
            }
            
        } catch (error) {
            console.error('Erreur lors de l\'action groupée:', error)
            this.showAlert('Erreur de connexion. Veuillez réessayer.', 'danger')
        } finally {
            this.hideLoading()
        }
    }

    // Export avancé avec options
    async exportData(event) {
        event.preventDefault()
        
        const format = event.currentTarget.dataset.format
        const includeSelected = event.currentTarget.dataset.selectedOnly === 'true'
        
        try {
            const params = new URLSearchParams({
                format: format,
                entity: this.entityClassValue,
                search: this.currentSearch,
                selected_only: includeSelected,
                selected_ids: includeSelected ? Array.from(this.selectedItems).join(',') : ''
            })
            
            if (this.currentSort) {
                params.append('sort', this.currentSort.field)
                params.append('direction', this.currentSort.direction)
            }
            
            // Créer un lien de téléchargement temporaire
            const downloadUrl = `${this.urlValue}/export?${params}`
            const link = document.createElement('a')
            link.href = downloadUrl
            link.download = '' // Le nom sera déterminé par le serveur
            link.style.display = 'none'
            
            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
            
            this.showAlert('Export en cours de téléchargement...', 'info')
            
        } catch (error) {
            console.error('Erreur lors de l\'export:', error)
            this.showAlert('Erreur lors de l\'export. Veuillez réessayer.', 'danger')
        }
    }

    // Chargement des données avec gestion d'erreurs avancée
    async loadData() {
        if (this.isLoading) {
            return // Éviter les requêtes multiples
        }
        
        const requestTime = Date.now()
        this.lastRequestTime = requestTime
        
        try {
            this.isLoading = true
            this.showLoading()
            
            const params = new URLSearchParams({
                page: this.currentPage,
                search: this.currentSearch,
                entity: this.entityClassValue
            })
            
            if (this.currentSort) {
                params.append('sort', this.currentSort.field)
                params.append('direction', this.currentSort.direction)
            }
            
            const controller = new AbortController()
            const timeoutId = setTimeout(() => controller.abort(), 30000) // Timeout 30s
            
            const response = await fetch(`${this.urlValue}?${params}`, {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            
            clearTimeout(timeoutId)
            
            // Vérifier si cette requête est toujours la plus récente
            if (requestTime < this.lastRequestTime) {
                return // Une requête plus récente est en cours
            }
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`)
            }
            
            const data = await response.json()
            
            // Mettre à jour l'interface
            this.updateTable(data.rows)
            this.updatePagination(data.pagination)
            this.updateInfo(data.pagination)
            
            // Émettre un événement personnalisé
            this.dispatch('dataLoaded', { detail: data })
            
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('Requête annulée (timeout)')
                this.showAlert('La requête a pris trop de temps. Veuillez réessayer.', 'warning')
            } else {
                console.error('Erreur lors du chargement:', error)
                this.showError('Erreur de chargement des données')
            }
        } finally {
            this.isLoading = false
            this.hideLoading()
        }
    }

    // Gestion des raccourcis clavier
    handleKeyboardShortcuts(event) {
        // Ctrl/Cmd + F : Focus sur la recherche
        if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
            event.preventDefault()
            if (this.hasSearchInputTarget) {
                this.searchInputTarget.focus()
                this.searchInputTarget.select()
            }
        }
        
        // Échap : Nettoyer la recherche
        if (event.key === 'Escape' && this.hasSearchInputTarget && this.searchInputTarget === event.target) {
            this.searchInputTarget.value = ''
            this.search({ target: this.searchInputTarget })
        }
        
        // Ctrl/Cmd + A : Sélectionner tout
        if ((event.ctrlKey || event.metaKey) && event.key === 'a' && this.selectedItems.size > 0) {
            event.preventDefault()
            const selectAllCheckbox = this.element.querySelector('.bulk-select-all')
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = true
                this.selectAll({ target: selectAllCheckbox })
            }
        }
    }

    // Utilitaires
    debounce(func, wait) {
        let timeout
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout)
                func(...args)
            }
            clearTimeout(timeout)
            timeout = setTimeout(later, wait)
        }
    }

    showAlert(message, type = 'info') {
        // Créer et afficher une alerte Bootstrap
        const alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `
        
        const alertContainer = this.element.querySelector('.alert-container') || this.element
        const tempDiv = document.createElement('div')
        tempDiv.innerHTML = alertHTML
        
        alertContainer.insertBefore(tempDiv.firstElementChild, alertContainer.firstChild)
        
        // Auto-suppression après 5 secondes
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert')
            if (alert) {
                alert.remove()
            }
        }, 5000)
    }

    trackSearchEvent(searchTerm) {
        // Intégration avec Google Analytics, Matomo, etc.
        if (typeof gtag !== 'undefined') {
            gtag('event', 'search', {
                event_category: 'DataTable',
                event_label: this.entityClassValue,
                value: searchTerm.length
            })
        }
    }

    // Auto-refresh
    startAutoRefresh() {
        if (this.autoRefreshValue > 0) {
            this.refreshInterval = setInterval(() => {
                if (!document.hidden && !this.isLoading) {
                    this.loadData()
                }
            }, this.autoRefreshValue * 1000)
        }
    }

    pauseAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval)
            this.refreshInterval = null
        }
    }

    resumeAutoRefresh() {
        if (!this.refreshInterval && this.autoRefreshValue > 0) {
            this.startAutoRefresh()
        }
    }
}
```

---

## Support et Ressources

### Documentation Complète
- 📖 **Guide Développeur** : [Architecture avancée](../developer-guide/architecture)
- 🔧 **API Reference** : [Documentation API](../api/overview)
- 💡 **Exemples** : [Cas d'usage avancés](../examples/advanced-customization)

### Communauté et Support
- 🐛 **Issues GitHub** : [Signaler un problème](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
- 💬 **Discussions** : [Forum communautaire](https://github.com/Chancel18/SigmasoftDataTableBundle/discussions)
- 📧 **Support technique** : [support@sigmasoft-solution.com](mailto:support@sigmasoft-solution.com)

---

*Documentation rédigée par [Gédéon MAKELA](mailto:g.makela@sigmasoft-solution.com) - [Sigmasoft Solutions](https://sigmasoft-solution.com)*