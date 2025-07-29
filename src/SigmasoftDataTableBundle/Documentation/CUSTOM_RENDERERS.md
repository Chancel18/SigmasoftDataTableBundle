# Guide : Créer des Renderers Personnalisés

## Vue d'ensemble

L'architecture modulaire du SigmasoftDataTableBundle permet d'ajouter facilement des types de colonnes personnalisés grâce au système de renderers extensible basé sur le **Strategy Pattern**.

## Architecture

### 1. Interface FieldRendererInterface

Tous les renderers doivent implémenter cette interface :

```php
namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

interface FieldRendererInterface
{
    /**
     * Vérifie si ce renderer peut gérer le type de configuration donné
     */
    public function supports(EditableFieldConfiguration $config): bool;
    
    /**
     * Génère le HTML pour le champ éditable
     */
    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string;
    
    /**
     * Priorité du renderer (plus élevé = plus prioritaire)
     */
    public function getPriority(): int;
}
```

### 2. Classe AbstractFieldRenderer

Une classe de base est fournie pour simplifier la création de nouveaux renderers :

```php
abstract class AbstractFieldRenderer implements FieldRendererInterface
{
    protected function generateBaseAttributes(...): array;
    protected function buildAttributesString(array $attributes): string;
    protected function escapeValue(mixed $value): string;
    protected function wrapWithIndicators(string $html): string;
}
```

## Exemple Complet : ColorFieldRenderer

### 1. Créer le Renderer

```php
<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

class ColorFieldRenderer extends AbstractFieldRenderer
{
    public const FIELD_TYPE_COLOR = 'color';
    
    private const SUPPORTED_TYPES = [self::FIELD_TYPE_COLOR];
    
    // Couleurs prédéfinies
    private const PRESET_COLORS = [
        '#FF0000' => 'Rouge',
        '#00FF00' => 'Vert', 
        '#0000FF' => 'Bleu',
        // ... autres couleurs
    ];

    public function supports(EditableFieldConfiguration $config): bool
    {
        return in_array($config->getFieldType(), self::SUPPORTED_TYPES, true);
    }

    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        $attributes = $this->generateBaseAttributes($config, $value, $entity, $fieldName);
        
        // Normaliser la couleur
        $colorValue = $this->normalizeColorValue($value) ?: '#000000';
        $attributes['value'] = $colorValue;
        $attributes['type'] = 'color';
        $attributes['class'] .= ' color-picker-field';
        
        // Construire le HTML avec preview et presets
        $html = '<div class="color-field-wrapper d-flex align-items-center gap-2">';
        $html .= sprintf('<input %s>', $this->buildAttributesString($attributes));
        $html .= $this->renderColorPreview($colorValue);
        
        if ($config->getOption('show_presets', true)) {
            $html .= $this->renderColorPresets($attributes['data-entity-id'], $attributes['data-field-name']);
        }
        
        $html .= '</div>';
        
        return $this->wrapWithIndicators($html);
    }
    
    public function getPriority(): int
    {
        return 100; // Priorité élevée
    }
    
    private function normalizeColorValue(mixed $value): ?string
    {
        // Logique de normalisation des couleurs
        // (convertir noms vers hex, ajouter #, etc.)
    }
    
    private function renderColorPreview(string $color): string
    {
        return sprintf(
            '<div class="color-preview" style="width: 30px; height: 30px; border: 2px solid #dee2e6; border-radius: 4px; background-color: %s; cursor: pointer;"></div>',
            $this->escapeValue($color)
        );
    }
    
    private function renderColorPresets(string $entityId, string $fieldName): string
    {
        // Génère un dropdown avec les couleurs prédéfinies
        $html = '<div class="color-presets dropdown">';
        $html .= '<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">';
        $html .= '<i class="fas fa-palette"></i></button>';
        
        $html .= '<div class="dropdown-menu p-2">';
        foreach (self::PRESET_COLORS as $color => $name) {
            $html .= sprintf(
                '<button type="button" class="preset-color-btn" style="background-color: %s;" data-color="%s" data-entity-id="%s" data-field-name="%s" title="%s"></button>',
                $color, $color, $entityId, $fieldName, $name
            );
        }
        $html .= '</div></div>';
        
        return $html;
    }
}
```

### 2. Ajouter le Type à la Configuration

```php
// Dans EditableFieldConfiguration.php
public const FIELD_TYPE_COLOR = 'color';

private array $validFieldTypes = [
    // ... autres types
    self::FIELD_TYPE_COLOR,
];
```

### 3. Ajouter la Méthode Factory

```php
// Dans EditableColumnFactory.php
public function color(
    string $name,
    string $propertyPath,
    string $label = '',
    bool $showPresets = true
): EditableColumnV2 {
    $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR)
        ->dataAttributes(['show_presets' => $showPresets]);
    return new EditableColumnV2($name, $propertyPath, $label, $config, true, true, [], $this->rendererRegistry);
}
```

### 4. Enregistrer le Service

```php
// Dans SigmasoftDataTableExtension.php
$container->register(\Sigmasoft\DataTableBundle\InlineEdit\Renderer\ColorFieldRenderer::class)
    ->setArguments([new Reference('property_accessor')])
    ->addTag('sigmasoft_datatable.field_renderer');
```

### 5. Utiliser dans un Contrôleur

```php
// Dans un contrôleur
->addColumn(
    $editableColumnFactory->color('preferred_color', 'preferredColor', 'Couleur favorite')
)
```

## Fonctionnalités Avancées

### 1. Validation Personnalisée

```php
public function validateValue(mixed $value, EditableFieldConfiguration $config): array
{
    $errors = [];
    
    if (!$value && $config->isRequired()) {
        $errors[] = 'Une couleur est requise';
        return $errors;
    }
    
    if ($value) {
        $normalizedValue = $this->normalizeColorValue($value);
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $normalizedValue)) {
            $errors[] = 'Format de couleur invalide';
        }
    }
    
    return $errors;
}
```

### 2. Support JavaScript

```javascript
// Gestion des couleurs prédéfinies
handleColorPresetClick(event) {
    if (!event.target.classList.contains('preset-color-btn')) return;
    
    const color = event.target.dataset.color;
    const field = document.querySelector(`[data-entity-id="${event.target.dataset.entityId}"][data-field-name="${event.target.dataset.fieldName}"]`);
    
    if (field) {
        field.value = color;
        this.updateColorPreview(field);
        this.scheduleFieldSave(field, 100);
    }
}

updateColorPreview(field) {
    const preview = field.closest('.color-field-wrapper')?.querySelector('.color-preview');
    if (preview && /^#[0-9A-Fa-f]{6}$/.test(field.value)) {
        preview.style.backgroundColor = field.value;
    }
}
```

### 3. Styles CSS

```css
.color-field-wrapper {
    align-items: center;
}

.color-picker-field {
    width: 60px !important;
    height: 35px;
    padding: 0;
    border-radius: 4px;
    cursor: pointer;
}

.color-preview {
    transition: all 0.2s ease;
}

.color-preview:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.preset-color-btn {
    width: 40px;
    height: 30px;
    border: 2px solid #dee2e6;
    cursor: pointer;
    transition: transform 0.15s ease;
}

.preset-color-btn:hover {
    transform: scale(1.1);
}
```

## Autres Exemples de Renderers

### DatePickerRenderer

```php
class DatePickerRenderer extends AbstractFieldRenderer
{
    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->getFieldType() === 'datepicker';
    }
    
    public function render(...): string
    {
        // Intégration avec une lib comme Flatpickr
        $html = sprintf('<input %s class="flatpickr-input">', $this->buildAttributesString($attributes));
        return $this->wrapWithIndicators($html);
    }
}
```

### RichTextRenderer

```php
class RichTextRenderer extends AbstractFieldRenderer
{
    public function supports(EditableFieldConfiguration $config): bool
    {
        return $config->getFieldType() === 'richtext';
    }
    
    public function render(...): string
    {
        // Intégration avec TinyMCE ou CKEditor
        $html = sprintf('<div class="tinymce-wrapper"><textarea %s></textarea></div>', $attributes);
        return $this->wrapWithIndicators($html);
    }
}
```

### FileUploadRenderer

```php
class FileUploadRenderer extends AbstractFieldRenderer
{
    public function render(...): string
    {
        // Upload avec preview
        $html = '<div class="file-upload-wrapper">';
        $html .= '<input type="file" ...>';
        $html .= '<div class="file-preview"></div>';
        $html .= '</div>';
        return $this->wrapWithIndicators($html);
    }
}
```

## Bonnes Pratiques

### 1. Performance
- Utilisez des priorités appropriées
- Minimisez les dépendances externes
- Lazy loading pour les libs JS lourdes

### 2. Sécurité
- Toujours échapper les valeurs avec `escapeValue()`
- Valider les entrées utilisateur
- Utiliser les data-attributes pour les métadonnées

### 3. Accessibilité
- Attributs `aria-*` appropriés
- Support clavier
- Labels et descriptions

### 4. Tests
```php
class ColorFieldRendererTest extends TestCase
{
    public function testSupports(): void
    {
        $renderer = new ColorFieldRenderer($this->createMock(PropertyAccessorInterface::class));
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR);
        
        $this->assertTrue($renderer->supports($config));
    }
    
    public function testRender(): void
    {
        // Test du rendu HTML
    }
    
    public function testValidation(): void
    {
        // Test de la validation
    }
}
```

## Résumé

L'architecture modulaire permet de créer facilement des renderers personnalisés en :

1. **Étendant** `AbstractFieldRenderer`
2. **Implémentant** les méthodes requises (`supports`, `render`, `getPriority`)
3. **Enregistrant** le service avec le tag `sigmasoft_datatable.field_renderer`
4. **Ajoutant** une méthode factory si nécessaire
5. **Incluant** le JavaScript et CSS associés

Cette approche garantit la maintenabilité, la testabilité et l'extensibilité du système.
