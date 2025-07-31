---
sidebar_position: 4
---

# Migration vers v2.3.0

Ce guide vous aide à migrer depuis les versions précédentes vers la v2.3.0 qui apporte des améliorations majeures de structure et compatibilité.

## 🚀 Nouveautés v2.3.0

### Structure PSR-4 Complète
- **Avant** : `src/SigmasoftDataTableBundle/`
- **Après** : `src/` (standard PSR-4)

### Autoloading Optimisé
```json
{
  "autoload": {
    "psr-4": {
      "Sigmasoft\\DataTableBundle\\": "src/"
    }
  }
}
```

### Symfony Flex Compatible
Le bundle inclut maintenant une recipe automatique pour configuration :
- Configuration automatique dans `config/packages/sigmasoft_data_table.yaml`
- Services auto-découverts et optimisés

## 📋 Migration depuis v2.2.x

### 1. Mise à jour Composer

```bash
composer update sigmasoft/datatable-bundle
```

### 2. Aucune modification de code nécessaire

✅ **Bonne nouvelle !** Les namespaces publics restent identiques :
- `Sigmasoft\DataTableBundle\*` (inchangé)
- Vos contrôleurs et templates fonctionnent sans modification

### 3. Configuration automatique

Avec Symfony Flex, la configuration est maintenant automatique :

```yaml
# config/packages/sigmasoft_data_table.yaml (créé automatiquement)
sigmasoft_data_table:
    defaults:
        items_per_page: 10
        enable_search: true
        table_class: 'table table-striped table-hover'
```

### 4. Services auto-découverts

Plus besoin de configuration manuelle des services :
```yaml
# config/services.yaml - AVANT (à supprimer si présent)
Sigmasoft\DataTableBundle\:
    resource: '../vendor/sigmasoft/datatable-bundle/src/*'
    exclude: '../vendor/sigmasoft/datatable-bundle/src/{Entity,Migrations,Tests}'

# APRÈS - Plus rien à faire, tout est automatique !
```

## 🔧 Améliorations Techniques

### DependencyInjection Optimisée
- Services auto-découverts avec pattern exclusion intelligent
- Compiler passes optimisés pour performances
- Résolution de dépendances améliorée

### Structure Bundle Moderne
```
vendor/sigmasoft/datatable-bundle/
├── src/                     # Code source principal (PSR-4)
├── config/                  # Configuration Symfony
├── recipe/                  # Recipe Symfony Flex
├── tests/                   # Tests unitaires
└── docs/                    # Documentation
```

### Compatibilité Étendue
- **Symfony 6.4+** et **7.0+** entièrement supportés
- **PHP 8.1+** à **8.3** testés et validés
- **Doctrine ORM 2.15+** et **3.0+** compatibles

## ✅ Vérification Migration

### Test de Fonctionnement
```bash
# 1. Vider le cache
php bin/console cache:clear

# 2. Vérifier les services
php bin/console debug:container sigmasoft

# 3. Tester la génération
php bin/console make:datatable TestEntity
```

### Exemple Complet
```php
// Votre code existant continue de fonctionner
use Sigmasoft\DataTableBundle\Builder\DataTableBuilderInterface;

class UserController extends AbstractController
{
    public function index(DataTableBuilderInterface $builder): Response
    {
        $config = $builder
            ->createDataTable(User::class)
            ->addTextColumn('name', 'name', 'Nom')
            ->addDateColumn('createdAt', 'createdAt', 'Créé le')
            ->configureSearch(true, ['name', 'email'])
            ->configurePagination(true, 10);

        return $this->render('user/index.html.twig', [
            'datatableConfig' => $config,
        ]);
    }
}
```

## 🐛 Problèmes Connus

### Cache Bundle
Si vous rencontrez des erreurs de services après migration :
```bash
php bin/console cache:clear --env=prod
php bin/console cache:clear --env=dev
```

### Composer Autoload
En cas de problème d'autoloading :
```bash
composer dump-autoload --optimize
```

## 📞 Support

En cas de problème lors de la migration :
- **GitHub Issues** : [Signaler un problème](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
- **Email** : [g.makela@sigmasoft-solution.com](mailto:g.makela@sigmasoft-solution.com)

---

## 🎯 Prochaines Étapes

Après migration réussie :
1. [Configuration avancée](./configuration) - Personnaliser le bundle
2. [Édition inline](./user-guide/inline-editing) - Fonctionnalités avancées
3. [Exemples pratiques](./examples/basic-example) - Cas d'usage réels