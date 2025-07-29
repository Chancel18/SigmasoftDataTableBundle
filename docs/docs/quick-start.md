---
sidebar_position: 3
---

# Démarrage Rapide

Créons votre premier DataTable en 5 minutes ! 🚀

## Vue d'ensemble

Ce guide vous montre comment créer un DataTable complet à partir d'une entité Doctrine existante.

### Ce que vous allez obtenir

À la fin de ce guide, vous aurez :
- ✅ Un DataTable Bootstrap responsive
- ✅ Recherche en temps réel
- ✅ Tri des colonnes
- ✅ Pagination automatique
- ✅ Actions CRUD (optionnel)

## Prérequis

- Bundle installé (voir [Installation](./installation))
- Une entité Doctrine existante (ex: `User`, `Product`, etc.)
- Serveur de développement Symfony en cours d'exécution

## Étape 1 : Génération automatique

### Commande de base

```bash
php bin/console make:datatable User
```

Cette commande va :
1. 🔍 Analyser votre entité `User`
2. 📝 Générer la configuration YAML
3. 🎨 Créer le template Twig
4. ✅ Configurer tous les champs automatiquement

### Commande avec options avancées

```bash
php bin/console make:datatable User --controller --with-actions --with-export
```

Options disponibles :
- `--controller` : Génère un contrôleur CRUD complet
- `--with-actions` : Ajoute les actions (voir, éditer, supprimer)
- `--with-export` : Active l'export CSV/Excel
- `--with-bulk` : Active les actions groupées

## Étape 2 : Configuration générée

La commande crée automatiquement `config/packages/sigmasoft_data_table.yaml` :

```yaml title="config/packages/sigmasoft_data_table.yaml"
sigmasoft_data_table:
    entities:
        'App\Entity\User':
            label: 'Gestion des Users'
            items_per_page: 25
            enable_search: true
            enable_sort: true
            enable_pagination: true
            fields:
                id:
                    type: integer
                    label: 'ID'
                    sortable: true
                    searchable: false
                name:
                    type: string
                    label: 'Nom'
                    sortable: true
                    searchable: true
                email:
                    type: string
                    label: 'Email'
                    sortable: true
                    searchable: true
                createdAt:
                    type: date
                    label: 'Créé le'
                    sortable: true
                    searchable: false
                    format: 'd/m/Y H:i'
```

## Étape 3 : Template généré

Le template `templates/admin/user/index.html.twig` est créé :

```twig title="templates/admin/user/index.html.twig"
{% extends 'base.html.twig' %}

{% block title %}Gestion des Users{% endblock %}

{% block body %}
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-table me-2"></i>
                        Gestion des Users
                    </h1>
                    
                    <div class="d-flex gap-2">
                        <a href="{{ path('user_new') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Nouveau User
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        {# 🚀 COMPOSANT DATATABLE - UNE SEULE LIGNE ! #}
                        <twig:SigmasoftDataTable entityClass="App\Entity\User" />
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Étape 4 : Contrôleur (optionnel)

Si vous avez utilisé `--controller`, un contrôleur complet est généré :

```php title="src/Controller/UserController.php"
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/user')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'user_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/user/index.html.twig');
    }

    // Autres méthodes CRUD générées...
}
```

## Étape 5 : Test et personnalisation

### Tester votre DataTable

1. Démarrez votre serveur :
   ```bash
   symfony server:start
   ```

2. Visitez votre route (ex: `/admin/user`)

3. Vous devriez voir un DataTable complet ! 🎉

### Personnalisation rapide

#### Modifier les colonnes affichées

```yaml title="config/packages/sigmasoft_data_table.yaml"
sigmasoft_data_table:
    entities:
        'App\Entity\User':
            fields:
                # Masquer une colonne
                id:
                    visible: false
                # Personnaliser une colonne
                name:
                    label: 'Nom Complet'
                    cssClass: 'fw-bold'
                    width: '200px'
```

#### Ajouter des actions personnalisées

```yaml
sigmasoft_data_table:
    entities:
        'App\Entity\User':
            actions:
                view:
                    label: 'Voir'
                    icon: 'fas fa-eye'
                    route: 'user_show'
                    variant: 'info'
                edit:
                    label: 'Modifier'
                    icon: 'fas fa-edit'
                    route: 'user_edit'
                    variant: 'warning'
                delete:
                    label: 'Supprimer'
                    icon: 'fas fa-trash'
                    route: 'user_delete'
                    variant: 'danger'
                    confirm: true
                    confirmMessage: 'Êtes-vous sûr de vouloir supprimer cet utilisateur ?'
```

## Fonctionnalités avancées

### Recherche globale

La recherche est automatiquement configurée sur les champs `searchable: true` :

```yaml
fields:
    name:
        searchable: true  # Inclus dans la recherche globale
    email:
        searchable: true  # Inclus dans la recherche globale
    id:
        searchable: false # Exclu de la recherche
```

### Relations

Le bundle détecte automatiquement les relations :

```yaml
# Pour une relation User -> Profile
fields:
    profile:
        type: relation
        label: 'Profil'
        relation:
            entity: 'App\Entity\Profile'
            field: 'name'  # Champ à afficher
```

### Export de données

Avec `--with-export`, vous obtenez :

```yaml
sigmasoft_data_table:
    entities:
        'App\Entity\User':
            export:
                formats: ['csv', 'excel']
                # Les données exportées respectent les filtres appliqués
```

## Dépannage rapide

### DataTable vide ?

1. Vérifiez que votre entité a des données :
   ```bash
   php bin/console doctrine:query:sql "SELECT COUNT(*) FROM user"
   ```

2. Vérifiez la configuration YAML générée

### Erreur de template ?

1. Vérifiez que le composant Twig est bien configuré
2. Videz le cache :
   ```bash
   php bin/console cache:clear
   ```

### Styles manquants ?

Assurez-vous que Bootstrap 5 est inclus dans votre projet :

```html title="templates/base.html.twig"
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
```

## Prochaines étapes

Maintenant que votre premier DataTable fonctionne :

1. 📖 Consultez le [Guide utilisateur](./user-guide/basic-usage) pour plus de fonctionnalités
2. 🎨 Découvrez la [Personnalisation](./user-guide/customization) avancée
3. 🚀 Explorez les [Live Components](./user-guide/live-components) temps réel

## Aide et support

- 📖 **Documentation complète** : [Guide utilisateur](./user-guide/basic-usage)
- 🐛 **Problèmes** : [Issues GitHub](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
- 💬 **Support technique** : [support@sigmasoft-solution.com](mailto:support@sigmasoft-solution.com)

---

*Guide créé par [Gédéon MAKELA](mailto:g.makela@sigmasoft-solution.com) - [Sigmasoft Solutions](https://sigmasoft-solution.com)*