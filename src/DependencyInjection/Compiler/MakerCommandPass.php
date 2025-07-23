<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass pour enregistrer conditionnellement la commande Maker
 */
final class MakerCommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Vérifier si MakerBundle est disponible
        if (!class_exists('Symfony\Bundle\MakerBundle\MakerBundle')) {
            // Supprimer la définition de la commande Maker si MakerBundle n'est pas installé
            if ($container->hasDefinition('Sigmasoft\DataTableBundle\Maker\MakeDataTable')) {
                $container->removeDefinition('Sigmasoft\DataTableBundle\Maker\MakeDataTable');
            }
        }
    }
}