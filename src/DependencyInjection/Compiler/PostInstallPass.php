<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * CompilerPass pour copier automatiquement les templates après l'installation
 * 
 * @author Gédéon Makela <g.makela@sigmasoft-solution.com>
 */
class PostInstallPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Cette méthode est appelée pendant la compilation du container
        // On va vérifier si les templates sont déjà copiés
        $projectDir = $container->getParameter('kernel.project_dir');
        $bundleTemplatesDir = __DIR__ . '/../../../templates/SigmasoftDataTable';
        $targetDir = $projectDir . '/templates/bundles/SigmasoftDataTableBundle';

        // Si le répertoire cible n'existe pas, on le crée et copie les templates
        if (!is_dir($targetDir) && is_dir($bundleTemplatesDir)) {
            $filesystem = new Filesystem();
            
            try {
                $filesystem->mkdir($targetDir);
                $filesystem->mirror($bundleTemplatesDir, $targetDir);
                
                // Log pour information
                if ($container->has('logger')) {
                    $container->get('logger')->info(
                        'SigmasoftDataTableBundle: Templates copied to templates/bundles/SigmasoftDataTableBundle/'
                    );
                }
            } catch (\Exception $e) {
                // En cas d'erreur, on ne fait rien pour ne pas bloquer l'installation
                // L'utilisateur pourra toujours copier manuellement ou utiliser la commande
            }
        }
    }
}