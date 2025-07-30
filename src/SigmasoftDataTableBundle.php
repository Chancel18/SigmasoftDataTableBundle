<?php

/**
 * SigmasoftDataTableBundle - Bundle Symfony pour tables de données interactives
 * 
 * @author Gédéon MAKELA <g.makela@sigmasoft-solution.com>
 * @copyright 2025 Sigmasoft Solutions
 * @license MIT
 * @package Sigmasoft\DataTableBundle
 * @version 2.1.0
 * @link https://github.com/Chancel18/SigmasoftDataTableBundle
 * @support support@sigmasoft-solution.com
 */

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SigmasoftDataTableBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        
        // FieldRendererPass sera ajouté dans une version ultérieure
        // pour éviter les erreurs ClassNotFoundError lors de l'installation
        if (class_exists('Sigmasoft\DataTableBundle\DependencyInjection\Compiler\FieldRendererPass')) {
            $container->addCompilerPass(new \Sigmasoft\DataTableBundle\DependencyInjection\Compiler\FieldRendererPass());
        }
    }

    public function getPath(): string
    {
        return __DIR__ . '/SigmasoftDataTableBundle';
    }
}

