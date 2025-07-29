<?php

/**
 * Tests for ColumnFactory
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package App\Tests\SigmasoftDataTableBundle\Service
 */

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Service;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Configuration\SerializableDataTableConfig;
use Sigmasoft\DataTableBundle\Exception\DataTableException;
use Sigmasoft\DataTableBundle\Service\ColumnFactory;
use Sigmasoft\DataTableBundle\Service\DataTableRegistryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ColumnFactoryTest extends TestCase
{
    private ColumnFactory $columnFactory;
    private UrlGeneratorInterface|MockObject $urlGenerator;
    private DataTableRegistryInterface|MockObject $registry;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->registry = $this->createMock(DataTableRegistryInterface::class);
        $this->columnFactory = new ColumnFactory($this->urlGenerator);
    }

    public function testCreateTextColumnFromDefinition(): void
    {
        $definition = [
            'type' => TextColumn::class,
            'name' => 'test_column',
            'property_path' => 'testProperty',
            'label' => 'Test Label',
            'sortable' => true,
            'searchable' => false,
            'options' => ['truncate' => true]
        ];

        $column = $this->columnFactory->createColumnFromDefinition($definition);

        $this->assertInstanceOf(TextColumn::class, $column);
        $this->assertEquals('test_column', $column->getName());
        $this->assertEquals('testProperty', $column->getPropertyPath());
        $this->assertEquals('Test Label', $column->getLabel());
        $this->assertTrue($column->isSortable());
        $this->assertFalse($column->isSearchable());
        $this->assertTrue($column->getOption('truncate'));
    }

    public function testCreateColumnFromDefinitionUnknownType(): void
    {
        $definition = [
            'type' => 'UnknownColumnType',
            'name' => 'test_column',
            'property_path' => 'testProperty',
            'label' => 'Test Label',
            'sortable' => true,
            'searchable' => false,
            'options' => []
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown column type: UnknownColumnType');

        $this->columnFactory->createColumnFromDefinition($definition);
    }

    public function testReconstructConfigurationWithExistingConfig(): void
    {
        // Configuration originale dans le registry
        $originalConfig = new DataTableConfiguration(User::class);
        $originalConfig->addColumn(new TextColumn('name', 'name', 'Name'));

        // Configuration sérialisée avec modifications
        $serializableConfig = $this->createMockSerializableConfig();
        $serializableConfig->configId = 'test_id';
        $serializableConfig->sortField = 'name';
        $serializableConfig->sortDirection = 'desc';
        $serializableConfig->page = 2;

        $this->registry
            ->expects($this->once())
            ->method('get')
            ->with('test_id')
            ->willReturn($originalConfig);

        $result = $this->columnFactory->reconstructConfiguration($serializableConfig, $this->registry);

        $this->assertSame($originalConfig, $result);
        $this->assertEquals('name', $result->getSortField());
        $this->assertEquals('desc', $result->getSortDirection());
        $this->assertEquals(2, $result->getPage());
    }

    public function testReconstructConfigurationWithMissingConfig(): void
    {
        // Simuler le scénario où la configuration n'est pas trouvée (nouvelle requête)
        $originalConfig = new DataTableConfiguration(User::class);
        $serializableConfig = SerializableDataTableConfig::fromConfiguration('missing_id', $originalConfig);

        // Premier appel : exception (configuration non trouvée)
        $this->registry
            ->expects($this->once())
            ->method('get')
            ->with('missing_id')
            ->willThrowException(DataTableException::configurationNotFound('missing_id'));

        // Deuxième appel : re-enregistrement de la configuration reconstruite
        $this->registry
            ->expects($this->once())
            ->method('register')
            ->with('missing_id', $this->isInstanceOf(DataTableConfiguration::class));

        $result = $this->columnFactory->reconstructConfiguration($serializableConfig, $this->registry);

        $this->assertInstanceOf(DataTableConfiguration::class, $result);
        $this->assertEquals(User::class, $result->getEntityClass());
    }

    public function testReconstructConfigurationRecoveryScenario(): void
    {
        // Test du scénario complet : pagination/tri après perte de configuration
        $originalConfig = new DataTableConfiguration(User::class);
        $originalConfig->addColumn(new TextColumn('name', 'name', 'Name', true, true));
        $originalConfig->setSortField('name');
        $originalConfig->setSortDirection('asc');
        $originalConfig->setPage(2);

        $serializableConfig = SerializableDataTableConfig::fromConfiguration('2be08cf13015cce007ab6b2cfc030c98', $originalConfig);

        $this->registry
            ->expects($this->once())
            ->method('get')
            ->with('2be08cf13015cce007ab6b2cfc030c98')
            ->willThrowException(DataTableException::configurationNotFound('2be08cf13015cce007ab6b2cfc030c98'));

        $this->registry
            ->expects($this->once())
            ->method('register')
            ->with('2be08cf13015cce007ab6b2cfc030c98', $this->anything());

        $result = $this->columnFactory->reconstructConfiguration($serializableConfig, $this->registry);

        // Vérifier que la configuration a été correctement reconstruite
        $this->assertInstanceOf(DataTableConfiguration::class, $result);
        $this->assertEquals('name', $result->getSortField());
        $this->assertEquals('asc', $result->getSortDirection());
        $this->assertEquals(2, $result->getPage());
    }

    private function createMockSerializableConfig(): SerializableDataTableConfig
    {
        $config = $this->createMock(SerializableDataTableConfig::class);
        $config->entityClass = User::class;
        $config->filters = [];
        $config->sortField = '';
        $config->sortDirection = 'asc';
        $config->page = 1;
        $config->itemsPerPage = 10;
        $config->searchQuery = '';
        $config->columnDefinitions = [];
        
        $config->method('createMutableConfig')
            ->willReturn(new DataTableConfiguration(User::class));
        
        return $config;
    }
}
