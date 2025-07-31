<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

/**
 * Registry pour les renderers de champs éditables
 * Utilise le pattern Strategy pour une architecture modulaire
 */
class FieldRendererRegistry
{
    /** @var FieldRendererInterface[] */
    private array $renderers = [];

    public function __construct()
    {
        // Enregistrer les renderers par défaut
        $this->registerDefaultRenderers();
    }

    /**
     * Enregistre un renderer
     */
    public function addRenderer(FieldRendererInterface $renderer): void
    {
        $this->renderers[] = $renderer;
        
        // Trier par priorité (plus élevé en premier)
        usort($this->renderers, fn($a, $b) => $b->getPriority() <=> $a->getPriority());
    }

    /**
     * Trouve le renderer approprié pour une configuration donnée
     */
    public function getRenderer(EditableFieldConfiguration $config): FieldRendererInterface
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($config)) {
                return $renderer;
            }
        }

        throw new \InvalidArgumentException(
            sprintf('No renderer found for field type "%s"', $config->getFieldType())
        );
    }

    /**
     * Rend un champ éditable
     */
    public function renderField(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string {
        $renderer = $this->getRenderer($config);
        return $renderer->render($config, $value, $entity, $fieldName);
    }

    /**
     * Enregistre les renderers par défaut
     */
    private function registerDefaultRenderers(): void
    {
        $this->addRenderer(new SelectFieldRenderer());
        $this->addRenderer(new TextAreaFieldRenderer());
        $this->addRenderer(new TextFieldRenderer()); // En dernier (priorité plus faible)
    }

    /**
     * Retourne tous les renderers enregistrés
     */
    public function getRenderers(): array
    {
        return $this->renderers;
    }

    /**
     * Vérifie si un type de champ est supporté
     */
    public function supports(EditableFieldConfiguration $config): bool
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($config)) {
                return true;
            }
        }
        return false;
    }
}
