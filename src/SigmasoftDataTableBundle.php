<?php

/**
 * SigmasoftDataTableBundle - Bundle Symfony pour tables de données interactives
 * 
 * @author Gédéon MAKELA <g.makela@sigmasoft-solution.com>
 * @copyright 2025 Sigmasoft Solutions
 * @license MIT
 * @package Sigmasoft\DataTableBundle
 * @version 2.0.5
 * @link https://github.com/Chancel18/SigmasoftDataTableBundle
 * @support support@sigmasoft-solution.com
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

