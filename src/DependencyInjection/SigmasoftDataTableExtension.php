<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SigmasoftDataTableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        // Définition des paramètres de configuration
        $container->setParameter('sigmasoft_data_table.global_config', $config['global_config'] ?? []);
        $container->setParameter('sigmasoft_data_table.entities', $config['entities'] ?? []);
        $container->setParameter('sigmasoft_data_table.templates', $config['templates'] ?? []);

        // Configuration automatique des composants Twig
        $this->configureTwigComponents($container);
        
        // Configuration conditionnelle de la commande Maker
        $this->configureMakerCommand($container);
    }

    private function configureTwigComponents(ContainerBuilder $container): void
    {
        // Enregistrer automatiquement le namespace des composants Twig
        if ($container->hasExtension('twig_component')) {
            $twigComponentConfig = $container->getExtensionConfig('twig_component');
            
            // Ajouter le namespace Sigmasoft\DataTableBundle aux defaults
            $newConfig = [
                'defaults' => [
                    'Sigmasoft\\DataTableBundle\\Twig\\Components\\' => [
                        'name_prefix' => 'Sigmasoft'
                    ]
                ]
            ];
            
            $container->prependExtensionConfig('twig_component', $newConfig);
        }
    }

    private function configureMakerCommand(ContainerBuilder $container): void
    {
        // Enregistrer la commande Maker seulement si MakerBundle est disponible
        if (class_exists('Symfony\Bundle\MakerBundle\MakerBundle')) {
            $definition = $container->register('sigmasoft_data_table.maker.make_data_table', 'Sigmasoft\DataTableBundle\Maker\MakeDataTable')
                ->setArguments(['@doctrine.orm.entity_manager'])
                ->addTag('maker.command')
                ->setAutoconfigured(true);
        }
    }
}