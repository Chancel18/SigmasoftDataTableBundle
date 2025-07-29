# CLAUDE.md - SigmasoftDataTableBundle

Ce fichier fournit les informations de contexte à Claude Code (claude.ai/code) pour travailler efficacement avec le **SigmasoftDataTableBundle**.

## 🎯 À Propos du Projet

**SigmasoftDataTableBundle** est un bundle Symfony moderne et autonome pour créer des tables de données interactives avec des fonctionnalités avancées d'édition inline, export et personnalisation.

### Informations Projet
- **Nom** : SigmasoftDataTableBundle
- **Package Composer** : `sigmasoft/datatable-bundle`
- **Version Actuelle** : 2.0.0
- **Namespace Principal** : `Sigmasoft\DataTableBundle`
- **Auteur** : Gédéon Makela (g.makela@sigmasoft-solution.com)
- **License** : MIT

## 🛠️ Stack Technique

### Environnement de Développement
- **PHP** : 8.1+ (minimum requis)
- **Symfony** : 6.4+ ou 7.0+ (bundle compatible)
- **Composer** : Gestionnaire de dépendances PSR-4
- **PHPUnit** : 10.0+ ou 11.0+ pour les tests

### Dépendances Principales
```json
{
    "symfony/framework-bundle": "^6.4|^7.0",
    "symfony/twig-bundle": "^6.4|^7.0",
    "symfony/ux-live-component": "^2.0",
    "symfony/ux-twig-component": "^2.0",
    "symfony/form": "^6.4|^7.0",
    "symfony/validator": "^6.4|^7.0",
    "symfony/security-bundle": "^6.4|^7.0",
    "doctrine/orm": "^2.15|^3.0",
    "doctrine/doctrine-bundle": "^2.10",
    "knplabs/knp-paginator-bundle": "^6.0",
    "twig/twig": "^3.0"
}
```

### Technologies Clés
- **Symfony UX LiveComponent** : Pour les interactions client-serveur fluides
- **Doctrine ORM** : Pour la gestion des données et requêtes
- **Twig** : Moteur de template avec composants réutilisables
- **Bootstrap 5** : Framework CSS pour l'interface utilisateur
- **JavaScript ES6** : Classes modernes pour les interactions client

## 🏗️ Architecture du Bundle

### Structure des Répertoires
```
src/
├── SigmasoftDataTableBundle/
│   ├── Builder/                 # API fluide pour configuration
│   ├── Column/                  # Types de colonnes (Text, Date, Badge, Action, Editable)
│   ├── Component/               # LiveComponent principal (DataTableComponent)
│   ├── Configuration/           # Classes de configuration et résolution
│   ├── DataProvider/            # Providers de données (Doctrine)
│   ├── DependencyInjection/     # Configuration Symfony et Compiler Pass
│   ├── Exception/               # Exceptions spécifiques au bundle
│   ├── Factory/                 # Factories pour création d'objets
│   ├── InlineEdit/              # Architecture modulaire édition inline V2
│   │   ├── Configuration/       # Configuration des champs éditables
│   │   └── Renderer/           # Renderers de champs (Strategy Pattern)
│   ├── Maker/                   # Commande make:datatable
│   └── Service/                 # Services principaux et registries
```

### Patterns de Développement Utilisés
1. **Builder Pattern** : `DataTableBuilder` pour configuration fluide
2. **Factory Pattern** : `DataTableFactory`, `EditableColumnFactory`
3. **Strategy Pattern** : Renderers de champs extensibles
4. **Registry Pattern** : `DataTableRegistry`, `FieldRendererRegistry`
5. **Dependency Injection** : Services Symfony avec autowiring

## 🔧 Commandes de Développement

### Installation et Setup
```bash
# Installation des dépendances
composer install

# Validation de la configuration
composer validate
```

### Tests
```bash
# Tous les tests
./vendor/bin/phpunit

# Tests spécifiques avec couverture
./vendor/bin/phpunit --coverage-html coverage/

# Tests d'un répertoire spécifique
./vendor/bin/phpunit tests/InlineEdit/
./vendor/bin/phpunit tests/Component/

# Test d'une classe spécifique
./vendor/bin/phpunit tests/InlineEdit/Renderer/ColorFieldRendererTest.php

# Test avec filtre de méthode
./vendor/bin/phpunit --filter testSupportsColorField
```

### Analyse de Code
```bash
# PHPStan (si installé)
./vendor/bin/phpstan analyse src/

# Vérification PSR
composer check-platform-reqs
```

## 📋 Fonctionnalités Principales

### 1. DataTable de Base
- **Configuration fluide** avec `DataTableBuilder`
- **Colonnes typées** : Text, Date, Badge, Action
- **Recherche et tri** dynamiques
- **Pagination** configurable
- **Templates responsive** Bootstrap 5

### 2. Édition Inline V2 (Architecture Modulaire)
- **Renderers extensibles** avec interface `FieldRendererInterface`
- **Types supportés** : Text, Email, Select, Textarea, Color, Number
- **Validation robuste** côté serveur et client
- **Transactions sécurisées** avec rollback automatique
- **JavaScript ES6** avec debouncing et retry

### 3. Renderers Personnalisés
```php
// Exemple de renderer personnalisé
class CustomFieldRenderer extends AbstractFieldRenderer
{
    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->getFieldType() === 'custom';
    }
    
    public function render(...): string
    {
        // Logique de rendu
    }
}
```

### 4. Export de Données
- **CSV** et **Excel** avec PhpSpreadsheet
- **Filtres appliqués** dans l'export
- **Styles automatiques** et formatage

## 🔐 Sécurité et Validation

### Mesures de Sécurité Implémentées
- **Échappement HTML** automatique des données
- **Validation SQL** contre les injections via métadonnées Doctrine
- **Contrôle des permissions** par rôle utilisateur
- **CSRF Protection** sur les formulaires d'édition
- **Logging PSR-3** des erreurs et tentatives suspectes

### Configuration Sécurité
```php
// Options de sécurité dans InlineEditServiceV2
$securityOptions = [
    'allowed_roles' => ['ROLE_ADMIN', 'ROLE_EDITOR'],
    'readonly_fields' => ['id', 'createdAt', 'updatedAt'],
    'check_owner' => true,
    'owner_field' => 'user'
];
```

## ⚙️ Configuration du Bundle

### Configuration YAML Type
```yaml
sigmasoft_data_table:
    defaults:
        items_per_page: 10
        enable_search: true
        table_class: 'table table-striped table-hover'
        date_format: 'd/m/Y'
        enable_export: true
        export_formats: ['csv', 'excel']
```

### Enregistrement des Renderers
Les renderers personnalisés sont automatiquement enregistrés via le **Compiler Pass** `FieldRendererPass` s'ils implémentent `FieldRendererInterface`.

## 🧪 Tests et Qualité

### Couverture de Tests
- **81%+ de couverture** sur le code principal
- **34+ tests unitaires** et fonctionnels
- **Tests d'intégration** pour l'édition inline
- **Tests des renderers** personnalisés

### Structure des Tests
```
tests/
├── Builder/              # Tests du DataTableBuilder
├── Component/            # Tests du LiveComponent
├── Configuration/        # Tests des configurations
├── InlineEdit/           # Tests édition inline V2
│   └── Renderer/        # Tests des renderers
├── Integration/          # Tests d'intégration
└── Service/             # Tests des services
```

## 🚀 Utilisation en Développement

### Exemple d'Utilisation Complète
```php
// Dans un contrôleur
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
            ->addColumn(
                $this->editableColumnFactory->text('name', 'name', 'Nom')
                    ->required(true)
                    ->maxLength(100)
            )
            ->addColumn(
                $this->editableColumnFactory->email('email', 'email', 'Email')
            )
            ->addColumn(
                $this->editableColumnFactory->select('status', 'status', 'Statut', [
                    'Y' => 'Actif',
                    'N' => 'Inactif'
                ])
            )
            ->addDateColumn('createdAt', 'createdAt', 'Créé le')
            ->configureSearch(true, ['name', 'email'])
            ->configurePagination(true, 10);

        return $this->render('user/index.html.twig', [
            'datatableConfig' => $config,
        ]);
    }
}
```

## 📚 Documentation et Ressources

### Fichiers de Documentation
- **README.md** : Documentation principale d'utilisation
- **CHANGELOG.md** : Historique des versions et changements
- **Documentation/CUSTOM_RENDERERS.md** : Guide des renderers personnalisés

### Exemples d'Intégration
- **ColorFieldRenderer** : Exemple complet de renderer personnalisé
- **Tests unitaires** : Exemples d'utilisation dans `/tests/`

## 🎯 Points Clés pour le Développement

### Architecture Modulaire V2
L'édition inline utilise une **architecture découplée** :
- **Configuration** séparée du rendu
- **Strategy Pattern** pour les renderers
- **Factory** pour la création simplifiée
- **Registry** pour la gestion centralisée

### JavaScript Moderne
- **ES6 Classes** (`InlineEditManagerV2`)
- **Debouncing intelligent** (1 seconde)
- **Retry automatique** (3 tentatives)
- **Gestion hors ligne** avec reconnexion

### Bonnes Pratiques
- **Transactions complètes** avec rollback
- **Validation côté serveur** et client
- **Logging détaillé** avec contexte
- **Tests automatisés** pour chaque feature

## 🔄 Migration et Compatibilité

### Compatibilité Versions
- **Symfony 6.4+** et **7.0+** supportés
- **PHP 8.1+** minimum requis
- **Doctrine ORM 2.15+** ou **3.0+**

### Breaking Changes V2
- Namespace changé : `App\SigmasoftDataTableBundle` → `Sigmasoft\DataTableBundle`
- `EditableColumn` → `EditableColumnV2` avec Factory
- `InlineEditService` → `InlineEditServiceV2`

---

## 💡 Instructions Spéciales pour Claude

Quand tu travailles sur ce bundle :

1. **Respecte l'architecture modulaire** : utilise les patterns existants (Builder, Factory, Strategy)
2. **Tests obligatoires** : crée toujours des tests pour les nouvelles fonctionnalités
3. **Sécurité première** : valide toujours les entrées utilisateur et échappe les sorties
4. **Documentation** : mets à jour README.md et CHANGELOG.md pour chaque modification
5. **Namespaces** : utilise toujours `Sigmasoft\DataTableBundle` comme base
6. **PSR Standards** : respecte PSR-4 pour l'autoloading et PSR-12 pour le code style
7. **Symfony Best Practices** : suis les conventions Symfony pour les services et DI

**Commandes importantes à retenir :**
- `composer validate` : Validation de la configuration
- `./vendor/bin/phpunit` : Exécution des tests
- `composer install` : Installation des dépendances