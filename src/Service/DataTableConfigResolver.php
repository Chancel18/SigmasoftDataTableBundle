<?php

namespace Sigmasoft\DataTableBundle\Service;

use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Configuration\ConfiguratorInterface;
use Sigmasoft\DataTableBundle\Configuration\PhpConfigurator;
use Sigmasoft\DataTableBundle\Configuration\YamlConfigurator;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfigurationInterface;
use Sigmasoft\DataTableBundle\Exception\DataTableException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DataTableConfigResolver
{
    private ParameterBagInterface $parameterBag;
    private array $registeredConfigurations = [];
    private array $bundleConfig = [];
    
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        
        // Charger la configuration du bundle si elle existe
        if ($this->parameterBag->has('sigmasoft_data_table.config')) {
            $this->bundleConfig = $this->parameterBag->get('sigmasoft_data_table.config');
        }
    }
    
    public function registerConfiguration(string $entity, DataTableConfigurationInterface $configuration): void
    {
        $this->registeredConfigurations[$entity] = $configuration;
    }
    
    public function resolveConfiguration(string $entity, DataTableBuilder $builder): void
    {
        $configurator = $this->getConfigurator($entity);
        
        if ($configurator) {
            $configurator->configure($builder);
        }
    }
    
    private function getConfigurator(string $entity): ?ConfiguratorInterface
    {
        if (isset($this->registeredConfigurations[$entity])) {
            return new PhpConfigurator($this->registeredConfigurations[$entity]);
        }
        
        $yamlConfigPath = $this->getYamlConfigPath($entity);
        if ($yamlConfigPath && file_exists($yamlConfigPath)) {
            return YamlConfigurator::fromFile($yamlConfigPath);
        }
        
        return null;
    }
    
    private function getYamlConfigPath(string $entity): ?string
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $entityName = $this->getEntityShortName($entity);
        
        $possiblePaths = [
            sprintf('%s/config/datatable/%s.yaml', $projectDir, strtolower($entityName)),
            sprintf('%s/config/datatable/%s.yml', $projectDir, strtolower($entityName)),
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    private function getEntityShortName(string $entityClass): string
    {
        $parts = explode('\\', $entityClass);
        return end($parts);
    }
    
    public function getGlobalDefaults(): array
    {
        // D'abord, vérifier la configuration du bundle
        if (!empty($this->bundleConfig['defaults'])) {
            return $this->bundleConfig['defaults'];
        }
        
        // Ensuite, vérifier le fichier global.yaml
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $globalPath = sprintf('%s/config/datatable/global.yaml', $projectDir);
        
        if (file_exists($globalPath)) {
            $config = \Symfony\Component\Yaml\Yaml::parseFile($globalPath);
            return $config['datatable']['defaults'] ?? [];
        }
        
        return [];
    }
}
