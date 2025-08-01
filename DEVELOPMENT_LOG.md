# Journal de Développement - SigmasoftDataTableBundle

## 2025-08-01 - Corrections Configuration YAML et Système d'Événements

### Problèmes identifiés
1. **Configuration YAML non prise en compte** : La configuration définie dans `config/packages/sigmasoft_data_table.yaml` n'était pas utilisée par les services du bundle
2. **Système d'événements inexistant** : La documentation mentionnait un système d'événements qui n'existait pas dans le code

### Modifications apportées

#### 1. Configuration YAML

##### DataTableConfigResolver.php
- **Ajout** : Propriété `$bundleConfig` pour stocker la configuration du bundle
- **Modification** : Le constructeur charge maintenant la configuration depuis le ParameterBag
- **Modification** : La méthode `getGlobalDefaults()` vérifie d'abord la configuration du bundle avant de chercher le fichier `global.yaml`

```php
// Avant
public function getGlobalDefaults(): array
{
    $projectDir = $this->parameterBag->get('kernel.project_dir');
    $globalPath = sprintf('%s/config/datatable/global.yaml', $projectDir);
    
    if (file_exists($globalPath)) {
        $config = \Symfony\Component\Yaml\Yaml::parseFile($globalPath);
        return $config['datatable']['defaults'] ?? [];
    }
    
    return [];
}

// Après
public function getGlobalDefaults(): array
{
    // D'abord, vérifier la configuration du bundle
    if (!empty($this->bundleConfig['defaults'])) {
        return $this->bundleConfig['defaults'];
    }
    
    // Ensuite, vérifier le fichier global.yaml
    $projectDir = $this->parameterBag->get('kernel.project_dir');
    $globalPath = sprintf('%s/config/datatable/global.yaml', $projectDir);
    
    if (file_exists($globalPath)) {
        $config = \Symfony\Component\Yaml\Yaml::parseFile($globalPath);
        return $config['datatable']['defaults'] ?? [];
    }
    
    return [];
}
```

##### DataTableBuilder.php
- **Modification** : La méthode `createDataTable()` applique maintenant automatiquement les configurations par défaut du bundle

```php
public function createDataTable(string $entityClass): DataTableConfiguration
{
    $config = new DataTableConfiguration($entityClass);
    
    // Appliquer les configurations par défaut du bundle
    $defaults = $this->configResolver->getGlobalDefaults();
    if (!empty($defaults)) {
        if (isset($defaults['items_per_page'])) {
            $config->setItemsPerPage($defaults['items_per_page']);
        }
        if (isset($defaults['enable_search'])) {
            $config->enableSearch($defaults['enable_search']);
        }
        if (isset($defaults['enable_pagination'])) {
            $config->enablePagination($defaults['enable_pagination']);
        }
        if (isset($defaults['enable_sorting'])) {
            $config->enableSorting($defaults['enable_sorting']);
        }
        if (isset($defaults['table_class'])) {
            $config->setTableClass($defaults['table_class']);
        }
        if (isset($defaults['date_format'])) {
            $config->setDateFormat($defaults['date_format']);
        }
    }
    
    return $config;
}
```

#### 2. Système d'événements

##### Nouvelles classes créées

###### src/Event/DataTableEvents.php
- Classe contenant toutes les constantes d'événements du bundle
- Événements définis :
  - `PRE_QUERY` : Avant l'exécution de la requête
  - `POST_QUERY` : Après l'exécution de la requête
  - `PRE_INLINE_EDIT` : Avant l'édition inline
  - `POST_INLINE_EDIT` : Après l'édition inline
  - `BULK_ACTION` : Lors d'une action groupée
  - `EXPORT` : Lors de l'export de données

###### src/Event/DataTableEvent.php
- Classe de base pour tous les événements DataTable
- Propriétés : `entityClass`, `context`

###### src/Event/DataTableQueryEvent.php
- Événement spécifique pour les requêtes
- Propriétés : `queryBuilder`, `searchTerm`, `sortField`, `sortDirection`, `currentPage`, `itemsPerPage`, `results`

###### src/Event/InlineEditEvent.php
- Événement spécifique pour l'édition inline
- Propriétés : `entity`, `field`, `oldValue`, `newValue`, `valid`, `errors`
- Méthodes pour valider/invalider l'édition et ajouter des erreurs

##### Services modifiés pour supporter les événements

###### InlineEditServiceV2.php
- **Ajout** : Import des classes d'événements et EventDispatcherInterface
- **Modification** : Constructeur accepte maintenant un EventDispatcher optionnel
- **Ajout** : Déclenchement de `PRE_INLINE_EDIT` avant la modification
- **Ajout** : Déclenchement de `POST_INLINE_EDIT` après la modification réussie
- **Ajout** : Possibilité de modifier/valider la valeur via l'événement PRE_INLINE_EDIT

###### DoctrineDataProvider.php
- **Ajout** : Import des classes d'événements et EventDispatcherInterface
- **Modification** : Constructeur accepte maintenant un EventDispatcher optionnel
- **Ajout** : Déclenchement de `PRE_QUERY` avant la requête (permet de modifier le QueryBuilder)
- **Ajout** : Déclenchement de `POST_QUERY` après la requête (permet de traiter les résultats)

###### config/services.yaml
- **Modification** : Ajout du paramètre `@?event_dispatcher` pour InlineEditServiceV2
- **Modification** : Ajout du paramètre `@?event_dispatcher` pour DoctrineDataProvider

#### 3. Documentation

##### Nouvelle page créée : docs/docs/user-guide/events.md
- Documentation complète du système d'événements
- Exemples d'utilisation pour chaque événement
- Guide de création d'Event Listeners
- Exemples concrets : multi-tenancy, validation métier, synchronisation externe, invalidation de cache
- Bonnes pratiques

### Impact des modifications

1. **Rétrocompatibilité** : Les modifications sont rétrocompatibles. Le EventDispatcher est optionnel dans tous les services.

2. **Configuration** : Les utilisateurs peuvent maintenant définir des valeurs par défaut globales dans `config/packages/sigmasoft_data_table.yaml` qui seront automatiquement appliquées à toutes les DataTables.

3. **Extensibilité** : Le système d'événements permet aux développeurs d'étendre le comportement du bundle sans modifier le code source.

### Tests à effectuer

1. Vérifier que la configuration YAML est bien prise en compte
2. Tester les événements PRE_QUERY et POST_QUERY
3. Tester les événements PRE_INLINE_EDIT et POST_INLINE_EDIT
4. Vérifier la validation via événements

## 2025-08-01 - Refactorisation Complète des Templates

### Problème identifié
Le template `datatable.html.twig` était monolithique et difficile à personnaliser. Il n'y avait pas de système de thèmes et la personnalisation nécessitait de réécrire complètement le template.

### Modifications apportées

#### 1. Architecture Modulaire des Templates

##### Template Principal Refactorisé
- **Nouveau** : Architecture basée sur des blocks Twig extensibles
- **Nouveau** : Support des thèmes via configuration (`bootstrap5`, `minimal`, `custom`)
- **Nouveau** : Classes CSS automatiques basées sur le thème et l'entité

##### Blocks Twig Créés
- `datatable_wrapper` : Conteneur principal personnalisable
- `datatable_toolbar` : Barre d'outils (recherche, actions)
- `datatable_table` : Table HTML avec sous-blocks
- `datatable_pagination` : Composant de pagination
- `datatable_styles` : Styles CSS personnalisables
- Plus de 15 blocks spécialisés pour une personnalisation fine

#### 2. Templates Partiels

##### Composants Créés
- `_search.html.twig` : Composant de recherche avec accessibility
- `_items_per_page.html.twig` : Sélecteur d'éléments par page
- `_header_cell.html.twig` : Cellule d'en-tête avec tri
- `_body_cell.html.twig` : Cellule de données
- `_pagination.html.twig` : Pagination complète avec navigation
- `_alerts.html.twig` : Alertes et debug info

##### Avantages
- Réutilisabilité des composants
- Personnalisation granulaire
- Maintenance simplifiée
- Tests unitaires possibles

#### 3. Support des Thèmes

##### Nouvelle Configuration
Ajout dans `DataTableConfiguration.php` :
```php
private string $theme = 'bootstrap5';
private array $paginationSizes = [5, 10, 25, 50, 100];

public function getTheme(): string;
public function setTheme(string $theme): self;
public function getPaginationSizes(): array;
public function setPaginationSizes(array $paginationSizes): self;
```

##### Thèmes Bootstrap 5
- Classes CSS automatiques selon le thème
- Wrapper conditionnel (card pour Bootstrap, simple pour autres)
- Styles CSS intégrés et personnalisables

#### 4. Internationalisation

##### Fichiers de Traduction
- `translations/SigmasoftDataTable.fr.yaml` : Traductions françaises
- `translations/SigmasoftDataTable.en.yaml` : Traductions anglaises
- Support complet de l'accessibility (aria-label, etc.)

##### Clés de Traduction
- `datatable.search.*` : Composant de recherche
- `datatable.pagination.*` : Pagination
- `datatable.sort.*` : Tri des colonnes
- `datatable.actions.*` : Actions des boutons

#### 5. Accessibilité (ARIA)

##### Améliorations
- Attributs `aria-label` sur tous les boutons
- Navigation au clavier pour la pagination
- Rôles ARIA appropriés (`navigation`, `button`, etc.)
- Messages d'état pour les lecteurs d'écran

#### 6. Tests Unitaires

##### Nouvelle Classe de Test
`tests/Template/DataTableTemplateTest.php` :
- Test du rendu du template principal
- Test des composants individuels
- Test de la personnalisation via blocks
- Test du support des thèmes
- Test de l'affichage des données vides
- Test de la surcharge de blocks

##### Couverture
- Rendu avec données
- Rendu sans données
- Pagination
- Recherche
- Tri des colonnes
- Thèmes personnalisés

#### 7. Système d'Installation Automatique

##### Nouvelle Commande
`InstallConfigCommand.php` :
- Installation manuelle de la configuration
- Sauvegarde automatique de l'existant
- Messages d'aide et conseils

##### EventSubscriber Amélioré
`PostInstallSubscriber.php` :
- Copie automatique des templates ET de la configuration
- Installation silencieuse lors de la première requête
- Gestion des erreurs sans bloquer l'application

##### Configuration par Défaut
`config/install/sigmasoft_data_table.yaml` :
- Configuration complète avec tous les paramètres
- Commentaires explicatifs
- Valeurs par défaut cohérentes avec le code

#### 8. Exemples de Personnalisation

##### Templates d'Exemple
- `examples/custom_theme_datatable.html.twig` : Thème personnalisé complet
- `examples/minimal_datatable.html.twig` : Version minimaliste sans Bootstrap

##### Patterns de Personnalisation
- Extension de blocks spécifiques
- Surcharge de styles CSS
- Personnalisation responsive
- Accessibilité maintenue

#### 9. Documentation Complète

##### Nouveau Guide
`docs/developer-guide/template-customization.md` :
- Architecture des templates expliquée
- Liste complète des blocks disponibles
- Exemples de personnalisation
- Bonnes pratiques d'accessibility
- Guide de migration

##### Documentation Mise à Jour
- `docs/installation.md` : Instructions d'installation automatique
- Références aux nouvelles commandes et fonctionnalités

### Impact des modifications

#### Avantages
1. **Personnalisation facilitée** : Blocks Twig permettent la surcharge granulaire
2. **Maintenabilité** : Architecture modulaire avec composants séparés
3. **Accessibilité** : Support complet ARIA et navigation clavier
4. **Internationalisation** : Traductions intégrées en français et anglais
5. **Installation simplifiée** : Configuration automatique lors de l'installation
6. **Tests robustes** : Couverture complète des templates
7. **Documentation complète** : Guides détaillés avec exemples

#### Rétrocompatibilité
- **Maintenue** : Les templates existants continuent de fonctionner
- **Migration douce** : Possibilité de migrer progressivement vers les nouveaux blocks
- **Configuration** : Ancien format de configuration toujours supporté

#### Performance
- **Amélioration** : Templates partiels mis en cache séparément
- **Optimisation** : CSS intégré évite les requêtes supplémentaires
- **Lazy loading** : Composants chargés uniquement si nécessaire

### Tests à effectuer

1. **Templates** : Vérifier le rendu avec différents thèmes
2. **Personnalisation** : Tester la surcharge de blocks
3. **Accessibilité** : Validation avec lecteurs d'écran
4. **Installation** : Test de l'installation automatique
5. **Traductions** : Vérification en français et anglais

## 🔢 Ajout du Support des Colonnes Numériques (NumberColumn)

### Date : 01/08/2025

### Problème identifié
Le bundle ne disposait pas d'un support spécialisé pour l'affichage et l'édition des valeurs numériques avec formatage (séparateurs de milliers, décimales, devises, pourcentages).

### Solution implémentée

#### 1. Création de la classe NumberColumn
**Fichier** : `src/Column/NumberColumn.php`
- Support de 4 formats : `integer`, `decimal`, `currency`, `percentage`
- Configuration des séparateurs de milliers et décimales
- Support de l'extension PHP Intl pour le formatage localisé
- Factory methods statiques pour chaque format
- Gestion des valeurs nulles et zéro
- Validation des paramètres de configuration

#### 2. Renderer pour l'édition inline
**Fichier** : `src/InlineEdit/Renderer/NumberFieldRenderer.php`
- Champ HTML5 `number` avec validation client
- Support des contraintes min/max et step
- Formatage automatique en temps réel
- Validation côté serveur avec gestion des erreurs
- Support des séparateurs de milliers français (espaces)
- Traitement des décimales avec virgule française

#### 3. Intégration dans le Maker Bundle
**Fichiers modifiés** :
- `src/Maker/MakeDataTable.php` : Mapping automatique des types Doctrine `integer`, `float`, `decimal` vers `NumberColumn`
- `src/Builder/DataTableBuilder.php` : Ajout de la méthode `addNumberColumn()`
- `config/install/sigmasoft_data_table.yaml` : Configuration par défaut des types numériques

#### 4. Factory pour colonnes éditables
**Fichier** : `src/Service/EditableColumnFactory.php`
- Méthodes spécialisées : `number()`, `currency()`, `percentage()`, `integer()`
- Configuration automatique des contraintes selon le type
- Intégration avec le système d'édition inline V2

#### 5. Tests complets
**Fichiers de tests** :
- `tests/Column/NumberColumnTest.php` : 34 tests pour tous les formats et options
- `tests/InlineEdit/Renderer/NumberFieldRendererTest.php` : 22 tests pour l'édition inline
- Couverture complète des cas d'usage et d'erreurs

### Configuration automatique

```yaml
# Mapping automatique dans la configuration
maker:
    default_column_types:
        integer: 'number'       # Format integer avec séparateurs
        float: 'number'         # Format decimal avec 2 décimales
        decimal: 'number'       # Format decimal personnalisable
```

### Exemples d'utilisation

#### Utilisation basique
```php
// Via le Builder
$config = $builder->addNumberColumn($config, 'price', 'price', 'Prix', [
    'format' => 'currency',
    'currency' => 'EUR',
    'decimals' => 2
]);

// Via la Factory
$column = NumberColumn::currency('price', 'price', 'Prix', 'EUR');
```

#### Colonne éditable
```php
$column = $editableColumnFactory->currency('price', 'price', 'Prix', 'EUR', 2);
```

### Fonctionnalités clés

1. **Formatage automatique** : 1234.56 → "1 234,56"
2. **Support devise** : 1234.56 → "EUR 1 234,56"
3. **Pourcentages** : 0.15 → "15,0 %"
4. **Édition inline** : Champ numérique avec validation
5. **Intégration Maker** : Détection automatique des types numériques
6. **Localisation** : Support des formats français et internationaux
7. **Validation robuste** : Contraintes min/max, required, step

### Impact des modifications

#### Avantages
1. **Formatage professionnel** : Affichage des nombres selon les standards français
2. **Édition intuitive** : Interface utilisateur optimisée pour la saisie numérique
3. **Validation complète** : Vérification côté client et serveur
4. **Flexibilité** : Support de tous les types de formatage numérique
5. **Génération automatique** : Le Maker détecte et configure automatiquement
6. **Compatibilité** : Fonctionne avec tous les types Doctrine numériques

#### Tests effectués
- ✅ Formatage de base : décimales, séparateurs
- ✅ Formats spécialisés : devise, pourcentage, entier
- ✅ Édition inline avec validation
- ✅ Valeurs nulles et zéro
- ✅ Contraintes min/max
- ✅ Génération automatique via Maker
- ✅ Factory methods et API fluide

### Prochaines étapes recommandées

1. Ajouter des tests unitaires pour le système d'événements
2. Créer des exemples d'Event Listeners dans la documentation
3. Implémenter les événements BULK_ACTION et EXPORT
4. Ajouter des événements PRE_RENDER et POST_RENDER pour le composant DataTable
5. Créer des thèmes additionnels (Tailwind CSS, Material Design)
6. Ajouter un système de preview des thèmes dans la documentation
7. **Documenter les colonnes NumberColumn** : Guide complet avec exemples