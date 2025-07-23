<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Sigmasoft\DataTableBundle\DependencyInjection\Compiler\MakerCommandPass;

class SigmasoftDataTableBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('global_config')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('items_per_page')->defaultValue(25)->end()
                        ->booleanNode('enable_search')->defaultValue(true)->end()
                        ->booleanNode('enable_sort')->defaultValue(true)->end()
                        ->booleanNode('enable_pagination')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('entities')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('label')->end()
                            ->integerNode('items_per_page')->end()
                            ->booleanNode('enable_search')->end()
                            ->booleanNode('enable_sort')->end()
                            ->booleanNode('enable_pagination')->end()
                            ->booleanNode('enable_export')->defaultValue(false)->end()
                            ->arrayNode('fields')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('type')->defaultValue('string')->end()
                                        ->scalarNode('label')->end()
                                        ->booleanNode('sortable')->defaultValue(false)->end()
                                        ->booleanNode('searchable')->defaultValue(false)->end()
                                        ->scalarNode('format')->end()
                                        ->integerNode('maxLength')->end()
                                        ->scalarNode('cssClass')->end()
                                        ->scalarNode('width')->end()
                                        ->arrayNode('relation')
                                            ->children()
                                                ->scalarNode('entity')->end()
                                                ->scalarNode('field')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('actions')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('label')->end()
                                        ->scalarNode('icon')->end()
                                        ->scalarNode('variant')->defaultValue('primary')->end()
                                        ->scalarNode('route')->end()
                                        ->booleanNode('confirm')->defaultValue(false)->end()
                                        ->scalarNode('confirmMessage')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('bulk_actions')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('label')->end()
                                        ->scalarNode('icon')->end()
                                        ->scalarNode('variant')->defaultValue('primary')->end()
                                        ->booleanNode('confirm')->defaultValue(false)->end()
                                        ->scalarNode('confirmMessage')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('export')
                                ->children()
                                    ->arrayNode('formats')
                                        ->scalarPrototype()->end()
                                        ->defaultValue(['csv'])
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('realtime')
                                ->children()
                                    ->booleanNode('enabled')->defaultValue(false)->end()
                                    ->booleanNode('auto_refresh')->defaultValue(false)->end()
                                    ->integerNode('refresh_interval')->defaultValue(30000)->end()
                                    ->booleanNode('turbo_streams')->defaultValue(false)->end()
                                    ->booleanNode('mercure')->defaultValue(false)->end()
                                    ->arrayNode('topics')
                                        ->scalarPrototype()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('css_classes')
                                ->children()
                                    ->scalarNode('table')->end()
                                    ->scalarNode('row')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->children()
                        ->scalarNode('base')->defaultValue('@SigmasoftDataTableBundle/components/SigmasoftDataTableComponent.html.twig')->end()
                        ->scalarNode('pagination')->defaultValue('@SigmasoftDataTableBundle/components/datatable/pagination.html.twig')->end()
                        ->scalarNode('search')->defaultValue('@SigmasoftDataTableBundle/components/datatable/search_input.html.twig')->end()
                    ->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
        
        // Configuration des paramètres
        $container->parameters()
            ->set('sigmasoft_data_table.global_config', $config['global_config'] ?? [])
            ->set('sigmasoft_data_table.entities', $config['entities'] ?? [])
            ->set('sigmasoft_data_table.templates', $config['templates'] ?? []);
            
        // Compiler pass pour la commande Maker
        $builder->addCompilerPass(new MakerCommandPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
