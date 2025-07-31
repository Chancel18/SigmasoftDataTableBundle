# Changelog

Toutes les modifications importantes de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet respecte le [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2025-07-31

### 🚨 RESTRUCTURATION MAJEURE - Standards Symfony et PSR-4

#### Changed
- **BREAKING**: Restructuré l'autoloading PSR-4 - déplacé de `src/SigmasoftDataTableBundle/` vers `src/`
- **BREAKING**: Mise à jour des chemins de configuration des services
- Amélioration de la conformité aux standards Symfony
- Configuration DependencyInjection optimisée
- Autodécouverte des services améliorée avec de meilleures exclusions

#### Added
- Recette Symfony Flex pour configuration automatique
- Configuration PHPUnit avec rapports de couverture
- Support des variables d'environnement pour le cache
- Alias de branche pour les versions de développement
- Configuration Composer améliorée avec de meilleures contraintes

#### Fixed
- Structure d'autoloading PSR-4 suit maintenant les meilleures pratiques Symfony
- Chemins de chargement des services corrigés pour la nouvelle structure
- Extension charge maintenant les services depuis le bon répertoire

## [2.2.0] - 2025-07-31

### 🔧 AMÉLIORATION MAJEURE - Installation et Compatibilité

#### Configuration et Services
- **NOUVEAU** : Création du fichier `config/services.yaml` avec tous les services du bundle
- **NOUVEAU** : Support complet de l'autowiring et autoconfiguration
- **NOUVEAU** : Manifest Symfony Flex (`.symfony/manifest.json`) pour l'auto-configuration
- **NOUVEAU** : Configuration par défaut (`config/packages/sigmasoft_data_table.yaml`)

#### Corrections Structure
- **CRITIQUE** : Correction autoloading PSR-4 dans composer.json (`src/SigmasoftDataTableBundle/`)
- **CRITIQUE** : Correction `getPath()` dans SigmasoftDataTableBundle.php (retourne `__DIR__`)
- **CRITIQUE** : SigmasoftDataTableExtension charge maintenant `services.yaml` au lieu du code manuel
- **FIX** : Renommage du dossier templates de `SigmasoftDataTableBundle` à `SigmasoftDataTable`

#### Documentation
- **NOUVEAU** : Guide d'installation détaillé (`INSTALL.md`)
- **NOUVEAU** : Rapport d'audit complet (`BUNDLE_AUDIT_REPORT.md`)
- **MISE À JOUR** : Documentation Docusaurus avec étapes d'installation correctes
- **NOUVEAU** : Section troubleshooting complète avec solutions détaillées

#### Amélioration Expérience Utilisateur
- **NOUVEAU** : Instructions claires pour `composer dump-autoload` après installation
- **NOUVEAU** : Commandes de diagnostic pour vérifier l'installation
- **NOUVEAU** : Script de vérification rapide (`check-datatable.php`)

#### Impact
- **Installation** : Le bundle s'installe maintenant correctement dans tous les projets Symfony 6.4/7.0
- **Maker** : La commande `make:datatable` apparaît correctement après installation
- **Services** : Tous les services sont disponibles avec autowiring
- **Templates** : Les templates sont trouvés automatiquement

Cette version résout définitivement tous les problèmes d'installation rencontrés !

## [2.1.1] - 2025-07-30

### 🚨 FIX ULTIME - Structure Autoloading PSR-4

#### Correction Structure Bundle
- **CRITIQUE** : `SigmasoftDataTableBundle.php` déplacé à la racine (PSR-4 compliance)
- **RÉSOLU** : Structure d'autoloading maintenant correcte selon PSR-4
- **NETTOYAGE** : Suppression fichiers dupliqués dans src/ (DataTableBuilder.php, README.md)
- **FIX** : Ajustement getPath() pour pointer vers src/SigmasoftDataTableBundle

#### Impact
- **Bundle** : Maintenant correctement chargé par l'autoloader Composer
- **Extension** : Services maintenant enregistrés correctement
- **Maker** : Commande make:datatable maintenant visible et fonctionnelle

Cette correction résout définitivement le problème de chargement du bundle !

## [2.1.0] - 2025-07-30

### 🎯 CORRECTIONS MAJEURES - Bundle Audit Complet

#### Bundle Architecture Fixes
- **CRITIQUE** : Correction méthode `getPath()` - utilisation directe de `__DIR__`
- **CRITIQUE** : Template autonome - suppression dépendance `@components/card_default.html.twig`
- **CRITIQUE** : Unification références template - utilisation `@SigmasoftDataTable/datatable.html.twig`
- **AMÉLIORATION** : Template complètement refactorisé sans dépendances externes

#### Extension & Services
- **RÉSOLU** : Suppression YamlFileLoader problématique dans SigmasoftDataTableExtension
- **RÉSOLU** : Nettoyage imports inutiles (FileLocator, YamlFileLoader)
- **AMÉLIORATION** : Commentaires explicatifs pour vérifications d'existence de services

#### Template Improvements
- **NOUVEAU** : Template datatable.html.twig entièrement autonome
- **NOUVEAU** : Structure card Bootstrap native sans dépendances
- **NOUVEAU** : Styles CSS intégrés pour éviter dépendances externes
- **AMÉLIORATION** : Interface utilisateur plus robuste et portable

#### Stability & Reliability
- **SÉCURITÉ** : Élimination de tous les points de défaillance identifiés
- **PERFORMANCE** : Optimisation chargement des services
- **MAINTENANCE** : Code plus maintenable sans dépendances fragiles

## [2.0.9] - 2025-07-30

### 🚨 FIX CRITIQUE Extension Loading

#### Extension SigmasoftDataTableExtension
- **RÉSOLU** : Suppression YamlFileLoader inutile causant erreur silencieuse
- **RÉSOLU** : Extension maintenant chargée correctement par Symfony
- **RÉSOLU** : Services du bundle maintenant enregistrés et visibles
- **RÉSOLU** : Alias `sigmasoft_data_table` maintenant disponible

#### Stabilité Bundle
- **NETTOYAGE** : Suppression imports inutiles (FileLocator, YamlFileLoader)
- **OPTIMISATION** : Enregistrement direct des services sans fichier config

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