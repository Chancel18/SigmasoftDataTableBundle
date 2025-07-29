---
sidebar_position: 1
---

# API Reference - Vue d'ensemble

Référence complète de l'API du SigmasoftDataTableBundle pour développeurs. 🔧

## Architecture Générale

Le SigmasoftDataTableBundle s'organise autour de plusieurs espaces de noms principaux :

```
Sigmasoft\DataTableBundle\
├── Builder\          # API de construction fluide
├── Column\           # Types de colonnes
├── Component\        # Live Components Twig
├── Configuration\    # Configuration et résolution
├── DataProvider\     # Providers de données
├── Exception\        # Exceptions du bundle
├── Factory\          # Factories pour création d'objets
├── InlineEdit\       # Édition inline (Architecture V2)
├── Maker\            # Commande Symfony Maker
└── Service\          # Services principaux
```

## Interfaces Principales

### DataTableConfiguration

Interface centrale pour la configuration des DataTables.

```php
interface DataTableConfigurationInterface
{
    public function getEntityClass(): string;
    public function getLabel(): string;
    public function getColumns(): array;
    public function getActions(): array;
    public function isSearchEnabled(): bool;
    public function isSortEnabled(): bool;
    public function isPaginationEnabled(): bool;
    public function getItemsPerPage(): int;
}
```

### ColumnInterface

Interface pour tous les types de colonnes.

```php
interface ColumnInterface
{
    public function getName(): string;
    public function getLabel(): string;
    public function getProperty(): string;
    public function isSearchable(): bool;
    public function isSortable(): bool;
    public function render(mixed $value, object $entity): string;
}
```

### DataProviderInterface

Interface pour les providers de données.

```php
interface DataProviderInterface
{
    public function getData(
        string $entityClass,
        int $page = 1,
        int $limit = 25,
        ?string $search = null,
        ?string $sort = null,
        ?string $direction = 'asc'
    ): DataTableResultInterface;
}
```

## Classes Principales

### DataTableBuilder

Builder principal pour la configuration des DataTables.

#### Constructeur

```php
public function __construct(
    private UrlGeneratorInterface $urlGenerator,
    private DataTableConfigResolver $configResolver
)
```

#### Méthodes de configuration

```php
// Création d'un DataTable
public function createDataTable(string $entityClass): DataTableConfiguration

// Configuration de base
public function setLabel(string $label): self
public function setDescription(string $description): self
public function setItemsPerPage(int $itemsPerPage): self

// Gestion des colonnes
public function addColumn(ColumnInterface $column): self
public function addTextColumn(string $name, string $property, string $label): self
public function addDateColumn(string $name, string $property, string $label): self
public function addBadgeColumn(string $name, string $property, string $label, array $badges = []): self
public function addActionColumn(string $name, string $label): self

// Configuration de la recherche
public function enableSearch(array $fields = []): self
public function disableSearch(): self

// Configuration du tri
public function enableSort(): self
public function disableSort(): self

// Configuration de la pagination
public function enablePagination(): self
public function disablePagination(): self

// Configuration de l'export
public function enableExport(array $formats = ['csv']): self
public function disableExport(): self
```

### DataTableComponent

Live Component principal pour le rendu des DataTables.

#### Propriétés

```php
#[ExposeInTemplate]
public string $entityClass;

#[ExposeInTemplate]
public int $page = 1;

#[ExposeInTemplate]
public string $search = '';

#[ExposeInTemplate]
public ?string $sort = null;

#[ExposeInTemplate]
public string $direction = 'asc';
```

#### Méthodes Live

```php
#[LiveAction]
public function changePage(int $page): void

#[LiveAction]
public function search(string $query): void

#[LiveAction]
public function sort(string $field, string $direction = 'asc'): void

#[LiveAction]
public function refresh(): void
```

### EditableColumnFactory

Factory pour créer des colonnes éditables.

#### Méthodes de création

```php
// Champ texte éditable
public function text(string $name, string $property, string $label): EditableColumnV2

// Champ email éditable
public function email(string $name, string $property, string $label): EditableColumnV2

// Champ select éditable
public function select(string $name, string $property, string $label, array $choices): EditableColumnV2

// Champ textarea éditable
public function textarea(string $name, string $property, string $label): EditableColumnV2

// Champ couleur éditable
public function color(string $name, string $property, string $label): EditableColumnV2

// Champ numérique éditable
public function number(string $name, string $property, string $label): EditableColumnV2
```

## Services

### InlineEditServiceV2

Service principal pour l'édition inline.

```php
class InlineEditServiceV2
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private FieldRendererRegistry $rendererRegistry,
        private LoggerInterface $logger
    )

    public function updateField(
        object $entity, 
        string $field, 
        mixed $value, 
        EditableFieldConfiguration $config
    ): InlineEditResult

    public function validateField(
        object $entity, 
        string $field, 
        mixed $value, 
        EditableFieldConfiguration $config
    ): ConstraintViolationListInterface
}
```

### DataTableRegistry

Registry pour la gestion des configurations DataTable.

```php
class DataTableRegistry implements DataTableRegistryInterface
{
    public function register(string $entityClass, DataTableConfigurationInterface $configuration): void
    public function get(string $entityClass): ?DataTableConfigurationInterface
    public function has(string $entityClass): bool
    public function all(): array
}
```

## Édition Inline V2

### FieldRendererInterface

Interface pour les renderers de champs.

```php
interface FieldRendererInterface
{
    public function supports(EditableFieldConfiguration $config): bool;
    
    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        array $options = []
    ): string;
    
    public function renderEdit(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        array $options = []
    ): string;
}
```

### AbstractFieldRenderer

Classe de base pour les renderers personnalisés.

```php
abstract class AbstractFieldRenderer implements FieldRendererInterface
{
    abstract public function supports(EditableFieldConfiguration $config): bool;
    
    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        array $options = []
    ): string {
        return $this->renderView($config, $value, $entity, $options);
    }
    
    public function renderEdit(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        array $options = []
    ): string {
        return $this->renderEditForm($config, $value, $entity, $options);
    }
    
    abstract protected function renderView(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        array $options = []
    ): string;
    
    abstract protected function renderEditForm(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        array $options = []
    ): string;
}
```

## Exceptions

### DataTableException

Exception de base du bundle.

```php
class DataTableException extends \Exception
{
    public static function entityNotFound(string $entityClass): self
    public static function columnNotFound(string $columnName): self
    public static function configurationError(string $message): self
    public static function renderingError(string $message, \Throwable $previous = null): self
}
```

### Exemples d'utilisation

```php
// Exception si entité non trouvée
throw DataTableException::entityNotFound('App\Entity\User');

// Exception si colonne non trouvée
throw DataTableException::columnNotFound('nonExistentColumn');

// Exception de configuration
throw DataTableException::configurationError('Invalid sort direction: ' . $direction);
```

## Événements

### DataTableEvents

Constantes des événements disponibles.

```php
final class DataTableEvents
{
    public const PRE_QUERY = 'datatable.pre_query';
    public const POST_QUERY = 'datatable.post_query';
    public const PRE_RENDER = 'datatable.pre_render';
    public const POST_RENDER = 'datatable.post_render';
    public const INLINE_EDIT = 'datatable.inline_edit';
    public const BULK_ACTION = 'datatable.bulk_action';
}
```

### DataTableEvent

Événement principal du bundle.

```php
class DataTableEvent extends Event
{
    public function __construct(
        private string $entityClass,
        private DataTableConfiguration $configuration,
        private array $data = []
    )

    public function getEntityClass(): string
    public function getConfiguration(): DataTableConfiguration
    public function getData(): array
    public function setData(array $data): void
    public function addData(string $key, mixed $value): void
}
```

## Configuration YAML

### Structure complète

```yaml
sigmasoft_data_table:
    # Configuration globale
    defaults:
        items_per_page: 25
        enable_search: true
        enable_sort: true
        enable_pagination: true
        table_class: 'table table-striped table-hover'
        date_format: 'd/m/Y H:i'
        locale: 'fr'
    
    # Templates par défaut
    templates:
        table: '@SigmasoftDataTable/components/table.html.twig'
        pagination: '@SigmasoftDataTable/components/pagination.html.twig'
        search: '@SigmasoftDataTable/components/search.html.twig'
    
    # Configuration par entité
    entities:
        'App\Entity\User':
            label: 'Users'
            items_per_page: 20
            fields:
                id:
                    type: integer
                    label: 'ID'
                    sortable: true
                    searchable: false
                name:
                    type: string
                    label: 'Name'
                    sortable: true
                    searchable: true
                    required: true
                    max_length: 255
            actions:
                view:
                    label: 'View'
                    route: 'user_show'
                    variant: 'info'
```

## Maker Command

### Utilisation

```bash
# Génération basique
php bin/console make:datatable User

# Avec options avancées
php bin/console make:datatable User --controller --with-actions --with-export
```

### Options disponibles

- `--controller` : Génère un contrôleur CRUD complet
- `--with-actions` : Ajoute les actions (voir, éditer, supprimer)
- `--with-export` : Active l'export CSV/Excel
- `--with-bulk` : Active les actions groupées

## Types de Données Supportés

### Types de base

| Type | Description | Exemple |
|------|-------------|---------|
| `string` | Texte simple | 'John Doe' |
| `text` | Texte long | 'Description...' |
| `integer` | Nombre entier | 42 |
| `float` | Nombre décimal | 19.99 |
| `boolean` | Booléen | true/false |
| `date` | Date | '2025-01-15' |
| `datetime` | Date et heure | '2025-01-15 14:30:00' |
| `time` | Heure | '14:30:00' |

### Types avancés

| Type | Description | Configuration |
|------|-------------|---------------|
| `email` | Email avec validation | `validation: [Email: ~]` |
| `url` | URL avec validation | `validation: [Url: ~]` |
| `choice` | Liste de choix | `choices: {key: 'Label'}` |
| `entity` | Relation avec entité | `entity: 'App\Entity\Category'` |
| `collection` | Collection d'entités | `target_entity: 'App\Entity\Tag'` |
| `json` | Données JSON | `json_decode: true` |
| `array` | Tableau PHP | `serialize: json` |

## Validation

### Contraintes supportées

```php
use Symfony\Component\Validator\Constraints as Assert;

// Configuration d'un champ avec validation
$field = $this->editableColumnFactory->text('email', 'email', 'Email')
    ->addConstraint(new Assert\NotBlank())
    ->addConstraint(new Assert\Email())
    ->addConstraint(new Assert\Length(['max' => 255]));
```

### Contraintes disponibles

- `NotBlank` : Champ obligatoire
- `Length` : Longueur min/max
- `Email` : Format email valide
- `Url` : URL valide
- `Regex` : Expression régulière
- `Choice` : Valeur dans une liste
- `Range` : Valeur numérique dans une plage
- `Positive` : Nombre positif
- `Count` : Nombre d'éléments dans une collection

## Sécurité

### Contrôle d'accès

```php
// Vérification des permissions par rôle
$config->setViewRole('ROLE_USER')
       ->setEditRole('ROLE_ADMIN')
       ->setDeleteRole('ROLE_SUPER_ADMIN');

// Vérification par Voter
$config->setViewVoter('user_view')
       ->setEditVoter('user_edit')
       ->setDeleteVoter('user_delete');
```

### Protection CSRF

```php
// Protection CSRF automatique sur l'édition inline
$service->updateField($entity, $field, $value, $config, $csrfToken);
```

## Performance

### Optimisations automatiques

- **Lazy Loading** : Chargement différé des relations
- **Query Optimization** : Optimisation automatique des requêtes Doctrine
- **Caching** : Cache des métadonnées d'entités
- **Pagination** : Limitation automatique des résultats

### Configuration de performance

```yaml
sigmasoft_data_table:
    performance:
        enable_query_cache: true
        cache_ttl: 3600
        max_results: 10000
        lazy_loading: true
```

---

## Support et Ressources

### Documentation Détaillée
- 📖 **Guide Utilisateur** : [Utilisation de base](../user-guide/basic-usage)
- 🎨 **Personnalisation** : [Guide de customisation](../user-guide/customization)
- 🔧 **Guide Développeur** : [Architecture](../developer-guide/architecture)

### Communauté et Support
- 🐛 **Issues GitHub** : [Signaler un problème](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
- 💬 **Discussions** : [Forum communautaire](https://github.com/Chancel18/SigmasoftDataTableBundle/discussions)
- 📧 **Support technique** : [support@sigmasoft-solution.com](mailto:support@sigmasoft-solution.com)

---

*Documentation API rédigée par [Gédéon MAKELA](mailto:g.makela@sigmasoft-solution.com) - [Sigmasoft Solutions](https://sigmasoft-solution.com)*