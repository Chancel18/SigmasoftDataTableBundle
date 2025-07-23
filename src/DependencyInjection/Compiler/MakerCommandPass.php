<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakerCommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Si MakerBundle n'est pas disponible, supprimer le service MakeDataTable
        if (!class_exists('Symfony\Bundle\MakerBundle\MakerBundle')) {
            if ($container->hasDefinition('Sigmasoft\DataTableBundle\Maker\MakeDataTable')) {
                $container->removeDefinition('Sigmasoft\DataTableBundle\Maker\MakeDataTable');
            }
        }
    }
}