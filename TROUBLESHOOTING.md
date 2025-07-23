# 🔧 Guide de Dépannage - SigmasoftDataTableBundle

## 🚨 Erreurs d'Autowiring Communes

### 1. "Cannot autowire ConfigurationManager"

**Erreur :**
```
Cannot autowire service "Sigmasoft\DataTableBundle\Twig\Components\SigmasoftDataTableComponent": 
argument "$configurationManager" references class "Sigmasoft\DataTableBundle\Service\ConfigurationManager" 
but no such service exists.
```

**Solution :**
Cette erreur est corrigée dans la version 1.3.2+. Mettez à jour :

```bash
composer update sigmasoft/datatable-bundle
php bin/console cache:clear
```

### 2. "Cannot autowire HubInterface" (Mercure)

**Erreur :**
```
Cannot find service for argument "$hub" of method "__construct()" 
for class "Sigmasoft\DataTableBundle\Service\RealtimeUpdateService".
```

**Solution :**
Le service Mercure est optionnel. Si vous n'utilisez pas Mercure :

```yaml
# config/packages/sigmasoft_data_table.yaml
sigmasoft_data_table:
    global_config:
        realtime:
            enabled: false
            mercure: false
```

Si vous voulez utiliser Mercure :

```bash
composer require symfony/mercure-bundle
```

### 3. "Service does not exist" après installation

**Erreur :**
```
Service "Sigmasoft\DataTableBundle\Service\..." does not exist.
```

**Solutions :**

1. **Vider le cache :**
```bash
php bin/console cache:clear
```

2. **Vérifier l'enregistrement du bundle :**
```php
// config/bundles.php
return [
    // ... autres bundles
    Sigmasoft\DataTableBundle\SigmasoftDataTableBundle::class => ['all' => true],
];
```

3. **Réinstaller le bundle :**
```bash
composer remove sigmasoft/datatable-bundle
composer require sigmasoft/datatable-bundle
```

## 🎯 Erreurs de Configuration

### 1. "No entity configuration found"

**Erreur :**
```
No configuration found for entity: App\Entity\User
```

**Solution :**
Ajoutez la configuration de l'entité :

```yaml
# config/packages/sigmasoft_data_table.yaml
sigmasoft_data_table:
    entities:
        'App\Entity\User':
            label: 'Utilisateurs'
            fields:
                id: { type: 'integer', sortable: true }
                name: { type: 'string', searchable: true }
```

Ou utilisez la commande Maker :

```bash
php bin/console make:datatable User
```

### 2. "Template not found"

**Erreur :**
```
Template "@SigmasoftDataTable/components/..." not found.
```

**Solution :**
Vérifiez que le bundle est correctement enregistré et videz le cache :

```bash
php bin/console cache:clear
php bin/console debug:twig --filter=SigmasoftDataTable
```

## 🔄 Erreurs de Composant Twig

### 1. "Component name already exists"

**Erreur :**
```
Another component already has this name: "SigmasoftDataTableComponent"
```

**Solution :**
Utilisez le nouveau nom de composant (version 1.3.1+) :

```twig
<!-- ANCIEN (❌) -->
<twig:SigmasoftDataTableComponent entityClass="App\\Entity\\User" />

<!-- NOUVEAU (✅) -->
<twig:SigmasoftDataTable entityClass="App\\Entity\\User" />
```

### 2. "Could not generate component name"

**Erreur :**
```
Could not generate a component name for class "..." no matching namespace found
```

**Solution :**
Ajoutez la configuration TwigComponent :

```yaml
# config/packages/twig_component.yaml
twig_component:
    defaults:
        Sigmasoft\DataTableBundle\Twig\Components\:
            name_prefix: 'Sigmasoft'
```

## 🗄️ Erreurs de Base de Données

### 1. "Entity not found"

**Erreur :**
```
Class "App\Entity\User" not found
```

**Solution :**
Vérifiez que l'entité existe et est correctement déclarée :

```bash
php bin/console doctrine:mapping:info
```

### 2. "No identifier/primary key"

**Erreur :**
```
Entity has no identifier/primary key
```

**Solution :**
Assurez-vous que votre entité a une clé primaire :

```php
<?php
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    
    // ... autres propriétés
}
```

## 🚀 Commandes de Diagnostic

## 🛠️ Problèmes avec la Commande Maker

### 1. "make:datatable not found"

**Error :**
```
Command "make:datatable" is not defined.
```

**Solutions :**

1. **Installer MakerBundle :**
```bash
composer require symfony/maker-bundle --dev
php bin/console cache:clear
```

2. **Vérifier que la commande est enregistrée :**
```bash
php bin/console list make | grep datatable
```

3. **Si toujours absent, vérifier les services :**
```bash
php bin/console debug:container maker.command
```

### Vérification de l'Installation

```bash
# Vérifier que le bundle est enregistré
php bin/console debug:container | grep -i sigmasoft

# Vérifier les services disponibles
php bin/console debug:container Sigmasoft

# Vérifier les composants Twig
php bin/console debug:twig --filter=Sigmasoft

# Vérifier la configuration
php bin/console debug:config sigmasoft_data_table

# Vérifier les commandes Maker
php bin/console list make
```

### Résolution Générale

```bash
# 1. Vider tous les caches
php bin/console cache:clear
rm -rf var/cache/*

# 2. Reconstruire l'autoload
composer dump-autoload

# 3. Réinstaller les dépendances
composer install --no-cache

# 4. Vérifier les permissions
chmod -R 755 var/cache var/log
```

## 📞 Support Avancé

### Activation du Mode Debug

```yaml
# config/packages/dev/sigmasoft_data_table.yaml
sigmasoft_data_table:
    global_config:
        debug: true
        log_level: 'debug'
```

### Logs Utiles

```bash
# Voir les logs Symfony
tail -f var/log/dev.log | grep -i sigmasoft

# Voir les erreurs de container
php bin/console lint:container
```

### Informations à Fournir pour le Support

Lorsque vous créez un ticket, incluez :

1. **Version du bundle :** `composer show sigmasoft/datatable-bundle`
2. **Version Symfony :** `php bin/console --version`
3. **Version PHP :** `php --version`
4. **Configuration :** Contenu de `config/packages/sigmasoft_data_table.yaml`
5. **Erreur complète :** Stack trace complet
6. **Services :** `php bin/console debug:container | grep -i sigmasoft`

## 🔗 Liens Utiles

- 🐛 [GitHub Issues](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
- 📖 [Documentation](README.md)
- 🔧 [Installation](INSTALLATION.md)
- 🔄 [Migration](MIGRATION.md)