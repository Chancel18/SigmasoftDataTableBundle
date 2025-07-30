# Changelog

Toutes les modifications importantes de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet respecte le [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.8] - 2025-07-30

### 🔧 Corrections Critiques

#### Maker Command `make:datatable`
- **RÉSOLU** : Suppression du fichier bundle en double `src/SigmasoftDataTableBundle/SigmasoftDataTableBundle.php`
- **RÉSOLU** : Correction du chemin `getPath()` dans le bundle principal
- **RÉSOLU** : Extension et services maintenant correctement chargés
- **RÉSOLU** : Commande `make:datatable` maintenant visible dans `php bin/console list make`

#### Amélioration de la Stabilité
- **Autoload** : Structure de fichiers corrigée pour éviter les conflits
- **Bundle Loading** : Extension `SigmasoftDataTableExtension` maintenant fonctionnelle
- **Services** : Tous les services du bundle correctement enregistrés

## [2.0.7] - 2025-07-30

### 🚨 HOTFIX ClassNotFoundError
- **RÉSOLU** : Vérification `class_exists()` avant utilisation de `FieldRendererPass`
- **SÉCURITÉ** : Installation du bundle sans erreur garantie

## [2.0.6] - 2025-07-30

### 🔧 Correction Critique Production
- **RÉSOLU** : ClassNotFoundError lors du `cache:clear` en production
- **AMÉLIORATION** : Gestion conditionnelle des compiler passes

## [2.0.5] - 2025-07-30

### 📚 Documentation Complète
- **NOUVEAU** : Documentation Docusaurus complète avec GitHub Pages
- **NOUVEAU** : Guide complet d'installation et utilisation  
- **NOUVEAU** : Exemples d'intégration détaillés

## [2.0.0] - 2025-07-29

### 🎯 Nouvelles Fonctionnalités Majeures

#### Édition Inline Modulaire V2

- **Architecture refactorée** avec séparation complète des responsabilités
- **Strategy Pattern** pour les renderers de champs extensibles
- **Factory Pattern** avec `EditableColumnFactory` pour création simplifiée
- **Registry Pattern** pour gestion centralisée des renderers
- **Support natif** de nouveaux types : Color, Email, Number, Select, Textarea

#### Système de Renderers Personnalisés

- **Interface standardisée** `FieldRendererInterface`
- **Classe abstraite** `AbstractFieldRenderer` avec utilitaires
- **Compiler Pass** pour injection automatique des renderers
- **ColorFieldRenderer** complet avec :
  - Color picker HTML5 natif
  - Preview en temps réel
  - Palette de 12 couleurs prédéfinies
  - Normalisation des formats (hex, noms)

#### Export de Données Amélioré

- **Export CSV** avec configuration flexible
- **Export Excel** via PhpSpreadsheet
- **Styles et formatage** automatiques
- **Support des filtres** et recherche dans l'export
- **Gestion des gros volumes** par batch

### 🔧 Améliorations Techniques

#### Service InlineEditServiceV2

- **Transactions complètes** avec rollback automatique
- **Validation robuste** avec configuration par champ
- **Conversion de types** selon métadonnées Doctrine
- **Sécurité renforcée** :
  - Contrôle des permissions par rôle
  - Vérification du propriétaire
  - Champs en lecture seule
- **Logging détaillé** avec contexte PSR-3

#### JavaScript InlineEditManagerV2

- **ES6 Classes** modernes
- **Debouncing intelligent** (1 seconde par défaut)
- **Retry automatique** (3 tentatives)
- **Gestion hors ligne** avec reconnexion
- **Actions groupées** (bulk save/cancel)
- **Notifications toast** élégantes
- **Indicateurs visuels** de statut

### 🐛 Corrections de Bugs

- **Fix #1**: Résolution du problème d'injection `UrlGeneratorInterface` dans `ActionColumn`
- **Fix #2**: Correction des appels de méthodes `hasMinLength()` → `getMinLength()`
- **Fix #3**: Adaptation des valeurs pour colonnes VARCHAR(1) (status)
- **Fix #4**: Résolution des erreurs 404 AssetMapper pour JavaScript
- **Fix #5**: Correction des permissions de sécurité dans l'exemple

### 📚 Documentation

- **Guide complet** des renderers personnalisés
- **Documentation détaillée** de l'édition inline V2
- **Exemples pratiques** avec code complet
- **Tests unitaires** pour ColorFieldRenderer
- **Migration vers bundle autonome** avec namespaces PSR

### ⚠️ Breaking Changes

- L'ancienne classe `EditableColumn` est dépréciée, utilisez `EditableColumnV2`
- `InlineEditService` remplacé par `InlineEditServiceV2`
- Nouvelle injection de dépendances requise pour `EditableColumnFactory`
- **Namespace** changé de `App\SigmasoftDataTableBundle` vers `Sigmasoft\DataTableBundle`

### 🔄 Migration vers Bundle Autonome

- **Nouveau namespace** : `Sigmasoft\DataTableBundle`
- **Composer package** : `sigmasoft/datatable-bundle`
- **PSR-4 autoloading** configuré
- **Tests PHPUnit** avec bootstrap Symfony
- **License MIT** incluse

### 🚀 Installation

```bash
composer require sigmasoft/datatable-bundle
```

### 📦 Architecture Bundle

- **src/** - Code source avec namespaces PSR-4
- **tests/** - Tests unitaires et fonctionnels
- **templates/** - Templates Twig du bundle
- **composer.json** - Configuration package avec dépendances Symfony 6.4+
- **README.md** - Documentation complète d'utilisation

## [1.2.1] - 2025-01-15

### Corrections

- Fix de la confirmation de suppression qui s'exécutait même après annulation
- Amélioration du JavaScript de confirmation avec `event.stopPropagation()`

## [1.2.0] - 2025-01-10

### Nouvelles Fonctionnalités

- Support complet de l'édition inline basique
- Export CSV et Excel initial
- Commande Maker `make:datatable`

### Améliorations

- Délai sur la recherche (1000ms) pour éviter les requêtes excessives
- Pagination toujours visible si activée
- Template LiveComponent avec élément racine unique

## [1.0.0] - 2024-12-15

### Version Initiale

- Architecture modulaire avec patterns Builder, Factory, Registry
- Support des colonnes : Text, Date, Badge, Action
- Recherche et tri dynamiques
- Pagination configurable
- Intégration Symfony UX LiveComponent
- Configuration YAML flexible
- Templates Bootstrap 5

---

Pour plus de détails sur les changements, consultez le [repository GitHub](https://github.com/sigmasoft-solution/datatable-bundle).