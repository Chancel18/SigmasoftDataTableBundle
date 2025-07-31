---
sidebar_position: 3
---

# Types de Colonnes

Le **SigmasoftDataTableBundle** offre une gamme complète de types de colonnes pour afficher et manipuler vos données efficacement.

## 📋 Vue d'Ensemble

### Types de Colonnes Disponibles

| Type | Description | Éditable | Cas d'Usage |
|------|-------------|----------|-------------|
| **TextColumn** | Texte simple avec options de formatage | ❌ | Noms, descriptions, codes |
| **DateColumn** | Dates avec formatage personnalisé | ❌ | Dates de création, échéances |
| **BadgeColumn** | Badges colorés pour statuts | ❌ | Statuts, catégories, tags |
| **ActionColumn** | Boutons d'actions (éditer, supprimer...) | ❌ | Actions CRUD |
| **EditableColumnV2** | Colonnes éditables inline | ✅ | Toute donnée modifiable |

---

## 🔤 TextColumn

Affiche du texte simple avec options de formatage avancées.

### Exemple de Base

```php
<?php

use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Column\TextColumn;

class ProductController extends AbstractController
{
    public function index(DataTableBuilder $builder): Response
    {
        $config = $builder
            ->createDataTable(Product::class)
            ->addColumn(new TextColumn('name', 'name', 'Nom du Produit'))
            ->addColumn(new TextColumn('description', 'description', 'Description'));

        return $this->render('product/index.html.twig', [
            'datatableConfig' => $config,
        ]);
    }
}
```

### Options Avancées

```php
// Texte avec troncature
->addColumn(new TextColumn('description', 'description', 'Description', true, true, [
    'truncate' => true,
    'truncate_length' => 100,
    'empty_value' => '<em>Aucune description</em>',
    'escape' => false // Permet le HTML si nécessaire
]))

// Texte avec formatage personnalisé
->addColumn(new TextColumn('code', 'code', 'Code Produit', true, true, [
    'prefix' => 'PROD-',
    'suffix' => '-2024',
    'transform' => 'uppercase'
]))
```

### Cas d'Usage Pratiques

```php
// Exemple complet avec plusieurs TextColumns
$config = $builder
    ->createDataTable(User::class)
    // Nom complet (non tronqué)
    ->addColumn(new TextColumn('fullName', 'fullName', 'Nom Complet'))
    
    // Bio avec troncature
    ->addColumn(new TextColumn('bio', 'bio', 'Biographie', true, true, [
        'truncate' => true,
        'truncate_length' => 80,
        'empty_value' => '<em class="text-muted">Pas de bio</em>',
        'escape' => false
    ]))
    
    // Code utilisateur (formaté)
    ->addColumn(new TextColumn('userCode', 'id', 'Code', false, false, [
        'prefix' => 'USR-',
        'format' => fn($value) => str_pad($value, 6, '0', STR_PAD_LEFT)
    ]));
```

---

## 📅 DateColumn

Affiche des dates avec formatage flexible et support international.

### Exemple de Base

```php
use Sigmasoft\DataTableBundle\Column\DateColumn;

// Date simple
->addColumn(new DateColumn('createdAt', 'createdAt', 'Créé le'))

// Date avec format personnalisé
->addColumn(new DateColumn('updatedAt', 'updatedAt', 'Modifié le', true, false, [
    'format' => 'd/m/Y H:i'
]))
```

### Formats de Date Supportés

```php
// Formats courts
->addColumn(new DateColumn('birthDate', 'birthDate', 'Naissance', true, true, [
    'format' => 'd/m/Y'  // 25/12/2024
]))

// Formats longs avec heure
->addColumn(new DateColumn('lastLogin', 'lastLogin', 'Dernière Connexion', true, false, [
    'format' => 'd/m/Y H:i:s'  // 25/12/2024 14:30:25
]))

// Format relatif (nécessite une extension)
->addColumn(new DateColumn('createdAt', 'createdAt', 'Créé', true, false, [
    'format' => 'relative',  // "Il y a 2 heures"
    'empty_value' => 'Jamais'
]))

// Format international
->addColumn(new DateColumn('eventDate', 'eventDate', 'Date Événement', true, true, [
    'format' => 'Y-m-d',  // 2024-12-25
    'locale' => 'fr_FR'
]))
```

### Exemple Complet avec Gestion des Null

```php
$config = $builder
    ->createDataTable(Order::class)
    ->addColumn(new DateColumn('orderDate', 'orderDate', 'Date Commande', true, true, [
        'format' => 'd/m/Y H:i',
        'empty_value' => '<span class="text-muted">Non définie</span>',
        'escape' => false
    ]))
    ->addColumn(new DateColumn('deliveryDate', 'deliveryDate', 'Livraison', true, false, [
        'format' => 'd/m/Y',
        'empty_value' => '<span class="badge bg-warning">En attente</span>',
        'escape' => false
    ]))
    ->addColumn(new DateColumn('cancelledAt', 'cancelledAt', 'Annulée le', false, false, [
        'format' => 'd/m/Y H:i',
        'empty_value' => ''
    ]));
```

---

## 🏷️ BadgeColumn

Affiche des badges colorés pour représenter des statuts, catégories ou étiquettes.

### Exemple de Base

```php
use Sigmasoft\DataTableBundle\Column\BadgeColumn;

// Badge simple
->addColumn(new BadgeColumn('status', 'status', 'Statut'))

// Badge avec mapping de valeurs
->addColumn(new BadgeColumn('status', 'status', 'Statut', false, false, [
    'value_mapping' => [
        'active' => 'Actif',
        'inactive' => 'Inactif',
        'pending' => 'En attente'
    ],
    'badge_class' => 'bg-success'
]))
```

### Badges avec Classes CSS Conditionnelles

```php
// Badge avec classes dynamiques basées sur la valeur
->addColumn(new BadgeColumn('priority', 'priority', 'Priorité', false, false, [
    'value_mapping' => [
        'high' => 'Haute',
        'medium' => 'Moyenne', 
        'low' => 'Basse'
    ],
    'class_mapping' => [
        'high' => 'bg-danger',
        'medium' => 'bg-warning',
        'low' => 'bg-success'
    ]
]))

// Statut utilisateur avec badges colorés
->addColumn(new BadgeColumn('role', 'roles', 'Rôle', false, false, [
    'value_mapping' => [
        'ROLE_ADMIN' => 'Administrateur',
        'ROLE_USER' => 'Utilisateur',
        'ROLE_MODERATOR' => 'Modérateur'
    ],
    'class_mapping' => [
        'ROLE_ADMIN' => 'bg-danger text-white',
        'ROLE_USER' => 'bg-primary text-white',
        'ROLE_MODERATOR' => 'bg-warning text-dark'
    ],
    'multiple' => true // Pour les tableaux de rôles
]))
```

### Badges Multiples et Collectionsn

```php
// Pour afficher plusieurs badges (ex: tags)
->addColumn(new BadgeColumn('tags', 'tags', 'Tags', false, true, [
    'multiple' => true,
    'badge_class' => 'bg-info text-white',
    'separator' => ' ',
    'max_items' => 3,
    'show_count' => true // Affiche "+2 autres" si plus d'items
]))

// Exemple avec une collection Doctrine
->addColumn(new BadgeColumn('categories', 'categories', 'Catégories', false, false, [
    'multiple' => true,
    'property_path' => 'name', // Propriété à afficher de chaque objet
    'badge_class' => 'bg-secondary',
    'empty_value' => '<span class="text-muted">Aucune catégorie</span>',
    'escape' => false
]))
```

### Exemple Complet E-commerce

```php
$config = $builder
    ->createDataTable(Product::class)
    // Statut produit
    ->addColumn(new BadgeColumn('status', 'status', 'Statut', false, false, [
        'value_mapping' => [
            'in_stock' => 'En stock',
            'out_of_stock' => 'Rupture',
            'discontinued' => 'Arrêté'
        ],
        'class_mapping' => [
            'in_stock' => 'bg-success',
            'out_of_stock' => 'bg-danger',
            'discontinued' => 'bg-secondary'
        ]
    ]))
    
    // Catégories multiples
    ->addColumn(new BadgeColumn('categories', 'categories', 'Catégories', false, true, [
        'multiple' => true,
        'property_path' => 'name',
        'badge_class' => 'bg-info text-white',
        'max_items' => 2,
        'show_count' => true
    ]))
    
    // Niveau de prix
    ->addColumn(new BadgeColumn('priceLevel', 'price', 'Gamme', false, false, [
        'value_callback' => function($price) {
            if ($price < 50) return 'budget';
            if ($price < 200) return 'mid';
            return 'premium';
        },
        'value_mapping' => [
            'budget' => 'Économique',
            'mid' => 'Milieu de gamme',
            'premium' => 'Premium'
        ],
        'class_mapping' => [
            'budget' => 'bg-success',
            'mid' => 'bg-warning text-dark',
            'premium' => 'bg-dark text-white'
        ]
    ]));
```

---

## ⚡ ActionColumn

Crée des boutons d'actions pour chaque ligne (éditer, supprimer, voir...).

### Exemple de Base

```php
use Sigmasoft\DataTableBundle\Column\ActionColumn;

// Actions simples
->addColumn(new ActionColumn($this->urlGenerator, 'actions', 'Actions', [
    'edit' => [
        'route' => 'product_edit',
        'icon' => 'bi bi-pencil',
        'title' => 'Modifier',
        'class' => 'btn btn-sm btn-primary'
    ],
    'delete' => [
        'route' => 'product_delete',
        'icon' => 'bi bi-trash',
        'title' => 'Supprimer',
        'class' => 'btn btn-sm btn-danger',
        'confirm' => 'Êtes-vous sûr de vouloir supprimer ce produit ?'
    ]
]))
```

### Actions Avancées avec Conditions

```php
->addColumn(new ActionColumn($this->urlGenerator, 'actions', 'Actions', [
    'view' => [
        'route' => 'product_show',
        'route_params' => ['id' => 'getId', 'slug' => 'getSlug'], // Paramètres multiples
        'icon' => 'bi bi-eye',
        'title' => 'Voir',
        'class' => 'btn btn-sm btn-outline-info',
        'target' => '_blank' // Ouvre dans un nouvel onglet
    ],
    
    'edit' => [
        'route' => 'product_edit',
        'icon' => 'bi bi-pencil',
        'title' => 'Modifier',
        'class' => 'btn btn-sm btn-primary',
        'condition' => function(Product $product) {
            return $product->isEditable(); // Condition d'affichage
        }
    ],
    
    'duplicate' => [
        'route' => 'product_duplicate',
        'icon' => 'bi bi-files',
        'title' => 'Dupliquer',
        'class' => 'btn btn-sm btn-secondary',
        'method' => 'POST', // Méthode HTTP
        'csrf_token' => true // Ajoute un token CSRF
    ],
    
    'archive' => [
        'route' => 'product_archive',
        'icon' => 'bi bi-archive',
        'title' => 'Archiver',
        'class' => 'btn btn-sm btn-warning',
        'confirm' => 'Archiver ce produit ?',
        'condition' => fn(Product $p) => !$p->isArchived()
    ]
]))
```

### Actions avec Modal et AJAX

```php
->addColumn(new ActionColumn($this->urlGenerator, 'actions', 'Actions', [
    'quick_edit' => [
        'route' => 'product_quick_edit',
        'icon' => 'bi bi-lightning',
        'title' => 'Édition Rapide',
        'class' => 'btn btn-sm btn-success',
        'modal' => true, // Ouvre dans une modal
        'modal_size' => 'lg',
        'ajax' => true // Charge le contenu en AJAX
    ],
    
    'toggle_status' => [
        'route' => 'product_toggle_status',
        'icon' => function(Product $product) {
            return $product->isActive() ? 'bi bi-toggle-on' : 'bi bi-toggle-off';
        },
        'title' => function(Product $product) {
            return $product->isActive() ? 'Désactiver' : 'Activer';
        },
        'class' => function(Product $product) {
            return $product->isActive() 
                ? 'btn btn-sm btn-success' 
                : 'btn btn-sm btn-outline-secondary';
        },
        'ajax' => true,
        'reload_datatable' => true // Recharge la datatable après l'action
    ]
]))
```

### Exemple Complet avec Permissions

```php
// Dans votre contrôleur
class ProductController extends AbstractController
{
    public function index(DataTableBuilder $builder): Response
    {
        $user = $this->getUser();
        
        $actions = [];
        
        // Action toujours disponible
        $actions['view'] = [
            'route' => 'product_show',
            'icon' => 'bi bi-eye',
            'class' => 'btn btn-sm btn-outline-primary',
            'title' => 'Détails'
        ];
        
        // Actions selon les permissions
        if ($this->isGranted('ROLE_EDITOR')) {
            $actions['edit'] = [
                'route' => 'product_edit',
                'icon' => 'bi bi-pencil',
                'class' => 'btn btn-sm btn-primary',
                'title' => 'Modifier'
            ];
        }
        
        if ($this->isGranted('ROLE_ADMIN')) {
            $actions['delete'] = [
                'route' => 'product_delete',
                'icon' => 'bi bi-trash',
                'class' => 'btn btn-sm btn-danger',
                'title' => 'Supprimer',
                'confirm' => 'Confirmer la suppression ?',
                'condition' => fn(Product $p) => $p->canBeDeleted()
            ];
        }
        
        $config = $builder
            ->createDataTable(Product::class)
            ->addTextColumn('name', 'name', 'Nom')
            ->addDateColumn('createdAt', 'createdAt', 'Créé le')
            ->addColumn(new ActionColumn($this->urlGenerator, 'actions', 'Actions', $actions));
            
        return $this->render('product/index.html.twig', [
            'datatableConfig' => $config
        ]);
    }
}
```

---

## ✏️ EditableColumnV2

Colonnes modifiables directement dans le tableau avec validation en temps réel.

### Types de Champs Éditables

#### Champ Texte

```php
use Sigmasoft\DataTableBundle\Service\EditableColumnFactory;

class ProductController extends AbstractController
{
    public function __construct(
        private EditableColumnFactory $editableColumnFactory
    ) {}

    public function index(): Response
    {
        $config = $this->dataTableBuilder
            ->createDataTable(Product::class)
            
            // Champ texte simple
            ->addColumn(
                $this->editableColumnFactory->text('name', 'name', 'Nom du Produit')
            )
            
            // Champ texte avec validation
            ->addColumn(
                $this->editableColumnFactory->text('code', 'code', 'Code Produit')
                    ->required(true)
                    ->minLength(3)
                    ->maxLength(20)
                    ->pattern('[A-Z0-9-]+')
                    ->placeholder('EX: PROD-123')
            );
    }
}
```

#### Champ Email

```php
// Email avec validation
->addColumn(
    $this->editableColumnFactory->email('contactEmail', 'contactEmail', 'Email Contact')
        ->required(true)
        ->placeholder('exemple@domaine.com')
)
```

#### Champ Numérique

```php
// Prix avec contraintes
->addColumn(
    $this->editableColumnFactory->number('price', 'price', 'Prix')
        ->required(true)
        ->min(0)
        ->max(9999.99)
        ->step(0.01)
        ->placeholder('0.00')
        ->suffix('€')
)

// Quantité entière
->addColumn(
    $this->editableColumnFactory->number('quantity', 'quantity', 'Quantité')
        ->required(true) 
        ->min(0)
        ->step(1)
        ->placeholder('0')
)
```

#### Liste Déroulante (Select)

```php
// Select simple
->addColumn(
    $this->editableColumnFactory->select('status', 'status', 'Statut', [
        'active' => 'Actif',
        'inactive' => 'Inactif', 
        'pending' => 'En attente'
    ])->required(true)
)

// Select avec options depuis la base de données
->addColumn(
    $this->editableColumnFactory->select('categoryId', 'category.id', 'Catégorie', 
        $this->getCategoryOptions() // Méthode qui retourne un tableau [id => nom]
    )->required(true)
)

private function getCategoryOptions(): array
{
    return $this->categoryRepository
        ->createQueryBuilder('c')
        ->select('c.id', 'c.name')
        ->getQuery()
        ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
}
```

#### Zone de Texte (Textarea)

```php
// Description longue
->addColumn(
    $this->editableColumnFactory->textarea('description', 'description', 'Description')
        ->maxLength(500)
        ->rows(4)
        ->placeholder('Décrivez le produit...')
)
```

#### Sélecteur de Couleur

```php
// Couleur avec préréglages
->addColumn(
    $this->editableColumnFactory->color('color', 'color', 'Couleur')
        ->required(true)
        ->showPresets(true) // Affiche des couleurs prédéfinies
        ->defaultValue('#3498db')
)
```

### Exemple Complet avec Validation

```php
class UserController extends AbstractController
{
    public function __construct(
        private DataTableBuilder $dataTableBuilder,
        private EditableColumnFactory $editableColumnFactory
    ) {}

    public function index(): Response
    {
        $config = $this->dataTableBuilder
            ->createDataTable(User::class)
            
            // Informations personnelles
            ->addColumn(
                $this->editableColumnFactory->text('firstName', 'firstName', 'Prénom')
                    ->required(true)
                    ->minLength(2)
                    ->maxLength(50)
                    ->placeholder('Prénom')
            )
            
            ->addColumn(
                $this->editableColumnFactory->text('lastName', 'lastName', 'Nom')
                    ->required(true)
                    ->minLength(2)
                    ->maxLength(50)
                    ->placeholder('Nom de famille')
            )
            
            ->addColumn(
                $this->editableColumnFactory->email('email', 'email', 'Email')
                    ->required(true)
                    ->placeholder('utilisateur@exemple.com')
            )
            
            // Informations professionnelles
            ->addColumn(
                $this->editableColumnFactory->select('department', 'department', 'Département', [
                    'IT' => 'Informatique',
                    'HR' => 'Ressources Humaines',
                    'SALES' => 'Commercial',
                    'MARKETING' => 'Marketing'
                ])->required(true)
            )
            
            ->addColumn(
                $this->editableColumnFactory->number('salary', 'salary', 'Salaire')
                    ->min(0)
                    ->max(200000)
                    ->step(100)
                    ->prefix('€')
                    ->placeholder('0')
            )
            
            // Notes
            ->addColumn(
                $this->editableColumnFactory->textarea('notes', 'notes', 'Notes')
                    ->maxLength(1000)
                    ->rows(3)
                    ->placeholder('Notes additionnelles...')
            )
            
            // Colonnes non-éditables
            ->addDateColumn('createdAt', 'createdAt', 'Créé le')
            ->addDateColumn('updatedAt', 'updatedAt', 'Modifié le');

        return $this->render('user/index.html.twig', [
            'datatableConfig' => $config,
        ]);
    }
}
```

---

## 🔧 Configuration Avancée

### Options Globales pour Tous les Types

```php
// Configuration au niveau du DataTable
$config = $builder
    ->createDataTable(Product::class)
    ->configureOptions([
        'table_class' => 'table table-striped table-hover',
        'empty_value_global' => '<em class="text-muted">Non défini</em>',
        'escape_html' => true,
        'date_format' => 'd/m/Y H:i'
    ]);
```

### Callbacks Personnalisés

```php
// Formatage personnalisé avec callback
->addColumn(new TextColumn('price', 'price', 'Prix', true, false, [
    'callback' => function($value, $entity) {
        return number_format($value, 2, ',', ' ') . ' €';
    }
]))

// Condition d'affichage avec callback  
->addColumn(new ActionColumn($this->urlGenerator, 'actions', 'Actions', [
    'edit' => [
        'route' => 'product_edit',
        'condition' => function(Product $product, User $currentUser) {
            return $currentUser->canEdit($product);
        }
    ]
]))
```

### Stylisation CSS Personnalisée

```php
// Classes CSS conditionnelles
->addColumn(new TextColumn('stock', 'stock', 'Stock', true, false, [
    'cell_class' => function($value, $entity) {
        if ($value <= 0) return 'text-danger fw-bold';
        if ($value <= 10) return 'text-warning';
        return 'text-success';
    }
]))
```

---

## 📚 Ressources Complémentaires

- [Configuration YAML](./configuration.md) - Configuration complète du bundle
- [Édition Inline](./inline-editing.md) - Guide détaillé de l'édition inline
- [Renderers Personnalisés](../developer-guide/custom-renderers.md) - Créer ses propres types de colonnes

---

*Documentation mise à jour pour SigmasoftDataTableBundle v2.3.5*