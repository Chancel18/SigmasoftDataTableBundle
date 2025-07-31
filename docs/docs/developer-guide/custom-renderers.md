---
sidebar_position: 2
title: Renderers Personnalisés
description: Créer des renderers personnalisés pour l'édition inline
---

# Renderers Personnalisés

Les renderers personnalisés permettent d'étendre les capacités d'édition inline du SigmasoftDataTableBundle en créant vos propres types de champs éditables.

## Architecture des Renderers

Le système de renderers utilise le **Strategy Pattern** pour permettre une extensibilité maximale :

```
FieldRendererInterface
    ├── AbstractFieldRenderer
    │   ├── TextFieldRenderer
    │   ├── SelectFieldRenderer
    │   ├── TextAreaFieldRenderer
    │   ├── ColorFieldRenderer
    │   └── VotreRendererPersonnalise
    └── FieldRendererRegistry
```

## Création d'un Renderer Personnalisé

### 1. Implémenter l'interface

```php
<?php

namespace App\DataTable\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Renderer\FieldRendererInterface;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

class DatePickerFieldRenderer implements FieldRendererInterface
{
    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->getFieldType() === 'datepicker';
    }

    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        $attributes = $this->buildAttributes($config, $entity, $fieldName);
        $formattedValue = $value instanceof \DateTimeInterface 
            ? $value->format('Y-m-d') 
            : '';

        return sprintf(
            '<input type="date" class="form-control datepicker-field" 
                    data-entity-id="%s" 
                    data-field-name="%s" 
                    value="%s" %s>',
            $entity->getId(),
            $fieldName,
            htmlspecialchars($formattedValue),
            $attributes
        );
    }

    public function renderStatic(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity
    ): string {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }
        return '-';
    }

    public function validateValue(mixed $value, EditableFieldConfiguration $config): array
    {
        $errors = [];
        
        if ($config->isRequired() && empty($value)) {
            $errors[] = 'Ce champ est requis';
        }
        
        if (!empty($value)) {
            $date = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $errors[] = 'Format de date invalide';
            }
        }
        
        return $errors;
    }

    public function normalizeValue(mixed $value, EditableFieldConfiguration $config): mixed
    {
        if (empty($value)) {
            return null;
        }
        
        return \DateTime::createFromFormat('Y-m-d', $value);
    }

    private function buildAttributes(
        EditableFieldConfiguration $config,
        object $entity,
        string $fieldName
    ): string {
        $attributes = [];
        
        // Attributs de validation
        if ($config->isRequired()) {
            $attributes[] = 'required';
        }
        
        // Attributs data personnalisés
        foreach ($config->getDataAttributes() as $key => $value) {
            $attributes[] = sprintf('data-%s="%s"', $key, htmlspecialchars($value));
        }
        
        // Min/Max dates si configurées
        if ($minDate = $config->getOption('min_date')) {
            $attributes[] = sprintf('min="%s"', $minDate);
        }
        
        if ($maxDate = $config->getOption('max_date')) {
            $attributes[] = sprintf('max="%s"', $maxDate);
        }
        
        return implode(' ', $attributes);
    }
}
```

### 2. Étendre AbstractFieldRenderer

Pour simplifier l'implémentation, étendez `AbstractFieldRenderer` :

```php
<?php

namespace App\DataTable\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Renderer\AbstractFieldRenderer;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

class RatingFieldRenderer extends AbstractFieldRenderer
{
    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->getFieldType() === 'rating';
    }

    protected function doRender(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        $maxStars = $config->getOption('max_stars', 5);
        $currentRating = (int) $value;
        
        $html = '<div class="rating-field" data-entity-id="' . $entity->getId() . '" 
                      data-field-name="' . $fieldName . '" 
                      data-current-value="' . $currentRating . '">';
        
        for ($i = 1; $i <= $maxStars; $i++) {
            $class = $i <= $currentRating ? 'star-filled' : 'star-empty';
            $html .= sprintf(
                '<span class="star %s" data-rating="%d">★</span>',
                $class,
                $i
            );
        }
        
        $html .= '</div>';
        
        return $html;
    }

    public function renderStatic(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity
    ): string {
        $maxStars = $config->getOption('max_stars', 5);
        $rating = (int) $value;
        
        return str_repeat('★', $rating) . str_repeat('☆', $maxStars - $rating);
    }

    public function validateValue(mixed $value, EditableFieldConfiguration $config): array
    {
        $errors = parent::validateValue($value, $config);
        
        $maxStars = $config->getOption('max_stars', 5);
        $rating = (int) $value;
        
        if ($rating < 0 || $rating > $maxStars) {
            $errors[] = sprintf('La note doit être entre 0 et %d', $maxStars);
        }
        
        return $errors;
    }
}
```

### 3. Enregistrer le Renderer

Les renderers sont automatiquement enregistrés grâce au **CompilerPass**. Il suffit de taguer votre service :

```yaml
# config/services.yaml
services:
    App\DataTable\Renderer\DatePickerFieldRenderer:
        tags:
            - { name: sigmasoft.field_renderer }
            
    App\DataTable\Renderer\RatingFieldRenderer:
        tags:
            - { name: sigmasoft.field_renderer }
```

Ou avec l'auto-configuration :

```yaml
# config/services.yaml
services:
    _instanceof:
        Sigmasoft\DataTableBundle\InlineEdit\Renderer\FieldRendererInterface:
            tags: ['sigmasoft.field_renderer']
```

## Utilisation du Renderer

### Dans le contrôleur

```php
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

// Configuration pour le DatePicker
$dateConfig = EditableFieldConfiguration::create('datepicker')
    ->required(true)
    ->options([
        'min_date' => '2020-01-01',
        'max_date' => '2025-12-31'
    ]);

$column = $editableColumnFactory->create(
    'delivery_date',
    'deliveryDate',
    'Date de livraison',
    $dateConfig
);

// Configuration pour le Rating
$ratingConfig = EditableFieldConfiguration::create('rating')
    ->options(['max_stars' => 10])
    ->validationRules(['min' => 0, 'max' => 10]);

$ratingColumn = $editableColumnFactory->create(
    'customer_rating',
    'customerRating',
    'Note client',
    $ratingConfig
);
```

### JavaScript associé

```javascript
// assets/js/renderers/rating-field.js
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des clics sur les étoiles
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('star')) {
            const rating = e.target.dataset.rating;
            const container = e.target.closest('.rating-field');
            const entityId = container.dataset.entityId;
            const fieldName = container.dataset.fieldName;
            
            // Mettre à jour l'affichage
            updateStarDisplay(container, rating);
            
            // Sauvegarder via l'API
            saveRating(entityId, fieldName, rating);
        }
    });
    
    function updateStarDisplay(container, rating) {
        const stars = container.querySelectorAll('.star');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('star-filled');
                star.classList.remove('star-empty');
            } else {
                star.classList.remove('star-filled');
                star.classList.add('star-empty');
            }
        });
    }
    
    function saveRating(entityId, fieldName, value) {
        // Utiliser l'API d'édition inline
        fetch(`/inline-edit/${entityId}/${fieldName}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ value: value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Note enregistrée', 'success');
            } else {
                showNotification('Erreur: ' + data.message, 'error');
            }
        });
    }
});
```

### Styles CSS

```css
/* assets/css/renderers/rating-field.css */
.rating-field {
    display: inline-flex;
    gap: 5px;
    font-size: 24px;
}

.star {
    cursor: pointer;
    transition: color 0.2s ease;
    user-select: none;
}

.star:hover {
    color: #ffd700;
    transform: scale(1.1);
}

.star-filled {
    color: #ffd700;
}

.star-empty {
    color: #ddd;
}

/* Animation de sauvegarde */
.rating-field.saving {
    opacity: 0.6;
    pointer-events: none;
}

.rating-field.saving::after {
    content: '⌛';
    margin-left: 10px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
```

## Renderer avec Composant Complexe

Pour des composants plus complexes, vous pouvez utiliser des templates Twig :

```php
<?php

namespace App\DataTable\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Renderer\AbstractFieldRenderer;
use Twig\Environment;

class ImageUploadFieldRenderer extends AbstractFieldRenderer
{
    public function __construct(
        private Environment $twig
    ) {}

    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->getFieldType() === 'image_upload';
    }

    protected function doRender(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        return $this->twig->render('renderers/image_upload.html.twig', [
            'entity' => $entity,
            'field_name' => $fieldName,
            'current_image' => $value,
            'config' => $config,
            'allowed_extensions' => $config->getOption('allowed_extensions', ['jpg', 'png', 'gif']),
            'max_size' => $config->getOption('max_size', 5 * 1024 * 1024) // 5MB
        ]);
    }
}
```

Template Twig associé :

```twig
{# templates/renderers/image_upload.html.twig #}
<div class="image-upload-field" 
     data-entity-id="{{ entity.id }}" 
     data-field-name="{{ field_name }}">
    
    {% if current_image %}
        <div class="current-image">
            <img src="{{ asset(current_image) }}" alt="Image actuelle" class="img-thumbnail">
            <button type="button" class="btn btn-sm btn-danger remove-image">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    {% endif %}
    
    <div class="upload-zone">
        <input type="file" 
               class="d-none image-input" 
               accept="{{ allowed_extensions|map(ext => '.' ~ ext)|join(',') }}"
               data-max-size="{{ max_size }}">
        
        <button type="button" class="btn btn-primary btn-sm upload-trigger">
            <i class="bi bi-upload"></i> Choisir une image
        </button>
        
        <div class="upload-progress d-none">
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
    </div>
    
    <small class="text-muted">
        Formats acceptés : {{ allowed_extensions|join(', ') }} | 
        Taille max : {{ (max_size / 1024 / 1024)|number_format(1) }} MB
    </small>
</div>
```

## Tests des Renderers

```php
<?php

namespace App\Tests\DataTable\Renderer;

use App\DataTable\Renderer\RatingFieldRenderer;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

class RatingFieldRendererTest extends TestCase
{
    private RatingFieldRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new RatingFieldRenderer();
    }

    public function testSupports(): void
    {
        $config = EditableFieldConfiguration::create('rating');
        $this->assertTrue($this->renderer->supports($config));
        
        $config = EditableFieldConfiguration::create('text');
        $this->assertFalse($this->renderer->supports($config));
    }

    public function testRender(): void
    {
        $config = EditableFieldConfiguration::create('rating')
            ->options(['max_stars' => 5]);
        
        $product = new Product();
        $product->setId(123);
        
        $html = $this->renderer->render($config, 3, $product, 'rating');
        
        $this->assertStringContainsString('data-entity-id="123"', $html);
        $this->assertStringContainsString('data-field-name="rating"', $html);
        $this->assertStringContainsString('data-current-value="3"', $html);
        $this->assertEquals(3, substr_count($html, 'star-filled'));
        $this->assertEquals(2, substr_count($html, 'star-empty'));
    }

    public function testValidation(): void
    {
        $config = EditableFieldConfiguration::create('rating')
            ->options(['max_stars' => 5])
            ->required(true);
        
        // Valeur valide
        $errors = $this->renderer->validateValue(3, $config);
        $this->assertEmpty($errors);
        
        // Valeur trop élevée
        $errors = $this->renderer->validateValue(6, $config);
        $this->assertContains('La note doit être entre 0 et 5', $errors);
        
        // Valeur requise manquante
        $errors = $this->renderer->validateValue('', $config);
        $this->assertContains('Ce champ est requis', $errors);
    }
}
```

## Bonnes Pratiques

### 1. Validation robuste

Toujours valider côté serveur ET côté client :

```php
public function validateValue(mixed $value, EditableFieldConfiguration $config): array
{
    $errors = [];
    
    // Validation de base
    if ($config->isRequired() && empty($value)) {
        $errors[] = 'Ce champ est requis';
    }
    
    // Validation spécifique au type
    // ...
    
    // Validation personnalisée via callback
    if ($validator = $config->getOption('custom_validator')) {
        $customErrors = call_user_func($validator, $value, $config);
        $errors = array_merge($errors, $customErrors);
    }
    
    return $errors;
}
```

### 2. Normalisation des données

Toujours normaliser les données avant la sauvegarde :

```php
public function normalizeValue(mixed $value, EditableFieldConfiguration $config): mixed
{
    // Nettoyer les espaces
    $value = trim($value);
    
    // Conversion de type appropriée
    return $this->convertToExpectedType($value, $config);
}
```

### 3. Accessibilité

Assurez-vous que vos renderers sont accessibles :

```php
protected function doRender(...): string
{
    return sprintf(
        '<input type="text" 
                aria-label="%s" 
                aria-required="%s"
                aria-invalid="%s"
                role="textbox"
                %s>',
        $config->getLabel(),
        $config->isRequired() ? 'true' : 'false',
        $hasErrors ? 'true' : 'false',
        $attributes
    );
}
```

### 4. Performance

Pour les renderers complexes, utilisez la mise en cache :

```php
private array $cache = [];

protected function doRender(...): string
{
    $cacheKey = sprintf('%s-%s-%s', get_class($entity), $entity->getId(), $fieldName);
    
    if (!isset($this->cache[$cacheKey])) {
        $this->cache[$cacheKey] = $this->generateComplexHtml($config, $value, $entity, $fieldName);
    }
    
    return $this->cache[$cacheKey];
}
```

## Exemples Avancés

### Renderer avec Auto-complétion

```php
class AutocompleteFieldRenderer extends AbstractFieldRenderer
{
    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->getFieldType() === 'autocomplete';
    }

    protected function doRender(...): string
    {
        $sourceUrl = $config->getOption('source_url');
        $minLength = $config->getOption('min_length', 2);
        
        return sprintf(
            '<input type="text" 
                   class="form-control autocomplete-field"
                   data-entity-id="%s"
                   data-field-name="%s"
                   data-source-url="%s"
                   data-min-length="%d"
                   value="%s"
                   autocomplete="off">',
            $entity->getId(),
            $fieldName,
            $sourceUrl,
            $minLength,
            htmlspecialchars($value)
        );
    }
}
```

### Renderer avec Validation Asynchrone

```php
class AsyncValidatedFieldRenderer extends AbstractFieldRenderer
{
    protected function doRender(...): string
    {
        $validationUrl = $config->getOption('validation_url');
        
        return sprintf(
            '<input type="text" 
                   class="form-control async-validated"
                   data-entity-id="%s"
                   data-field-name="%s"
                   data-validation-url="%s"
                   data-validation-delay="500"
                   value="%s">
            <div class="validation-feedback"></div>',
            $entity->getId(),
            $fieldName,
            $validationUrl,
            htmlspecialchars($value)
        );
    }
}
```

## Ressources

- [Architecture du bundle](./architecture.md)
- [Configuration avancée](../user-guide/customization.md)
- [API Reference](../api/overview.md)