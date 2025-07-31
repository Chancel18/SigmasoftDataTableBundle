<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\InlineEdit\Renderer;

use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;

/**
 * Interface pour les renderers de champs éditables
 * Permet une architecture modulaire et extensible
 */
interface FieldRendererInterface
{
    /**
     * Teste si ce renderer peut traiter le type de champ donné
     */
    public function supports(EditableFieldConfiguration $config): bool;

    /**
     * Rend le champ éditable
     */
    public function render(
        EditableFieldConfiguration $config,
        mixed $value,
        object $entity,
        string $fieldName
    ): string;

    /**
     * Retourne la priorité de ce renderer (plus élevé = plus prioritaire)
     */
    public function getPriority(): int;
}
