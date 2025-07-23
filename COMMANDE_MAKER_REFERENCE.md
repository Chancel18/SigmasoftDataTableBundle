# 📋 Référence Commande make:datatable

## 🚀 **Syntaxe de Base**

```bash
php bin/console make:datatable [ENTITY] [OPTIONS]
```

## 📝 **Options Disponibles**

| Option Longue | Raccourci | Description |
|---------------|-----------|-------------|
| `--controller` | `-c` | Générer le contrôleur CRUD complet |
| `--with-actions` | `-a` | Inclure les actions CRUD (view, edit, delete) |
| `--with-export` | `-x` | Inclure les fonctionnalités d'export (CSV, Excel, PDF) |
| `--with-bulk` | `-b` | Inclure les actions groupées (sélection multiple) |
| `--template-path` | `-t` | Chemin personnalisé pour les templates |
| `--overwrite` | *(aucun)* | Écraser les fichiers existants |

## 🎯 **Exemples d'Utilisation**

### Génération Basique
```bash
# Configuration minimale
php bin/console make:datatable User

# Avec contrôleur
php bin/console make:datatable User --controller
php bin/console make:datatable User -c
```

### Génération Complète
```bash
# Version longue (explicite)
php bin/console make:datatable User --controller --with-actions --with-export --with-bulk

# Version courte (raccourcis)
php bin/console make:datatable User -c -a -x -b
```

### Options Avancées
```bash
# Chemin de template personnalisé
php bin/console make:datatable User -c -a --template-path="admin/users/"

# Écraser les fichiers existants
php bin/console make:datatable User -c -a --overwrite
```

## 📊 **Résultat de la Génération**

### Fichiers Générés

| Option | Fichiers Créés |
|--------|----------------|
| *(basique)* | `config/packages/sigmasoft_data_table.yaml` |
| `-c` | + `src/Controller/UserController.php` |
| `-a` | + Actions CRUD dans la configuration |
| `-x` | + Configuration d'export dans YAML |
| `-b` | + Actions groupées dans la configuration |
| `-t PATH` | Templates dans `templates/PATH/` |

### Template Généré
```twig
{# templates/user/index.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
    <h1>👥 Gestion des Utilisateurs</h1>
    
    {# 🚀 UNE SEULE LIGNE POUR TOUT LE TABLEAU ! #}
    <twig:SigmasoftDataTable entityClass="App\\Entity\\User" />
{% endblock %}
```

## 🔧 **Configuration Générée**

### YAML Auto-généré
```yaml
# config/packages/sigmasoft_data_table.yaml
sigmasoft_data_table:
    entities:
        'App\Entity\User':
            label: 'Gestion des Utilisateurs'
            items_per_page: 25
            enable_search: true
            enable_sort: true
            
            fields:
                id: { type: 'integer', label: 'ID', sortable: true }
                name: { type: 'string', label: 'Nom', searchable: true }
                email: { type: 'email', label: 'Email', searchable: true }
                # ... autres champs détectés automatiquement
```

## 🎨 **Auto-détection des Champs**

La commande analyse automatiquement votre entité Doctrine et :

- ✅ **Détecte les types** (string, integer, boolean, date, etc.)
- ✅ **Génère des labels** lisibles (camelCase → "Title Case")
- ✅ **Configure la recherche** (champs string/text recherchables)
- ✅ **Configure le tri** (tous les champs sauf text/json/blob)
- ✅ **Gère les relations** (ManyToOne/OneToOne)
- ✅ **Détecte les formats** (email, url, image par nom de champ)

## 🚨 **Résolution d'Erreurs**

### Erreur "option shortcut already exists"
**Solution :** Mise à jour vers v1.3.5+
```bash
composer update sigmasoft/datatable-bundle
```

### Commande non trouvée
**Solution :** Installer MakerBundle
```bash
composer require symfony/maker-bundle --dev
php bin/console cache:clear
```

## 💡 **Conseils d'Utilisation**

1. **Utilisez les raccourcis** pour aller plus vite : `-c -a -x -b`
2. **Testez d'abord sans options** pour voir la configuration de base
3. **Personnalisez après génération** dans le fichier YAML créé
4. **Utilisez `--overwrite`** pour régénérer après modifications d'entité

## 📞 **Support**

- 🐛 [Issues GitHub](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
- 📖 [Documentation complète](README.md)
- 🔧 [Guide d'installation](INSTALLATION.md)