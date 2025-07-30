<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\DependencyInjection;

use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Component\DataTableComponent;
use Sigmasoft\DataTableBundle\DataProvider\DataProviderInterface;
use Sigmasoft\DataTableBundle\DataProvider\DoctrineDataProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class SigmasoftDataTableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Register services manually
        $this->registerServices($container, $config);
    }

    private function registerServices(ContainerBuilder $container, array $config): void
    {
        // Register DataProvider with service existence checks
        $container->register(DoctrineDataProvider::class)
            ->setArguments([
                new Reference('doctrine.orm.entity_manager'),
                new Reference('knp_paginator'),
                new Reference('logger', ContainerBuilder::NULL_ON_INVALID_REFERENCE)
            ])
            ->addTag('sigmasoft_datatable.data_provider');

        $container->setAlias(DataProviderInterface::class, DoctrineDataProvider::class);

        // Register DataTableRegistry (singleton)
        $container->register(\Sigmasoft\DataTableBundle\Service\DataTableRegistry::class)
            ->setPublic(false);

        $container->setAlias(\Sigmasoft\DataTableBundle\Service\DataTableRegistryInterface::class, \Sigmasoft\DataTableBundle\Service\DataTableRegistry::class);

        // Register ColumnFactory
        $container->register(\Sigmasoft\DataTableBundle\Service\ColumnFactory::class)
            ->setArguments([
                new Reference('router')
            ])
            ->setPublic(false);

        // Register DataTableConfigResolver
        $container->register(\Sigmasoft\DataTableBundle\Service\DataTableConfigResolver::class)
            ->setArguments([
                new Reference('parameter_bag')
            ])
            ->setPublic(false);

        // Register DataTableBuilder
        $container->register(DataTableBuilder::class)
            ->setArguments([
                new Reference('router'),
                new Reference(\Sigmasoft\DataTableBundle\Service\DataTableConfigResolver::class)
            ])
            ->setPublic(true);

        // Register DataTableFactory
        $container->register(\Sigmasoft\DataTableBundle\Factory\DataTableFactory::class)
            ->setArguments([
                new Reference(DataTableBuilder::class),
                $config['defaults']
            ])
            ->setPublic(true);

        // Register DataTableComponent
        $container->register(DataTableComponent::class)
            ->setArguments([
                new Reference(DataProviderInterface::class),
                new Reference('doctrine.orm.entity_manager'),
                new Reference(\Sigmasoft\DataTableBundle\Service\ColumnFactory::class),
                new Reference(\Sigmasoft\DataTableBundle\Service\DataTableRegistryInterface::class),
                new Reference('logger', ContainerBuilder::NULL_ON_INVALID_REFERENCE)
            ])
            ->addTag('twig.component', ['key' => 'sigmasoft_datatable'])
            ->addTag('controller.service_arguments');

        // Register ExportService
        $container->register(\Sigmasoft\DataTableBundle\Service\ExportService::class)
            ->setArguments([
                new Reference(DataProviderInterface::class)
            ])
            ->setPublic(true);

        // Register InlineEditService
        $container->register(\Sigmasoft\DataTableBundle\Service\InlineEditService::class)
            ->setArguments([
                new Reference('doctrine.orm.entity_manager'),
                new Reference('validator'),
                new Reference('logger', ContainerBuilder::NULL_ON_INVALID_REFERENCE)
            ])
            ->setPublic(true);

        // Register InlineEditServiceV2 (nouvelle version)
        $container->register(\Sigmasoft\DataTableBundle\Service\InlineEditServiceV2::class)
            ->setArguments([
                new Reference('doctrine.orm.entity_manager'),
                new Reference('validator'),
                new Reference('security.helper', ContainerBuilder::NULL_ON_INVALID_REFERENCE),
                new Reference('logger', ContainerBuilder::NULL_ON_INVALID_REFERENCE)
            ])
            ->setPublic(true);

        // Register Field Renderers
        $container->register(\Sigmasoft\DataTableBundle\InlineEdit\Renderer\TextFieldRenderer::class)
            ->setArguments([
                new Reference('property_accessor')
            ])
            ->addTag('sigmasoft_datatable.field_renderer');

        $container->register(\Sigmasoft\DataTableBundle\InlineEdit\Renderer\SelectFieldRenderer::class)
            ->setArguments([
                new Reference('property_accessor')
            ])
            ->addTag('sigmasoft_datatable.field_renderer');

        $container->register(\Sigmasoft\DataTableBundle\InlineEdit\Renderer\TextAreaFieldRenderer::class)
            ->setArguments([
                new Reference('property_accessor')
            ])
            ->addTag('sigmasoft_datatable.field_renderer');

        $container->register(\Sigmasoft\DataTableBundle\InlineEdit\Renderer\ColorFieldRenderer::class)
            ->setArguments([
                new Reference('property_accessor')
            ])
            ->addTag('sigmasoft_datatable.field_renderer');

        // Register FieldRendererRegistry
        $container->register(\Sigmasoft\DataTableBundle\InlineEdit\Renderer\FieldRendererRegistry::class)
            ->setArguments([
                [] // Will be populated by compiler pass
            ])
            ->setPublic(false);

        // Register EditableColumnFactory
        $container->register(\Sigmasoft\DataTableBundle\Service\EditableColumnFactory::class)
            ->setArguments([
                new Reference(\Sigmasoft\DataTableBundle\InlineEdit\Renderer\FieldRendererRegistry::class)
            ])
            ->setPublic(true);

        // Register MakeDataTable command
        $container->register(\Sigmasoft\DataTableBundle\Maker\MakeDataTable::class)
            ->setArguments([
                new Reference('doctrine.orm.entity_manager'),
                new Reference('parameter_bag'),
                $config
            ])
            ->addTag('maker.command');

        // Set configuration parameters
        $container->setParameter('sigmasoft_data_table.config', $config);
        $container->setParameter('sigmasoft_data_table.defaults', $config['defaults']);
        $container->setParameter('sigmasoft_data_table.templates', $config['templates']);
        $container->setParameter('sigmasoft_data_table.caching', $config['caching']);
        $container->setParameter('sigmasoft_data_table.maker', $config['maker']);
    }

    public function getAlias(): string
    {
        return 'sigmasoft_data_table';
    }
}
