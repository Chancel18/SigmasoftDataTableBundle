# 📖 Guide d'Utilisation - SigmasoftDataTableBundle

## 🚀 NOUVEAU : Génération Automatique avec Maker

### 🛠️ Commande `make:datatable` - Génération Ultra-Rapide

La nouvelle commande Maker permet de générer automatiquement un DataTable complet en analysant vos entités Doctrine.

#### Génération Basique
```bash
# Génération automatique complète
php bin/console make:datatable User

# Avec contrôleur CRUD
php bin/console make:datatable User --controller

# Avec toutes les fonctionnalités
php bin/console make:datatable User --controller --with-actions --with-export --with-bulk
```

#### Résultat Automatique
- ✅ **Configuration YAML** générée avec auto-détection des champs
- ✅ **Template Twig** avec composant intégré UNE SEULE LIGNE
- ✅ **Contrôleur CRUD** avec toutes les actions (optionnel)
- ✅ **Routes** configurées automatiquement

#### Auto-détection Intelligente
La commande analyse automatiquement votre entité Doctrine et :
- 🔍 **Détecte les types** de champs (string, integer, boolean, date, etc.)
- 🏷️ **Génère les labels** (camelCase → "Title Case")
- 🔍 **Configure la recherche** (champs string/text automatiquement recherchables)
- ⬆️⬇️ **Configure le tri** (tous les champs sauf text/json/blob)
- 🔗 **Gère les relations** (ManyToOne/OneToOne avec champ d'affichage)
- 🎨 **Applique les formats** (email, url, image détectés par nom)

### Exemple de Génération Complète

```bash
# Commande
php bin/console make:datatable Product --controller --with-actions --with-export

# Fichiers générés automatiquement :
# ✅ config/packages/sigmasoft_data_table.yaml (configuration)
# ✅ templates/product/index.html.twig (template avec UNE ligne)
# ✅ src/Controller/ProductController.php (contrôleur CRUD complet)
```

**Template généré :**
```twig
{# templates/product/index.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
    <h1>🛍️ Gestion des Produits</h1>
    
    {# 🚀 UNE SEULE LIGNE POUR TOUT LE TABLEAU ! #}
    <twig:SigmasoftDataTableComponent entityClass="App\\Entity\\Product" />
{% endblock %}
```

**Résultat immédiat :** Interface Bootstrap professionnelle complète !

## 🎨 Utilisation Simple du Composant Twig

Le composant `SigmasoftDataTableComponent` génère automatiquement un tableau Bootstrap complet sans avoir besoin de coder manuellement l'HTML.

### ✅ Configuration YAML

```yaml
# config/packages/sigmasoft_data_table.yaml
sigmasoft_data_table:
    global_config:
        items_per_page: 25
        enable_search: true
        enable_sort: true
        
    entities:
        'App\Entity\User':
            label: 'Gestion des Utilisateurs'
            fields:
                id: 
                    type: 'integer'
                    label: 'ID'
                    sortable: true
                    searchable: false
                    width: '80px'
                name: 
                    type: 'string'
                    label: 'Nom complet'
                    searchable: true
                    sortable: true
                    maxLength: 30
                email: 
                    type: 'email'
                    label: 'Email'
                    searchable: true
                    sortable: true
                status: 
                    type: 'badge'
                    label: 'Statut'
                    badgeColor: 'success'
                    sortable: true
                createdAt: 
                    type: 'datetime'
                    label: 'Créé le'
                    format: 'd/m/Y H:i'
                    sortable: true
                    searchable: false
                avatar:
                    type: 'image'
                    label: 'Avatar'
                    width: '60px'
                isActive:
                    type: 'boolean'
                    label: 'Actif'
                    
            # Actions individuelles
            actions:
                view:
                    label: 'Voir'
                    icon: 'eye'
                    variant: 'info'
                    route: 'user_show'
                edit:
                    label: 'Modifier'
                    icon: 'edit'
                    variant: 'primary'
                    route: 'user_edit'
                delete:
                    label: 'Supprimer'
                    icon: 'trash'
                    variant: 'danger'
                    confirm: true
                    
            # Actions groupées
            bulk_actions:
                activate:
                    label: 'Activer'
                    icon: 'check'
                    variant: 'success'
                    confirm: true
                    confirmMessage: 'Activer les utilisateurs sélectionnés ?'
                delete:
                    label: 'Supprimer'
                    icon: 'trash'
                    variant: 'danger'
                    confirm: true
                    
            # Export
            enable_export: true
            export:
                formats: ['csv', 'xlsx', 'pdf']
                
            # Temps réel (optionnel)
            realtime:
                enabled: true
                auto_refresh: false
                refresh_interval: 30000
                turbo_streams: true
                mercure: false
                
            # Styles personnalisés
            css_classes:
                table: ' table-striped'
                row: 'user-row'
                
        'App\Entity\Product':
            label: 'Catalogue Produits'
            fields:
                id: { type: 'integer', label: 'ID', sortable: true, width: '80px' }
                name: { type: 'string', label: 'Nom du produit', searchable: true, sortable: true }
                price: { type: 'currency', label: 'Prix', sortable: true }
                category.name: { type: 'string', label: 'Catégorie', searchable: true }
                description: { type: 'string', label: 'Description', maxLength: 50 }
                inStock: { type: 'boolean', label: 'En stock' }
                createdAt: { type: 'date', label: 'Créé le', format: 'd/m/Y' }
```

### 🎨 Template Twig - Usage Ultra Simple

```twig
{# templates/user/index.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}Gestion des Utilisateurs{% endblock %}

{% block body %}
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1>👥 Gestion des Utilisateurs</h1>
                
                {# 🚀 UNE SEULE LIGNE POUR TOUT LE TABLEAU ! #}
                <twig:SigmasoftDataTableComponent entityClass="App\\Entity\\User" />
                
                {# Optionnel: avec configuration override #}
                {# <twig:SigmasoftDataTableComponent 
                     entityClass="App\\Entity\\User" 
                     :overrideConfig="{ 'items_per_page': 50 }" /> #}
            </div>
        </div>
    </div>
{% endblock %}
```

### 🎛️ Contrôleur - Configuration Minimale

```php
<?php
// src/Controller/UserController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_index')]
    public function index(): Response
    {
        // 🎉 AUCUN CODE NÉCESSAIRE !
        // Le composant gère tout automatiquement
        return $this->render('user/index.html.twig');
    }
    
    // Actions pour edit/delete si nécessaires
    #[Route('/users/{id}/edit', name: 'user_edit')]
    public function edit(int $id): Response
    {
        // Logique d'édition
        return $this->render('user/edit.html.twig');
    }
    
    #[Route('/users/{id}', name: 'user_show')]
    public function show(int $id): Response
    {
        // Logique d'affichage
        return $this->render('user/show.html.twig');
    }
}
```

## 🎯 Fonctionnalités Générées Automatiquement

### ✨ Interface Bootstrap Complète
- ✅ **Tableau responsive** avec classes Bootstrap
- ✅ **Pagination** avec navigation complète
- ✅ **Recherche en temps réel** avec barre de recherche
- ✅ **Tri des colonnes** avec indicateurs visuels
- ✅ **Actions individuelles** (Voir, Modifier, Supprimer)
- ✅ **Actions groupées** avec sélection multiple
- ✅ **Export** (CSV, Excel, PDF)
- ✅ **Messages d'alerte** contextuels
- ✅ **Gestion d'erreurs** avec notifications

### 🎨 Types de Champs Supportés

| Type | Rendu | Exemple |
|------|-------|---------|
| `string` | Texte simple | "John Doe" |
| `integer` | Nombre | 42 |
| `boolean` | Badge Oui/Non | <span class="badge bg-success">Oui</span> |
| `date` | Date formatée | 15/03/2024 |
| `datetime` | Date et heure | 15/03/2024 14:30 |
| `email` | Lien mailto | [📧 john@example.com] |
| `url` | Lien externe | [🔗 Lien] |
| `currency` | Montant formaté | 1 234,56 € |
| `image` | Miniature | ![Image](thumb.jpg) |
| `badge` | Badge coloré | <span class="badge bg-primary">Actif</span> |

### 🔄 Interactions en Temps Réel

- **🔍 Recherche instantanée** - Pas de rechargement de page
- **📄 Pagination fluide** - Navigation sans rechargement
- **🔀 Tri dynamique** - Tri instantané des colonnes
- **🗑️ Suppression immédiate** - Confirmation et suppression
- **✅ Actions groupées** - Opérations sur plusieurs éléments
- **📤 Export asynchrone** - Téléchargement en arrière-plan

## 🛠️ Personnalisation Avancée

### 🎨 CSS Personnalisé

```yaml
entities:
    'App\Entity\User':
        css_classes:
            table: ' table-striped table-hover'
            row: 'user-row clickable'
        fields:
            name:
                cssClass: 'fw-bold text-primary'
            status:
                cssClass: 'text-center'
```

### 🔧 Actions Personnalisées

```yaml
actions:
    custom_action:
        label: 'Action Spéciale'
        icon: 'star'
        variant: 'warning'
        action: 'customAction'  # Méthode du composant
        
bulk_actions:
    export_selected:
        label: 'Exporter Sélection'
        icon: 'download'
        variant: 'info'
        confirm: false
```

### ⚡ Temps Réel avec Mercure

```yaml
realtime:
    enabled: true
    mercure: true
    topics: 
        - 'user/updates'
        - 'admin/notifications'
    auto_refresh: true
    refresh_interval: 15000
```

## 🎉 Résultat Final

Avec cette configuration, vous obtenez **automatiquement** :

1. **Un tableau Bootstrap professionnel** 
2. **Toutes les interactions utilisateur**
3. **La pagination intelligente**
4. **La recherche en temps réel**
5. **Les actions CRUD complètes**
6. **L'export multi-format**
7. **Les notifications utilisateur**
8. **La compatibilité mobile**

**🚀 Et tout cela avec UNE SEULE LIGNE dans votre template !**

```twig
<twig:SigmasoftDataTableComponent entityClass="App\\Entity\\User" />
```

---

**💡 Plus besoin de créer manuellement des tableaux HTML complexes !**  
**Le composant gère tout automatiquement selon votre configuration.**