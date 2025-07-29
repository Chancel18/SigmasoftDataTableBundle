<?php

namespace App\Tests\SigmasoftDataTableBundle\Configuration;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Configuration\YamlConfigurator;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Service\DataTableConfigResolver;
use Sigmasoft\DataTableBundle\Exception\DataTableException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class YamlConfiguratorTest extends TestCase
{
    private DataTableBuilder $builder;
    private DataTableConfiguration $config;
    private string $testYamlPath;
    
    protected function setUp(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $configResolver = $this->createMock(DataTableConfigResolver::class);
        
        $this->builder = new DataTableBuilder($urlGenerator, $configResolver);
        $this->config = new DataTableConfiguration(User::class);
        $this->builder->setCurrentConfig($this->config);
        
        // Créer un fichier YAML de test temporaire
        $this->testYamlPath = sys_get_temp_dir() . '/test_datatable_config.yaml';
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
      
    - field: createdAt
      property: createdAt
      label: Créé le
      type: date
      format: d/m/Y
      
    - field: isActive
      property: isActive
      label: Statut
      type: badge
      options:
        "true":
          label: Actif
          class: badge-success
        "false":
          label: Inactif
          class: badge-danger
          
  search:
    enabled: true
    fields: [name, email]
    
  pagination:
    enabled: true
    items_per_page: 25
    items_per_page_options: [25, 50, 100]
    
  sorting:
    field: name
    direction: desc
    
  table_class: table table-yaml-test
  date_format: d/m/Y H:i
  
  export:
    enabled: true
    formats: [csv, excel, pdf]
YAML;
        
        file_put_contents($this->testYamlPath, $yamlContent);
    }
    
    protected function tearDown(): void
    {
        if (file_exists($this->testYamlPath)) {
            unlink($this->testYamlPath);
        }
    }
    
    public function testFromFileThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage('Configuration file not found');
        
        YamlConfigurator::fromFile('/non/existent/file.yaml');
    }
    
    public function testFromFileThrowsExceptionForInvalidYaml(): void
    {
        $invalidYamlPath = sys_get_temp_dir() . '/invalid_datatable_config.yaml';
        file_put_contents($invalidYamlPath, 'invalid: yaml: content');
        
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage("Invalid YAML configuration:");
        
        try {
            YamlConfigurator::fromFile($invalidYamlPath);
        } finally {
            unlink($invalidYamlPath);
        }
    }
    
    public function testFromFileCreatesConfiguratorSuccessfully(): void
    {
        $configurator = YamlConfigurator::fromFile($this->testYamlPath);
        
        $this->assertInstanceOf(YamlConfigurator::class, $configurator);
    }
    
    public function testConfigureColumns(): void
    {
        $configurator = YamlConfigurator::fromFile($this->testYamlPath);
        $configurator->configure($this->builder);
        
        $columns = $this->config->getColumns();
        $this->assertCount(4, $columns);
        $this->assertArrayHasKey('name', $columns);
        $this->assertArrayHasKey('email', $columns);
        $this->assertArrayHasKey('createdAt', $columns);
        $this->assertArrayHasKey('isActive', $columns);
    }
    
    public function testConfigureSearch(): void
    {
        $configurator = YamlConfigurator::fromFile($this->testYamlPath);
        $configurator->configure($this->builder);
        
        $this->assertTrue($this->config->isSearchEnabled());
        $this->assertEquals(['name', 'email'], $this->config->getSearchFields());
    }
    
    public function testConfigurePagination(): void
    {
        $configurator = YamlConfigurator::fromFile($this->testYamlPath);
        $configurator->configure($this->builder);
        
        $this->assertTrue($this->config->isPaginationEnabled());
        $this->assertEquals(25, $this->config->getItemsPerPage());
        $this->assertEquals([25, 50, 100], $this->config->getItemsPerPageOptions());
    }
    
    public function testConfigureSorting(): void
    {
        $configurator = YamlConfigurator::fromFile($this->testYamlPath);
        $configurator->configure($this->builder);
        
        $this->assertEquals('name', $this->config->getSortField());
        $this->assertEquals('desc', $this->config->getSortDirection());
    }
    
    public function testConfigureTableProperties(): void
    {
        $configurator = YamlConfigurator::fromFile($this->testYamlPath);
        $configurator->configure($this->builder);
        
        $this->assertEquals('table table-yaml-test', $this->config->getTableClass());
        $this->assertEquals('d/m/Y H:i', $this->config->getDateFormat());
    }
    
    public function testConfigureExport(): void
    {
        $configurator = YamlConfigurator::fromFile($this->testYamlPath);
        $configurator->configure($this->builder);
        
        $this->assertTrue($this->config->isExportEnabled());
        $this->assertEquals(['csv', 'excel', 'pdf'], $this->config->getExportFormats());
    }
    
    public function testCreateColumnThrowsExceptionForUnknownType(): void
    {
        $invalidYamlPath = sys_get_temp_dir() . '/invalid_column_type.yaml';
        $yamlContent = <<<YAML
datatable:
  columns:
    - field: test
      property: test
      label: Test
      type: unknown_type
YAML;
        
        file_put_contents($invalidYamlPath, $yamlContent);
        
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage('Unknown column type: unknown_type');
        
        try {
            $configurator = YamlConfigurator::fromFile($invalidYamlPath);
            $configurator->configure($this->builder);
        } finally {
            unlink($invalidYamlPath);
        }
    }
}
