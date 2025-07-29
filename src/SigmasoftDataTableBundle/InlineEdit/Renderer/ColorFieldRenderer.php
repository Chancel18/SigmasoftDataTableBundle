<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

/**
 * Renderer personnalisé pour les champs de couleur avec color picker
 * 
 * Exemple d'extension du système de rendu modulaire
 * Supporte les formats hexadécimaux (#ffffff) et nommés (red, blue, etc.)
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 */
class ColorFieldRenderer extends AbstractFieldRenderer
{
    public const FIELD_TYPE_COLOR = 'color';
    
    private const SUPPORTED_TYPES = [
        self::FIELD_TYPE_COLOR
    ];
    
    // Couleurs prédéfinies populaires
    private const PRESET_COLORS = [
        '#FF0000' => 'Rouge',
        '#00FF00' => 'Vert', 
        '#0000FF' => 'Bleu',
        '#FFFF00' => 'Jaune',
        '#FF00FF' => 'Magenta',
        '#00FFFF' => 'Cyan',
        '#000000' => 'Noir',
        '#FFFFFF' => 'Blanc',
        '#808080' => 'Gris',
        '#FFA500' => 'Orange',
        '#800080' => 'Violet',
        '#008000' => 'Vert foncé'
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
        
        // Valeur par défaut si vide
        $colorValue = $this->normalizeColorValue($value) ?: '#000000';
        $attributes['value'] = $colorValue;
        
        // Attributs spécifiques au color picker
        $attributes['type'] = 'color';
        $attributes['class'] .= ' color-picker-field';
        
        // Validation pattern pour couleurs hex
        if (!$config->getPattern()) {
            $attributes['pattern'] = '^#[0-9A-Fa-f]{6}$';
            $attributes['title'] = 'Format: #RRGGBB (ex: #FF0000)';
        }
        
        // Construire le HTML avec color picker amélioré
        $html = '<div class="color-field-wrapper d-flex align-items-center gap-2">';
        
        // Input color natif
        $html .= sprintf('<input %s>', $this->buildAttributesString($attributes));
        
        // Preview de la couleur
        $html .= sprintf(
            '<div class="color-preview" style="width: 30px; height: 30px; border: 2px solid #dee2e6; border-radius: 4px; background-color: %s; cursor: pointer;" title="Couleur actuelle"></div>',
            $this->escapeValue($colorValue)
        );
        
        // Bouton pour les couleurs prédéfinies (optionnel)
        $dataAttributes = $config->getDataAttributes();
        $showPresets = $dataAttributes['show_presets'] ?? true;
        if ($showPresets) {
            $html .= $this->renderColorPresets((string)$attributes['data-entity-id'], $attributes['data-field-name']);
        }
        
        $html .= '</div>';
        
        return $this->wrapWithIndicators($html);
    }
    
    public function getPriority(): int
    {
        return 100; // Priorité élevée pour ce type spécialisé
    }
    
    /**
     * Normalise la valeur de couleur (conversion des noms vers hex si possible)
     */
    private function normalizeColorValue(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }
        
        $stringValue = (string) $value;
        
        // Si c'est déjà un format hex valide
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $stringValue)) {
            return strtoupper($stringValue);
        }
        
        // Si c'est un format hex sans #
        if (preg_match('/^[0-9A-Fa-f]{6}$/', $stringValue)) {
            return '#' . strtoupper($stringValue);
        }
        
        // Conversion des couleurs nommées communes
        $namedColors = [
            'red' => '#FF0000',
            'green' => '#008000',
            'blue' => '#0000FF',
            'yellow' => '#FFFF00',
            'orange' => '#FFA500',
            'purple' => '#800080',
            'pink' => '#FFC0CB',
            'brown' => '#A52A2A',
            'black' => '#000000',
            'white' => '#FFFFFF',
            'gray' => '#808080',
            'grey' => '#808080'
        ];
        
        $lowerValue = strtolower($stringValue);
        if (isset($namedColors[$lowerValue])) {
            return $namedColors[$lowerValue];
        }
        
        // Si aucune conversion possible, retourner la valeur originale
        return $stringValue;
    }
    
    /**
     * Génère le HTML pour les couleurs prédéfinies
     */
    private function renderColorPresets(string $entityId, string $fieldName): string
    {
        $html = '<div class="color-presets dropdown">';
        $html .= '<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Couleurs prédéfinies">';
        $html .= '<i class="fas fa-palette"></i>';
        $html .= '</button>';
        
        $html .= '<div class="dropdown-menu color-presets-menu p-2" style="min-width: 200px;">';
        $html .= '<div class="d-grid gap-1" style="grid-template-columns: repeat(4, 1fr);">';
        
        foreach (self::PRESET_COLORS as $color => $name) {
            $html .= sprintf(
                '<button type="button" class="btn btn-sm preset-color-btn" style="background-color: %s; border: 2px solid #dee2e6; width: 40px; height: 30px;" data-color="%s" data-entity-id="%s" data-field-name="%s" title="%s"></button>',
                $this->escapeValue($color),
                $this->escapeValue($color),
                $this->escapeValue($entityId),
                $this->escapeValue($fieldName),
                $this->escapeValue($name)
            );
        }
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Validation spécifique pour les couleurs
     */
    public function validateValue(mixed $value, EditableFieldConfiguration $config): array
    {
        $errors = [];
        
        if (!$value && $config->isRequired()) {
            $errors[] = 'Une couleur est requise';
            return $errors;
        }
        
        if ($value) {
            $normalizedValue = $this->normalizeColorValue($value);
            
            // Vérifier le format final
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $normalizedValue)) {
                $errors[] = 'Format de couleur invalide. Utilisez le format #RRGGBB (ex: #FF0000)';
            }
        }
        
        return $errors;
    }
}
