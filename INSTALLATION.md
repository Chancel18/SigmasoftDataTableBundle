# 📦 Guide d'Installation - SigmasoftDataTableBundle

## 🚀 Installation Rapide

### 1. Installation via Composer

```bash
composer require sigmasoft/datatable-bundle
```

### 2. Activation du Bundle

Le bundle s'active automatiquement avec Symfony Flex. Si ce n'est pas le cas :

```php
// config/bundles.php
return [
    // ... autres bundles
    Sigmasoft\DataTableBundle\SigmasoftDataTableBundle::class => ['all' => true],
];
```

### 3. Configuration TwigComponent (Automatique)

Le bundle configure automatiquement les composants Twig. Si vous rencontrez des problèmes, ajoutez manuellement :

```yaml
# config/packages/twig_component.yaml
twig_component:
    defaults:
        Sigmasoft\DataTableBundle\Twig\Components\:
            name_prefix: 'Sigmasoft'
            template_directory: '@SigmasoftDataTable/components'
```

### 4. Installation du MakerBundle (Optionnel)

Pour utiliser la commande de génération automatique :

```bash
composer require symfony/maker-bundle --dev
```

### 5. Configuration du Bundle

```yaml
# config/packages/sigmasoft_data_table.yaml
sigmasoft_data_table:
    global_config:
        items_per_page: 25
        enable_search: true
        enable_sort: true
```

## 🛠️ Génération Automatique

### Prérequis pour la Commande Maker

La commande `make:datatable` nécessite `symfony/maker-bundle` :

```bash
# Si pas encore installé
composer require symfony/maker-bundle --dev

# Vérifier que la commande est disponible
php bin/console list make
```

### Commande Maker

```bash
# Génération automatique d'un DataTable
php bin/console make:datatable User --controller --with-actions --with-export

# Avec raccourcis
php bin/console make:datatable User -c -a -x -b
```

### Utilisation dans les Templates

```twig
{# UNE SEULE LIGNE POUR TOUT LE TABLEAU ! #}
<twig:SigmasoftDataTable entityClass="App\\Entity\\User" />
```

## 🔧 Dépannage

### Erreur "Could not generate a component name"

Si vous rencontrez cette erreur :

1. **Vérifiez la configuration TwigComponent :**
```yaml
# config/packages/twig_component.yaml
twig_component:
    defaults:
        Sigmasoft\DataTableBundle\Twig\Components\:
            name_prefix: 'Sigmasoft'
```

2. **Effacez le cache :**
```bash
php bin/console cache:clear
```

3. **Vérifiez que Symfony UX est installé :**
```bash
composer require symfony/ux-live-component
composer require symfony/ux-twig-component
```

### Erreur "Bundle not found"

```bash
# Vérifiez l'installation
composer show sigmasoft/datatable-bundle

# Réinstallez si nécessaire
composer remove sigmasoft/datatable-bundle
composer require sigmasoft/datatable-bundle
```

## 📋 Prérequis

- **PHP 8.1+**
- **Symfony 6.3+**
- **Doctrine ORM**
- **Twig**
- **Symfony UX (Live Components)**

## ✅ Vérification de l'Installation

### Test de la Commande Maker
```bash
php bin/console list make
# Vous devriez voir : make:datatable
```

### Test du Composant
```twig
{# Dans n'importe quel template #}
<twig:SigmasoftDataTable entityClass="App\\Entity\\User" />
```

## 🎯 Installation depuis GitHub (Développement)

Si le package n'est pas encore sur Packagist :

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Chancel18/SigmasoftDataTableBundle.git"
        }
    ],
    "require": {
        "sigmasoft/datatable-bundle": "dev-master"
    }
}
```

```bash
composer require sigmasoft/datatable-bundle:dev-master
```

## 📞 Support

En cas de problème :
- 🐛 [GitHub Issues](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
- 📖 [Documentation](README.md)
- 💬 [Discussions](https://github.com/Chancel18/SigmasoftDataTableBundle/discussions)