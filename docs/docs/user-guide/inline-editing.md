---
sidebar_position: 3
title: Édition Inline
description: Guide complet pour l'édition inline des données dans les DataTables
---

# Édition Inline

L'édition inline permet aux utilisateurs de modifier directement les données dans la table sans avoir besoin de naviguer vers une page d'édition séparée. Cette fonctionnalité améliore considérablement l'expérience utilisateur en rendant les modifications rapides et intuitives.

## Vue d'ensemble

Le SigmasoftDataTableBundle offre deux approches pour l'édition inline :

1. **EditableColumn** : Version basique pour des besoins simples
2. **EditableColumnV2** : Version avancée avec architecture modulaire et support de multiples types de champs

## Utilisation de base avec EditableColumnFactory

### Configuration dans le contrôleur

```php
use Sigmasoft\DataTableBundle\Service\EditableColumnFactory;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_product_list')]
    public function index(
        DataTableBuilder $dataTableBuilder,
        EditableColumnFactory $editableColumnFactory
    ): Response {
        $datatableConfig = $dataTableBuilder
            ->createDataTable(Product::class)
            // Champ texte éditable
            ->addColumn($editableColumnFactory->text('name', 'name', 'Nom du produit'))
            
            // Champ email éditable avec validation
            ->addColumn($editableColumnFactory->email('contact', 'contact', 'Email'))
            
            // Champ numérique éditable
            ->addColumn($editableColumnFactory->number('stock', 'stock', 'Stock'))
            
            // Liste déroulante éditable
            ->addColumn($editableColumnFactory->select('status', 'status', 'Statut', [
                'active' => 'Actif',
                'inactive' => 'Inactif',
                'pending' => 'En attente'
            ]))
            
            // Zone de texte éditable
            ->addColumn($editableColumnFactory->textarea('description', 'description', 'Description', 3))
            
            // Sélecteur de couleur
            ->addColumn($editableColumnFactory->color('color', 'color', 'Couleur', true));

        return $this->render('product/index.html.twig', [
            'datatableConfig' => $datatableConfig
        ]);
    }
}
```

## Types de champs éditables

### 1. Champ Texte
```php
$editableColumnFactory->text('field_name', 'property_path', 'Label')
```

### 2. Champ Email
```php
$editableColumnFactory->email('email', 'email', 'Adresse Email')
```
- Validation automatique du format email
- Clavier email sur mobile

### 3. Champ Numérique
```php
$editableColumnFactory->number('price', 'price', 'Prix')
```
- Validation des nombres
- Support des décimales
- Clavier numérique sur mobile

### 4. Liste Déroulante (Select)
```php
$editableColumnFactory->select('category', 'category', 'Catégorie', [
    'electronics' => 'Électronique',
    'clothing' => 'Vêtements',
    'food' => 'Alimentation'
])
```

### 5. Zone de Texte (Textarea)
```php
$editableColumnFactory->textarea('notes', 'notes', 'Notes', 5) // 5 lignes
```

### 6. Sélecteur de Couleur
```php
$editableColumnFactory->color('theme_color', 'themeColor', 'Couleur du thème', true)
```
- `true` : affiche les couleurs prédéfinies
- `false` : sélecteur de couleur simple

## Configuration avancée avec EditableColumnV2

Pour des besoins plus complexes, utilisez directement `EditableColumnV2` :

```php
use Sigmasoft\DataTableBundle\Column\EditableColumnV2;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

// Configuration personnalisée
$fieldConfig = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_TEXT)
    ->validationRules([
        'required' => true,
        'minLength' => 3,
        'maxLength' => 100,
        'pattern' => '^[A-Za-z0-9\s]+$'
    ])
    ->dataAttributes([
        'placeholder' => 'Entrez le nom du produit',
        'autocomplete' => 'off'
    ])
    ->cssClasses(['form-control-lg', 'text-primary']);

$column = new EditableColumnV2(
    'product_name',
    'name',
    'Nom du Produit',
    $fieldConfig,
    true,  // sortable
    true,  // searchable
    [],    // options
    $rendererRegistry
);
```

## Validation des données

### Validation côté client

La validation côté client est automatique selon le type de champ :

```php
$fieldConfig = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_EMAIL)
    ->validationRules([
        'required' => true,
        'email' => true
    ]);
```

### Validation côté serveur

Le bundle valide automatiquement les données côté serveur en utilisant :
- Les contraintes de validation Symfony définies sur l'entité
- Les métadonnées Doctrine pour la validation des types
- Les règles personnalisées définies dans la configuration

## Gestion des événements

### Événements JavaScript

Le système d'édition inline émet plusieurs événements JavaScript :

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Avant la sauvegarde
    document.addEventListener('datatable:inline-edit:before-save', function(event) {
        console.log('Sauvegarde en cours...', event.detail);
    });
    
    // Après la sauvegarde réussie
    document.addEventListener('datatable:inline-edit:saved', function(event) {
        console.log('Sauvegarde réussie!', event.detail);
    });
    
    // En cas d'erreur
    document.addEventListener('datatable:inline-edit:error', function(event) {
        console.error('Erreur de sauvegarde:', event.detail.error);
    });
});
```

### Callbacks personnalisés

```php
$fieldConfig = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_NUMBER)
    ->onBeforeSave('validateStockLevel')  // Fonction JS
    ->onAfterSave('updateTotalStock');    // Fonction JS
```

## Sécurité

### Contrôle des permissions

```php
use Sigmasoft\DataTableBundle\Service\InlineEditServiceV2;

class ProductController extends AbstractController
{
    public function __construct(
        private InlineEditServiceV2 $inlineEditService
    ) {}
    
    #[Route('/inline-edit/{entity}/{id}/{field}', name: 'app_inline_edit')]
    public function inlineEdit(string $entity, int $id, string $field): JsonResponse
    {
        // Vérification des permissions
        $this->denyAccessUnlessGranted('ROLE_EDITOR');
        
        // Ou vérification personnalisée
        if (!$this->isGranted('EDIT', $entity)) {
            throw $this->createAccessDeniedException();
        }
        
        // Traitement de la modification...
    }
}
```

### Champs protégés

Certains champs peuvent être configurés en lecture seule :

```php
$fieldConfig = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_TEXT)
    ->readOnly(true)
    ->cssClasses(['form-control-plaintext']);
```

## Styles et personnalisation

### CSS personnalisé

```css
/* Styles pour les champs éditables */
.editable-field {
    border: 1px dashed #ccc;
    background-color: #f9f9f9;
    transition: all 0.3s ease;
}

.editable-field:hover {
    background-color: #fff;
    border-color: #007bff;
}

.editable-field:focus {
    background-color: #fff;
    border-style: solid;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

/* Indicateurs de sauvegarde */
.saving-indicator {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
}

/* Styles pour les erreurs */
.editable-field.is-invalid {
    border-color: #dc3545;
    background-color: #f8d7da;
}
```

### Templates personnalisés

Vous pouvez surcharger les templates des renderers :

```twig
{# templates/bundles/SigmasoftDataTableBundle/fields/custom_text.html.twig #}
<div class="custom-editable-wrapper">
    <input type="text" 
           class="form-control custom-editable-field"
           data-entity-id="{{ entity.id }}"
           data-field-name="{{ field_name }}"
           value="{{ value }}"
           {{ attributes|raw }}>
    <span class="field-hint">Cliquez pour éditer</span>
</div>
```

## Performance et optimisation

### Debouncing

Les modifications sont automatiquement "debounced" pour éviter les requêtes excessives :

```javascript
// Configuration par défaut : 1000ms
const DEBOUNCE_DELAY = 1000;
```

### Mise en cache

Les valeurs originales sont mises en cache pour permettre l'annulation :

```javascript
// Annuler les modifications
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const field = e.target;
        if (field.classList.contains('editable-field')) {
            field.value = field.dataset.originalValue;
        }
    }
});
```

## Exemple complet

```php
<?php

namespace App\Controller;

use App\Entity\Product;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Service\EditableColumnFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_product_list')]
    public function index(
        DataTableBuilder $dataTableBuilder,
        EditableColumnFactory $editableColumnFactory
    ): Response {
        $datatableConfig = $dataTableBuilder
            ->createDataTable(Product::class)
            ->addColumn($editableColumnFactory->text('name', 'name', 'Nom'))
            ->addColumn($editableColumnFactory->textarea('description', 'description', 'Description', 3))
            ->addColumn($editableColumnFactory->number('price', 'price', 'Prix'))
            ->addColumn($editableColumnFactory->number('stock', 'stock', 'Stock'))
            ->addColumn($editableColumnFactory->select('category', 'category', 'Catégorie', [
                'electronics' => 'Électronique',
                'clothing' => 'Vêtements',
                'food' => 'Alimentation',
                'other' => 'Autre'
            ]))
            ->addColumn($editableColumnFactory->select('status', 'status', 'Statut', [
                'active' => 'Actif',
                'inactive' => 'Inactif'
            ]))
            ->configureSearch(true, ['name', 'description', 'category'])
            ->configurePagination(true, 10);

        return $this->render('product/index.html.twig', [
            'datatableConfig' => $datatableConfig
        ]);
    }
}
```

## Dépannage

### Les modifications ne sont pas sauvegardées

1. Vérifiez que le service `InlineEditServiceV2` est correctement configuré
2. Assurez-vous que l'entité a les setters appropriés
3. Vérifiez les permissions sur les champs

### Erreur "Field not editable"

Cette erreur survient quand :
- Le champ n'existe pas sur l'entité
- Le setter n'est pas accessible
- Le champ est configuré en lecture seule

### Les validations ne fonctionnent pas

1. Vérifiez les contraintes de validation sur l'entité
2. Assurez-vous que la validation côté client est activée
3. Consultez les logs pour les erreurs de validation côté serveur

## Prochaines étapes

- [Personnalisation avancée](./customization.md)
- [Création de renderers personnalisés](../developer-guide/custom-renderers.md)
- [API Reference](../api/overview.md)