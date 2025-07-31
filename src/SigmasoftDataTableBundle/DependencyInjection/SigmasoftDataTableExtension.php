<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SigmasoftDataTableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Load services from YAML file
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../../config'));
        $loader->load('services.yaml');

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
