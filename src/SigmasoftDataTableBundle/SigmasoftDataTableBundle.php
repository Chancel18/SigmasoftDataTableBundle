<?php

/**
 * SigmasoftDataTableBundle - Bundle Symfony pour tables de données interactives
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package Sigmasoft\DataTableBundle
 */

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle;

use Sigmasoft\DataTableBundle\DependencyInjection\Compiler\FieldRendererPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SigmasoftDataTableBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        
        $container->addCompilerPass(new FieldRendererPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__, 1) . '/SigmasoftDataTableBundle';
    }
}

