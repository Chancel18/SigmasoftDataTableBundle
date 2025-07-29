<?php

namespace App\Tests\SigmasoftDataTableBundle\Service;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Configuration\AbstractDataTableConfiguration;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Service\DataTableConfigResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DataTableConfigResolverTest extends TestCase
{
    private DataTableConfigResolver $resolver;
    private ParameterBagInterface $parameterBag;
    private DataTableBuilder $builder;
    private string $tempDir;
    
    protected function setUp(): void
    {
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->resolver = new DataTableConfigResolver($this->parameterBag);
        
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->builder = new DataTableBuilder($urlGenerator, $this->resolver);
        
        // Créer un répertoire temporaire pour les tests
        $this->tempDir = sys_get_temp_dir() . '/datatable_config_test_' . uniqid();
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/config');
        mkdir($this->tempDir . '/config/datatable');
        
        $this->parameterBag
            ->method('get')
            ->with('kernel.project_dir')
            ->willReturn($this->tempDir);
    }
    
    protected function tearDown(): void
    {
        // Nettoyer les fichiers temporaires
        $this->removeDirectory($this->tempDir);
    }
    
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    public function testRegisterConfiguration(): void
    {
        $config = new class extends AbstractDataTableConfiguration {
            public function getEntityClass(): string
            {
                return User::class;
            }
            
            public function configure(): void
            {
                $this->addColumn(new TextColumn('name', 'name', 'Nom'));
            }
        };
        
        $this->resolver->registerConfiguration(User::class, $config);
        
        $dataTableConfig = new DataTableConfiguration(User::class);
        $this->builder->setCurrentConfig($dataTableConfig);
        
        $this->resolver->resolveConfiguration(User::class, $this->builder);
        
        $columns = $dataTableConfig->getColumns();
        $this->assertCount(1, $columns);
        $this->assertArrayHasKey('name', $columns);
    }
    
    public function testResolveConfigurationFromYamlFile(): void
    {
        // Créer un fichier YAML de test
        $yamlContent = <<<YAML
datatable:
  entity: App\Entity\User
  
  columns:
    - field: name
      property: name
      label: Nom
      type: text
      
    - field: email
      property: email
      label: Email
      type: text
      
  search:
    enabled: true
    fields: [name, email]
    
  pagination:
    enabled: true
    items_per_page: 20
YAML;
        
        file_put_contents($this->tempDir . '/config/datatable/user.yaml', $yamlContent);
        
        $dataTableConfig = new DataTableConfiguration(User::class);
        $this->builder->setCurrentConfig($dataTableConfig);
        
        $this->resolver->resolveConfiguration(User::class, $this->builder);
        
        $columns = $dataTableConfig->getColumns();
        $this->assertCount(2, $columns);
        $this->assertArrayHasKey('name', $columns);
        $this->assertArrayHasKey('email', $columns);
        $this->assertTrue($dataTableConfig->isSearchEnabled());
        $this->assertTrue($dataTableConfig->isPaginationEnabled());
        $this->assertEquals(20, $dataTableConfig->getItemsPerPage());
    }
    
    public function testResolveConfigurationPriority(): void
    {
        // Enregistrer une configuration PHP
        $phpConfig = new class extends AbstractDataTableConfiguration {
            public function getEntityClass(): string
            {
                return User::class;
            }
            
            public function configure(): void
            {
                $this->addColumn(new TextColumn('php_column', 'name', 'PHP Column'));
            }
        };
        
        $this->resolver->registerConfiguration(User::class, $phpConfig);
        
        // Créer aussi un fichier YAML
        $yamlContent = <<<YAML
datatable:
  columns:
    - field: yaml_column
      property: name
      label: YAML Column
      type: text
YAML;
        
        file_put_contents($this->tempDir . '/config/datatable/user.yaml', $yamlContent);
        
        $dataTableConfig = new DataTableConfiguration(User::class);
        $this->builder->setCurrentConfig($dataTableConfig);
        
        $this->resolver->resolveConfiguration(User::class, $this->builder);
        
        // La configuration PHP doit avoir la priorité
        $columns = $dataTableConfig->getColumns();
        $this->assertCount(1, $columns);
        $this->assertArrayHasKey('php_column', $columns);
        $this->assertArrayNotHasKey('yaml_column', $columns);
    }
    
    public function testGetGlobalDefaults(): void
    {
        $globalYamlContent = <<<YAML
datatable:
  defaults:
    table_class: table table-global
    date_format: Y-m-d
    search:
      enabled: true
    pagination:
      enabled: true
      items_per_page: 25
YAML;
        
        file_put_contents($this->tempDir . '/config/datatable/global.yaml', $globalYamlContent);
        
        $defaults = $this->resolver->getGlobalDefaults();
        
        $this->assertEquals('table table-global', $defaults['table_class']);
        $this->assertEquals('Y-m-d', $defaults['date_format']);
        $this->assertTrue($defaults['search']['enabled']);
        $this->assertTrue($defaults['pagination']['enabled']);
        $this->assertEquals(25, $defaults['pagination']['items_per_page']);
    }
    
    public function testGetGlobalDefaultsReturnsEmptyArrayWhenFileNotExists(): void
    {
        $defaults = $this->resolver->getGlobalDefaults();
        
        $this->assertIsArray($defaults);
        $this->assertEmpty($defaults);
    }
    
    public function testResolveConfigurationReturnsNullWhenNoConfigFound(): void
    {
        $dataTableConfig = new DataTableConfiguration('NonExistentEntity');
        $this->builder->setCurrentConfig($dataTableConfig);
        
        // Ne devrait pas lever d'exception
        $this->resolver->resolveConfiguration('NonExistentEntity', $this->builder);
        
        // La configuration ne devrait pas être modifiée
        $columns = $dataTableConfig->getColumns();
        $this->assertEmpty($columns);
    }
}
