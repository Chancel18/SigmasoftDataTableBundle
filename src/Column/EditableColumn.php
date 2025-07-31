<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Column;

final class EditableColumn extends AbstractColumn
{
    public function __construct(
        string $name,
        string $propertyPath,
        string $label = '',
        bool $sortable = true,
        bool $searchable = true,
        array $options = []
    ) {
        parent::__construct($name, $propertyPath, $label, $sortable, $searchable, $options);
    }

    protected function doRender(mixed $value, object $entity): string
    {
        $fieldType = $this->getOption('field_type', 'text');
        $required = $this->getOption('required', false);
        $placeholder = $this->getOption('placeholder', '');
        $maxLength = $this->getOption('max_length', 255);
        $validation = $this->getOption('validation', []);
        
        $entityId = $this->propertyAccessor->getValue($entity, 'id');
        $fieldName = $this->name;
        $currentValue = htmlspecialchars((string) $value);
        
        $inputAttributes = [
            'type' => $fieldType,
            'class' => 'form-control form-control-sm editable-field',
            'data-entity-id' => $entityId,
            'data-field-name' => $fieldName,
            'data-original-value' => $currentValue,
            'value' => $currentValue,
            'placeholder' => $placeholder,
        ];
        
        if ($required) {
            $inputAttributes['required'] = 'required';
        }
        
        if ($fieldType === 'text' && $maxLength > 0) {
            $inputAttributes['maxlength'] = $maxLength;
        }
        
        // Validation côté client
        if (isset($validation['pattern'])) {
            $inputAttributes['pattern'] = $validation['pattern'];
        }
        
        if (isset($validation['min'])) {
            $inputAttributes['min'] = $validation['min'];
        }
        
        if (isset($validation['max'])) {
            $inputAttributes['max'] = $validation['max'];
        }
        
        $attributesString = '';
        foreach ($inputAttributes as $attr => $val) {
            $attributesString .= sprintf(' %s="%s"', $attr, htmlspecialchars((string) $val));
        }
        
        $html = '<div class="editable-cell-wrapper position-relative">';
        
        // Select pour les options prédéfinies
        if ($this->hasOption('options')) {
            $options = $this->getOption('options');
            $html .= sprintf('<select%s>', $attributesString);
            
            foreach ($options as $optionValue => $optionLabel) {
                $selected = ($optionValue == $value) ? ' selected' : '';
                $html .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    htmlspecialchars((string) $optionValue),
                    $selected,
                    htmlspecialchars((string) $optionLabel)
                );
            }
            
            $html .= '</select>';
        } else {
            // Input normal
            $html .= sprintf('<input%s>', $attributesString);
        }
        
        // Indicateur de sauvegarde
        $html .= '<div class="saving-indicator d-none position-absolute top-0 end-0 me-1 mt-1">';
        $html .= '<div class="spinner-border spinner-border-sm text-primary" role="status">';
        $html .= '<span class="visually-hidden">Sauvegarde...</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Indicateur d'erreur
        $html .= '<div class="error-indicator d-none position-absolute top-0 end-0 me-1 mt-1">';
        $html .= '<i class="fas fa-exclamation-triangle text-danger" title="Erreur de sauvegarde"></i>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
}
