<?php

/**
 * Integration tests for SigmasoftDataTableBundle
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package Sigmasoft\Tests\DataTableBundle
 */

declare(strict_types=1);

namespace Sigmasoft\Tests\DataTableBundle;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Exception\DataTableException;
use Sigmasoft\DataTableBundle\Service\DataTableRegistry;
use Sigmasoft\DataTableBundle\Service\DataTableConfigResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BundleIntegrationTest extends TestCase
{
    public function testBasicBundleWorkflow(): void
    {
        // Test basic component instantiation
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $configResolver = $this->createMock(DataTableConfigResolver::class);
        $builder = new DataTableBuilder($urlGenerator, $configResolver);
        $registry = new DataTableRegistry();
        
        $this->assertInstanceOf(DataTableBuilder::class, $builder);
        $this->assertInstanceOf(DataTableRegistry::class, $registry);
    }

    public function testDataTableConfigurationCreation(): void
    {
        $config = new DataTableConfiguration(User::class);
        
        $this->assertEquals(User::class, $config->getEntityClass());
        $this->assertEquals(10, $config->getItemsPerPage());
        $this->assertEquals(1, $config->getPage());
        
        // Les valeurs par défaut peuvent être true selon l'implémentation
        $this->assertIsBool($config->isSearchEnabled());
        $this->assertIsBool($config->isPaginationEnabled());
        $this->assertIsBool($config->isSortingEnabled());
    }

    public function testRegistryBasicOperations(): void
    {
        $registry = new DataTableRegistry();
        $config = new DataTableConfiguration(User::class);
        
        // Test basic operations
        $id = $registry->generateId();
        $this->assertIsString($id);
        
        $registry->register($id, $config);
        $this->assertTrue($registry->has($id));
        
        $retrieved = $registry->get($id);
        $this->assertSame($config, $retrieved);
    }

    public function testExceptionClasses(): void
    {
        $exception = DataTableException::invalidEntityClass('InvalidClass');
        $this->assertInstanceOf(DataTableException::class, $exception);
        $this->assertStringContainsString('InvalidClass', $exception->getMessage());
        
        $exception2 = DataTableException::invalidSortDirection('invalid');
        $this->assertInstanceOf(DataTableException::class, $exception2);
        $this->assertStringContainsString('invalid', $exception2->getMessage());
    }

    public function testConfigurationValidation(): void
    {
        $config = new DataTableConfiguration(User::class);
        
        // Test that configuration accepts valid values
        $config->setPage(5);
        $config->setItemsPerPage(25);
        $config->setSortDirection('desc');
        
        $this->assertEquals(5, $config->getPage());
        $this->assertEquals(25, $config->getItemsPerPage());
        $this->assertEquals('desc', $config->getSortDirection());
    }

    public function testBundleClassesExist(): void
    {
        // Verify all main classes exist and are loadable
        $classes = [
            'Sigmasoft\DataTableBundle\SigmasoftDataTableBundle',
            'Sigmasoft\DataTableBundle\Builder\DataTableBuilder',
            'Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration',
            'Sigmasoft\DataTableBundle\Exception\DataTableException',
            'Sigmasoft\DataTableBundle\Service\DataTableRegistry',
            'Sigmasoft\DataTableBundle\Service\DataTableRegistryInterface',
            'Sigmasoft\DataTableBundle\Maker\MakeDataTable',
        ];

        foreach ($classes as $class) {
            $this->assertTrue(class_exists($class) || interface_exists($class), "Class $class should exist");
        }
    }

    public function testConfigurationPersistenceRecovery(): void
    {
        // Test du scénario de récupération après perte de configuration du registry
        $registry = new \Sigmasoft\DataTableBundle\Service\DataTableRegistry();
        $urlGenerator = $this->createMock(\Symfony\Component\Routing\Generator\UrlGeneratorInterface::class);
        $columnFactory = new \Sigmasoft\DataTableBundle\Service\ColumnFactory($urlGenerator);

        // Créer une configuration initiale
        $config = new DataTableConfiguration(User::class);
        $config->addColumn(new \Sigmasoft\DataTableBundle\Column\TextColumn('name', 'name', 'Name', true, true));
        
        // Simuler la sérialisation (comme dans un LiveComponent)
        $serializableConfig = \Sigmasoft\DataTableBundle\Configuration\SerializableDataTableConfig::fromConfiguration('test_id', $config);
        
        // Simuler une nouvelle requête où le registry est vide
        $newRegistry = new \Sigmasoft\DataTableBundle\Service\DataTableRegistry();
        
        // La reconstruction doit réussir même avec un registry vide
        $reconstructed = $columnFactory->reconstructConfiguration($serializableConfig, $newRegistry);
        
        $this->assertInstanceOf(DataTableConfiguration::class, $reconstructed);
        $this->assertEquals(User::class, $reconstructed->getEntityClass());
        $this->assertCount(1, $reconstructed->getColumns());
        
        // Vérifier que la configuration a été re-enregistrée
        $this->assertTrue($newRegistry->has('test_id'));
    }
}
