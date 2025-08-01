---
sidebar_position: 6
---

# Changelog

Toutes les modifications notables de ce projet sont documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Versioning Sémantique](https://semver.org/lang/fr/).

## [2.3.5] - 2025-07-31

### 🚀 Nouvelles Fonctionnalités
- **Installation Automatique** : Templates copiés automatiquement lors de l'installation
- **InstallAssetsCommand** : Nouvelle commande `php bin/console sigmasoft:datatable:install-assets`
- **PostInstallSubscriber** : Copie automatique des templates au premier lancement en dev
- **Configuration YAML** : Support complet avec toutes les options configurables
- **Recipe Symfony Flex** : Amélioration pour inclure les templates automatiquement

### 📚 Documentation
- **Configuration YAML** : [Guide complet](./user-guide/configuration.md) avec toutes les options
- **Édition Inline** : [Guide détaillé](./user-guide/inline-editing.md) avec exemples complets
- **Renderers Personnalisés** : [Guide développeur](./developer-guide/custom-renderers.md) avec architecture modulaire
- **Basic Usage** : Mise à jour avec section édition inline

### 🔧 Architecture
- **PostInstallPass** : CompilerPass pour copie automatique des templates
- **EventSubscriber** : Gestion intelligente de l'installation en environnement dev
- **Tests d'intégration** : Validation des chemins de templates ✅

### ⚡ Amélioré
- **UX Développeur** : Plus besoin de copie manuelle des templates
- **Installation** : Process d'installation entièrement automatisé
- **Tests** : 6 tests d'intégration passent ✅

## [2.3.4] - 2025-07-31

### 🐛 Corrigé
- **ColumnFactory** : Ajout du support pour `EditableColumnV2`
- **DependencyInjection** : Injection de `FieldRendererRegistry` dans `ColumnFactory`
- **Erreur Runtime** : Résolution de "Unknown column type: EditableColumnV2"

### 📚 Documentation
- **Édition Inline** : Ajout de la page complète [Guide d'édition inline](./user-guide/inline-editing.md)
- **Renderers** : Ajout du guide [Renderers Personnalisés](./developer-guide/custom-renderers.md)
- **Basic Usage** : Mise à jour avec section édition inline

### ⚡ Amélioré
- **Test Coverage** : Tests FreshTest validés avec succès
- **Packagist** : Version 2.3.4 disponible immédiatement

## [2.3.3] - 2025-07-31

### 🐛 Corrigé
- **LiveComponent** : Ajout des dépendances symfony/ux-live-component manquantes
- **Services** : Configuration auto-découverte corrigée
- **Templates** : Résolution du namespace @SigmasoftDataTable

## [2.3.2] - 2025-07-31

### 🐛 Corrigé
- **Templates** : Correction du rendu des données dans datatable.html.twig
- **Data Display** : Résolution du problème de données invisibles

## [2.3.1] - 2025-07-31

### 🐛 Corrigé
- **Services** : Suppression du binding $logger problématique
- **Compilation** : Résolution de l'erreur ResolveBindingsPass

## [2.3.0] - 2025-01-31

### 🚀 Ajouté
- **Structure PSR-4 complète** : Bundle restructuré selon les standards Symfony modernes
- **Recipe Symfony Flex** : Configuration automatique lors de l'installation
- **Autoloading optimisé** : Performance améliorée avec PSR-4 `src/` comme racine
- **Guide de migration v2.3** : Documentation complète pour migrer depuis v2.2.x
- **Configuration automatique** : Services auto-découverts sans configuration manuelle

### 🔧 Modifié
- **Structure de fichiers** : Migration de `src/SigmasoftDataTableBundle/` vers `src/`
- **composer.json** : Autoloading PSR-4 avec `"Sigmasoft\\DataTableBundle\\": "src/"`
- **DependencyInjection** : Chemins de services mis à jour pour la nouvelle structure
- **Services.yaml** : Optimisation des patterns de découverte automatique

### ⚡ Amélioré
- **Performance** : Chargement des services optimisé
- **Compatibilité** : Meilleure intégration avec Symfony 6.4+ et 7.0+
- **Développeur UX** : Installation plus simple avec Flex

### 🔒 Sécurité
- **Validation des chemins** : Vérification renforcée des chemins de services
- **Autoloading sûr** : Exclusions appropriées dans la découverte automatique

## [2.2.0] - 2025-01-30

### 🚀 Ajouté
- **Architecture Édition Inline V2** : Système modulaire avec renderers extensibles
- **Nouveaux Renderers** :
  - `TextFieldRenderer` : Champs texte avec validation
  - `EmailFieldRenderer` : Validation email intégrée
  - `SelectFieldRenderer` : Listes déroulantes dynamiques
  - `TextareaFieldRenderer` : Zones de texte multi-lignes
  - `ColorFieldRenderer` : Sélecteur de couleur avec validation
  - `NumberFieldRenderer` : Champs numériques avec contraintes
- **Factory Pattern** : `EditableColumnFactory` pour création simplifiée
- **Registry Pattern** : `FieldRendererRegistry` pour gestion centralisée
- **JavaScript ES6** : `InlineEditManagerV2` avec debouncing et retry automatique

### 🔧 Modifié
- **EditableColumn** → **EditableColumnV2** : API améliorée avec Factory
- **InlineEditService** → **InlineEditServiceV2** : Architecture découplée
- **Validation** : Système de validation robuste côté serveur et client
- **Transactions** : Gestion complète avec rollback automatique

### ⚡ Amélioré
- **Performance** : Debouncing intelligent (1 seconde)
- **Fiabilité** : Retry automatique (3 tentatives)
- **UX** : Gestion hors ligne avec reconnexion
- **Tests** : 34+ tests unitaires et fonctionnels

## [2.1.1] - 2025-01-29

### 🐛 Corrigé
- **Autoloading PSR-4** : Structure autoloading corrigée définitivement
- **Services DI** : Configuration services.yaml optimisée
- **Extensions** : SigmasoftDataTableExtension maintenant fonctionnelle

### 🔧 Modifié
- **Namespace** : Standardisation complète sur `Sigmasoft\DataTableBundle`
- **Tests** : Couverture étendue à 81%+

## [2.1.0] - 2025-01-28

### 🚀 Ajouté
- **Bundle Audit Complet** : Revue complète de l'architecture
- **Corrections Critiques** : Résolution des erreurs ClassNotFound
- **Documentation** : Guide développeur étendu
- **Tests** : Suite de tests unitaires complète

### 🐛 Corrigé
- **make:datatable** : Commande Maker maintenant fonctionnelle
- **ClassNotFoundError** : Résolution définitive des erreurs de chargement
- **Services** : Configuration DependencyInjection corrigée

## [2.0.9] - 2025-01-27

### 🐛 Corrigé
- **Extension** : SigmasoftDataTableExtension fonctionnelle
- **Services** : Enregistrement automatique des services

## [2.0.8] - 2025-01-26

### 🐛 Corrigé
- **Maker Command** : `make:datatable` maintenant pleinement fonctionnel
- **Generation** : Templates et configurations générés correctement

## [2.0.7] - 2025-01-25

### 🐛 Corrigé
- **ClassNotFoundError** : Erreur définitivement résolue
- **Autoloading** : Configuration PSR-4 optimisée

## [2.0.6] - 2025-01-24

### 🔧 Modifié
- **AbstractBundle** : Migration vers AbstractBundle Symfony moderne
- **Compatibilité** : Support Symfony 6.4+ et 7.0+

## [2.0.5] - 2025-01-23

### 🚀 Ajouté
- **Production Ready** : Version stable pour production
- **Enterprise Quality** : Standards enterprise appliqués
- **Documentation** : Documentation complète Docusaurus

### ⚡ Amélioré
- **Stabilité** : Tests automatisés 100% passés
- **Performance** : Optimisations diverses

## [2.0.0] - 2025-01-20

### 🚀 Version Majeure
- **Refonte complète** : Architecture moderne et modulaire
- **Symfony UX** : Intégration LiveComponent et TwigComponent
- **Bootstrap 5** : Templates responsives modernes
- **API Fluide** : DataTableBuilder pour configuration intuitive

### 💥 Breaking Changes
- **Namespace** : Migration vers `Sigmasoft\DataTableBundle`
- **Configuration** : Nouveau format YAML
- **Templates** : Nouveaux templates Bootstrap 5

### 🚀 Nouvelles Fonctionnalités
- **Live Components** : Interactions temps réel sans JavaScript
- **Maker Command** : Génération automatique avec `make:datatable`
- **Export** : Support CSV/Excel intégré
- **Inline Editing** : Édition directe dans le tableau
- **Actions** : Système d'actions configurables

---

## Types de Changements

- 🚀 **Ajouté** : Nouvelles fonctionnalités
- 🔧 **Modifié** : Changements dans les fonctionnalités existantes
- 🐛 **Corrigé** : Corrections de bugs
- ⚡ **Amélioré** : Améliorations de performance ou UX
- 🔒 **Sécurité** : Corrections de vulnérabilités
- 💥 **Breaking Changes** : Changements incompatibles

---

## Support des Versions

| Version | Status | Support jusqu'à | Symfony | PHP |
|---------|--------|----------------|---------|-----|
| **2.3.x** | ✅ **Active** | TBD | 6.4+ / 7.0+ | 8.1+ |
| **2.2.x** | ⚠️ LTS | 2026-01-31 | 6.4+ / 7.0+ | 8.1+ |
| **2.1.x** | ❌ EOL | 2025-06-30 | 6.4+ / 7.0+ | 8.1+ |
| **2.0.x** | ❌ EOL | 2025-03-31 | 6.4+ / 7.0+ | 8.1+ |

---

*Changelog maintenu par [Gédéon MAKELA](mailto:g.makela@sigmasoft-solution.com) - [Sigmasoft Solutions](https://sigmasoft-solution.com)*