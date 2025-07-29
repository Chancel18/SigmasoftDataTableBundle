<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\DependencyInjection\Compiler;

use Sigmasoft\DataTableBundle\InlineEdit\Renderer\FieldRendererRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass pour injecter automatiquement tous les field renderers
 * dans le FieldRendererRegistry
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 */
class FieldRendererPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(FieldRendererRegistry::class)) {
            return;
        }

        $definition = $container->findDefinition(FieldRendererRegistry::class);
        $taggedServices = $container->findTaggedServiceIds('sigmasoft_datatable.field_renderer');

        $renderers = [];
        foreach ($taggedServices as $id => $tags) {
            $renderers[] = new Reference($id);
        }

        $definition->setArgument(0, $renderers);
    }
}
