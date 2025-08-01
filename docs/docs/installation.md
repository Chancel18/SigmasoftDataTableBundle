---
sidebar_position: 2
---

# Installation

## Prérequis

Avant d'installer SigmasoftDataTableBundle, assurez-vous que votre environnement respecte ces exigences :

### Environnement requis

| Composant | Version minimale | Recommandé |
|-----------|------------------|------------|
| **PHP** | 8.1 | 8.3+ |
| **Symfony** | 6.4 | 7.2+ |
| **Doctrine ORM** | 2.15 | 3.3+ |
| **Composer** | 2.0 | 2.7+ |

### Bundles Symfony requis

Le bundle nécessite ces dépendances Symfony :

```bash
# Déjà inclus dans une installation Symfony standard
symfony/framework-bundle
symfony/twig-bundle
symfony/ux-live-component
symfony/ux-twig-component
doctrine/orm
doctrine/doctrine-bundle
```

## Installation via Composer

### 1. Installation du package

```bash
composer require sigmasoft/datatable-bundle
```

<div className="highlight-box highlight-box--tip">
  <strong>💡 Astuce :</strong> Avec Symfony Flex, le bundle v2.3.0+ est automatiquement configuré via la recipe intégrée !
</div>

### 1.1. Configuration automatique

Le bundle s'installe automatiquement et :
- Configure automatiquement les services
- Crée le fichier `config/packages/sigmasoft_data_table.yaml`
- Copie les templates de base dans `templates/bundles/SigmasoftDataTableBundle/`
- Active l'autoloading PSR-4 optimisé

### 2. Activation du bundle (si nécessaire)

**Note :** Avec la v2.3.0+, cette étape est automatique grâce à la recipe Flex. Si nécessaire, vérifiez dans `config/bundles.php` :

```php title="config/bundles.php"
<?php

return [
    // ... autres bundles
    Sigmasoft\DataTableBundle\SigmasoftDataTableBundle::class => ['all' => true],
];
```

### 3. Mise à jour de l'autoloader

Après l'installation, mettez à jour l'autoloader Composer :

```bash
composer dump-autoload
```

### 4. Vider le cache Symfony

Videz le cache pour charger la nouvelle configuration :

```bash
php bin/console cache:clear
```

### 5. Installation des assets (si nécessaire)

Si vous utilisez Symfony UX, installez les assets :

```bash
php bin/console assets:install
```

## Vérification de l'installation

### 1. Vérifier les commandes disponibles

```bash
php bin/console list sigmasoft
```

Vous devriez voir les commandes suivantes :
- `sigmasoft:datatable:install-config` - Installer/réinstaller la configuration
- Et si le MakerBundle est installé : `make:datatable`

### 2. Vérifier la configuration

```bash
php bin/console config:dump-reference sigmasoft_data_table
```

Cette commande affiche la référence complète de la configuration disponible.

### 3. Test rapide

Créez votre premier DataTable :

```bash
php bin/console make:datatable --help
```

Si la commande s'affiche correctement, l'installation est réussie ! ✅

## Configuration optionnelle

### Configuration globale

Le fichier de configuration `config/packages/sigmasoft_data_table.yaml` est automatiquement créé lors de l'installation. Vous pouvez le personnaliser selon vos besoins :

```yaml title="config/packages/sigmasoft_data_table.yaml"
sigmasoft_data_table:
    defaults:
        items_per_page: 10
        enable_search: true
        enable_pagination: true
        enable_sorting: true
        table_class: 'table table-striped table-hover align-middle'
        date_format: 'd/m/Y'
        pagination_sizes: [5, 10, 25, 50, 100]
        theme: 'bootstrap5'
    
    templates:
        datatable: '@SigmasoftDataTable/datatable.html.twig'
    
    caching:
        enabled: false
        ttl: 3600
    
    maker:
        auto_add_actions: true
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
            show:
                label: 'Voir'
                icon: 'eye'
                class: 'btn btn-sm btn-info'
            edit:
                label: 'Modifier'
                icon: 'pencil'
                class: 'btn btn-sm btn-warning'
            delete:
                label: 'Supprimer'
                icon: 'trash'
                class: 'btn btn-sm btn-danger'
                confirm: true
```

### Assets et Webpack Encore

Si vous utilisez Webpack Encore, aucune configuration supplémentaire n'est nécessaire. Le bundle utilise Bootstrap 5 et les composants UX de Symfony.

Assurez-vous d'avoir Bootstrap dans votre `package.json` :

```json title="package.json"
{
  "dependencies": {
    "bootstrap": "^5.3.0",
    "@symfony/ux-live-component": "^2.23.0"
  }
}
```

## Dépannage installation

### Erreur "Bundle not found"

Si vous obtenez une erreur concernant le bundle non trouvé :

1. Vérifiez que le package est bien installé :
   ```bash
   composer show sigmasoft/datatable-bundle
   ```

2. Vérifiez la configuration dans `config/bundles.php`

3. Videz le cache :
   ```bash
   php bin/console cache:clear
   ```

### Erreur "Component not found"

Si les composants Twig ne sont pas trouvés :

1. Vérifiez la configuration Twig Component
2. Assurez-vous que `symfony/ux-live-component` est installé
3. Redémarrez votre serveur de développement

### MakerBundle non disponible

Si la commande `make:datatable` n'apparaît pas :

1. Installez MakerBundle (uniquement en dev) :
   ```bash
   composer require --dev symfony/maker-bundle
   ```

2. Le bundle détecte automatiquement MakerBundle et active la commande

## Mise à jour

Pour mettre à jour vers la dernière version :

```bash
composer update sigmasoft/datatable-bundle
```

<div className="highlight-box highlight-box--warning">
  <strong>⚠️ Attention :</strong> Consultez le <a href="https://github.com/Chancel18/SigmasoftDataTableBundle/blob/master/CHANGELOG.md">CHANGELOG</a> avant de mettre à jour pour connaître les éventuels breaking changes.
</div>

## Prochaine étape

Maintenant que le bundle est installé, passez au [🚀 Démarrage rapide](./quick-start) pour créer votre premier DataTable !

---

## Support

En cas de problème d'installation :

- **Documentation** : [Troubleshooting](./troubleshooting/common-issues)
- **Issues GitHub** : [Signaler un problème](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
- **Support technique** : [support@sigmasoft-solution.com](mailto:support@sigmasoft-solution.com)
- **Discussions** : [GitHub Discussions](https://github.com/Chancel18/SigmasoftDataTableBundle/discussions)

---

*Bundle développé par [Gédéon MAKELA](mailto:g.makela@sigmasoft-solution.com) - [Sigmasoft Solutions](https://sigmasoft-solution.com)*