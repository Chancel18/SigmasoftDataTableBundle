---
sidebar_position: 1
---

# Problèmes d'Installation

## Problèmes Courants et Solutions

### 1. La commande make:datatable n'apparaît pas

**Symptômes:**
- La commande `make:datatable` n'est pas listée dans `php bin/console list make`
- Erreur "Command make:datatable is not defined"

**Solutions:**

1. **Vérifiez que MakerBundle est installé:**
   ```bash
   composer require --dev symfony/maker-bundle
   ```

2. **Mettez à jour l'autoloader:**
   ```bash
   composer dump-autoload
   ```

3. **Videz le cache:**
   ```bash
   php bin/console cache:clear
   ```

4. **Vérifiez que le bundle est bien enregistré:**
   ```bash
   php bin/console debug:container sigmasoft.maker
   ```

### 2. Service non trouvé (ServiceNotFoundException)

**Symptômes:**
- Erreur "Service Sigmasoft\DataTableBundle\Builder\DataTableBuilder not found"
- Erreur d'autowiring dans les contrôleurs

**Solutions:**

1. **Vérifiez l'enregistrement du bundle:**
   ```php title="config/bundles.php"
   return [
       // ...
       Sigmasoft\DataTableBundle\SigmasoftDataTableBundle::class => ['all' => true],
   ];
   ```

2. **Mettez à jour l'autoloader et videz le cache:**
   ```bash
   composer dump-autoload
   php bin/console cache:clear
   ```

3. **Vérifiez la configuration des services:**
   ```bash
   php bin/console debug:container "Sigmasoft\DataTableBundle"
   ```

### 3. Template non trouvé

**Symptômes:**
- Erreur "Unable to find template @SigmasoftDataTable/datatable.html.twig"

**Solutions:**

1. **Vérifiez le chemin des templates:**
   - Le dossier doit être `templates/SigmasoftDataTable/` (pas `SigmasoftDataTableBundle`)

2. **Vérifiez la configuration:**
   ```yaml title="config/packages/sigmasoft_data_table.yaml"
   sigmasoft_data_table:
       templates:
           datatable: '@SigmasoftDataTable/datatable.html.twig'
   ```

3. **Installez les assets:**
   ```bash
   php bin/console assets:install
   ```

### 4. Erreur d'autoloading PSR-4

**Symptômes:**
- Erreur "Class Sigmasoft\DataTableBundle\... not found"
- Problèmes après l'installation du bundle

**Solutions:**

1. **Vérifiez le composer.json du bundle:**
   - L'autoloading doit pointer vers `src/SigmasoftDataTableBundle/`

2. **Forcez la mise à jour de l'autoloader:**
   ```bash
   composer dump-autoload -o
   ```

3. **Réinstallez le bundle si nécessaire:**
   ```bash
   composer remove sigmasoft/datatable-bundle
   composer require sigmasoft/datatable-bundle
   ```

### 5. Problème avec Symfony Flex

**Symptômes:**
- Le bundle n'est pas auto-configuré
- Les fichiers de configuration ne sont pas copiés

**Solutions:**

1. **Activez Symfony Flex si nécessaire:**
   ```bash
   composer require symfony/flex
   ```

2. **Copiez manuellement la configuration:**
   ```bash
   # Créez le fichier de configuration
   mkdir -p config/packages
   touch config/packages/sigmasoft_data_table.yaml
   ```

   Puis ajoutez la configuration par défaut (voir la section Configuration).

### 6. Incompatibilité de version

**Symptômes:**
- Erreur de dépendances Composer
- "Your requirements could not be resolved to an installable set of packages"

**Solutions:**

1. **Vérifiez les versions requises:**
   - PHP >= 8.1
   - Symfony 6.4 ou 7.0
   - Doctrine ORM 2.15+ ou 3.0+

2. **Mettez à jour vos dépendances:**
   ```bash
   composer update
   ```

3. **Si nécessaire, spécifiez une version:**
   ```bash
   composer require sigmasoft/datatable-bundle:^2.0
   ```

### 7. Live Component non configuré

**Symptômes:**
- Erreur "Component sigmasoft_datatable not found"
- Problèmes avec les composants Twig

**Solutions:**

1. **Installez les dépendances UX:**
   ```bash
   composer require symfony/ux-live-component symfony/ux-twig-component
   ```

2. **Vérifiez la configuration des composants:**
   ```bash
   php bin/console debug:twig-component
   ```

## Commandes de Diagnostic

### Vérifier l'installation complète:

```bash
# Vérifier que le bundle est chargé
php bin/console debug:container --tag=sigmasoft_datatable

# Vérifier la configuration
php bin/console config:dump sigmasoft_data_table

# Lister tous les services du bundle
php bin/console debug:container | grep -i sigmasoft

# Vérifier les composants Twig
php bin/console debug:twig-component | grep -i sigmasoft
```

### Script de vérification rapide:

Créez un fichier `check-datatable.php` dans le répertoire racine :

```php
<?php
// check-datatable.php
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new \App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

echo "Checking SigmasoftDataTableBundle installation...\n\n";

// Check bundle registration
$bundles = $kernel->getBundles();
if (isset($bundles['SigmasoftDataTableBundle'])) {
    echo "✅ Bundle is registered\n";
} else {
    echo "❌ Bundle is NOT registered\n";
}

// Check key services
$services = [
    'Sigmasoft\DataTableBundle\Builder\DataTableBuilder',
    'Sigmasoft\DataTableBundle\Component\DataTableComponent',
    'Sigmasoft\DataTableBundle\Maker\MakeDataTable',
];

foreach ($services as $service) {
    if ($container->has($service)) {
        echo "✅ Service $service is available\n";
    } else {
        echo "❌ Service $service is NOT available\n";
    }
}

echo "\nInstallation check complete!\n";
```

Exécutez avec :
```bash
php check-datatable.php
```

## Support

Si les problèmes persistent après avoir suivi ces étapes :

1. **Consultez les logs Symfony:**
   ```bash
   tail -f var/log/dev.log
   ```

2. **Ouvrez une issue sur GitHub:**
   - [Issues GitHub](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
   - Incluez la sortie des commandes de diagnostic
   - Précisez votre version de Symfony et PHP

3. **Contactez le support:**
   - Email: support@sigmasoft-solution.com
   - Documentation: https://chancel18.github.io/SigmasoftDataTableBundle/

---

*Dernière mise à jour: 31/07/2025*