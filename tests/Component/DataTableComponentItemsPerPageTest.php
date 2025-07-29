<?php

/**
 * Tests for DataTableComponent items per page functionality
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package App\Tests\SigmasoftDataTableBundle\Component
 */

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Component;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Component\DataTableComponent;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Configuration\SerializableDataTableConfig;
use Sigmasoft\DataTableBundle\DataProvider\DataProviderInterface;
use Sigmasoft\DataTableBundle\Service\ColumnFactory;
use Sigmasoft\DataTableBundle\Service\DataTableRegistryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class DataTableComponentItemsPerPageTest extends TestCase
{
    private DataTableComponent $component;
    private DataProviderInterface|MockObject $dataProvider;
    private EntityManagerInterface|MockObject $entityManager;
    private ColumnFactory|MockObject $columnFactory;
    private DataTableRegistryInterface|MockObject $registry;

    protected function setUp(): void
    {
        $this->dataProvider = $this->createMock(DataProviderInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->columnFactory = $this->createMock(ColumnFactory::class);
        $this->registry = $this->createMock(DataTableRegistryInterface::class);

        $this->component = new DataTableComponent(
            $this->dataProvider,
            $this->entityManager,
            $this->columnFactory,
            $this->registry,
            null // logger
        );
    }

    public function testChangeItemsPerPageAction(): void
    {
        // Préparer la configuration initiale
        $originalConfig = new DataTableConfiguration(User::class);
        $originalConfig->setItemsPerPage(10);
        
        $this->component->config = SerializableDataTableConfig::fromConfiguration('test_id', $originalConfig);

        // Mock du columnFactory pour reconstruction
        $updatedConfig = new DataTableConfiguration(User::class);
        $updatedConfig->setItemsPerPage(25);
        $updatedConfig->setPage(1);

        $this->columnFactory
            ->expects($this->once())
            ->method('reconstructConfiguration')
            ->willReturn($updatedConfig);

        // Mock du dataProvider
        $pagination = $this->createMock(PaginationInterface::class);
        $this->dataProvider
            ->expects($this->once())
            ->method('getData')
            ->willReturn($pagination);

        // Test de l'action
        $this->component->itemsPerPageValue = 25;
        $this->component->changeItemsPerPage();

        // Vérifications
        $this->assertEquals(25, $this->component->config->itemsPerPage);
        $this->assertEquals(1, $this->component->config->page); // Page reset à 1
    }

    public function testChangeItemsPerPageWithMinimumValue(): void
    {
        // Préparer la configuration initiale
        $originalConfig = new DataTableConfiguration(User::class);
        $this->component->config = SerializableDataTableConfig::fromConfiguration('test_id', $originalConfig);

        // Mock du columnFactory
        $this->columnFactory
            ->expects($this->once())
            ->method('reconstructConfiguration')
            ->willReturn(new DataTableConfiguration(User::class));

        // Mock du dataProvider
        $this->dataProvider
            ->expects($this->once())
            ->method('getData')
            ->willReturn($this->createMock(PaginationInterface::class));

        // Test avec valeur négative (doit être mise à 1)
        $this->component->itemsPerPageValue = -5;
        $this->component->changeItemsPerPage();

        // Vérification que la valeur minimale est appliquée
        $this->assertEquals(1, $this->component->config->itemsPerPage);
    }

    public function testChangeItemsPerPageWithZero(): void
    {
        // Préparer la configuration initiale
        $originalConfig = new DataTableConfiguration(User::class);
        $this->component->config = SerializableDataTableConfig::fromConfiguration('test_id', $originalConfig);

        // Mock du columnFactory
        $this->columnFactory
            ->expects($this->once())
            ->method('reconstructConfiguration')
            ->willReturn(new DataTableConfiguration(User::class));

        // Mock du dataProvider
        $this->dataProvider
            ->expects($this->once())
            ->method('getData')
            ->willReturn($this->createMock(PaginationInterface::class));

        // Test avec valeur zéro (doit être mise à 1)
        $this->component->itemsPerPageValue = 0;
        $this->component->changeItemsPerPage();

        // Vérification que la valeur minimale est appliquée
        $this->assertEquals(1, $this->component->config->itemsPerPage);
    }

    public function testChangeItemsPerPageResetsPage(): void
    {
        // Préparer la configuration initiale avec page 5
        $originalConfig = new DataTableConfiguration(User::class);
        $originalConfig->setPage(5);
        $originalConfig->setItemsPerPage(10);
        
        $this->component->config = SerializableDataTableConfig::fromConfiguration('test_id', $originalConfig);

        // Mock du columnFactory
        $this->columnFactory
            ->expects($this->once())
            ->method('reconstructConfiguration')
            ->willReturn(new DataTableConfiguration(User::class));

        // Mock du dataProvider
        $this->dataProvider
            ->expects($this->once())
            ->method('getData')
            ->willReturn($this->createMock(PaginationInterface::class));

        // Changer le nombre d'éléments par page
        $this->component->itemsPerPageValue = 50;
        $this->component->changeItemsPerPage();

        // Vérifier que la page est remise à 1
        $this->assertEquals(1, $this->component->config->page);
        $this->assertEquals(50, $this->component->config->itemsPerPage);
    }

    public function testChangeItemsPerPageCallsDataProvider(): void
    {
        // Préparer la configuration initiale
        $originalConfig = new DataTableConfiguration(User::class);
        $this->component->config = SerializableDataTableConfig::fromConfiguration('test_id', $originalConfig);

        // Configuration reconstruite (après mise à jour)
        $reconstructedConfig = new DataTableConfiguration(User::class);

        $this->columnFactory
            ->expects($this->once())
            ->method('reconstructConfiguration')
            ->with($this->anything(), $this->registry)
            ->willReturn($reconstructedConfig);

        // Le dataProvider doit être appelé avec la nouvelle configuration
        $this->dataProvider
            ->expects($this->once())
            ->method('getData')
            ->with($reconstructedConfig)
            ->willReturn($this->createMock(PaginationInterface::class));

        $this->component->itemsPerPageValue = 25;
        $this->component->changeItemsPerPage();
    }

    public function testItemsPerPageOptions(): void
    {
        // Test que les options communes sont supportées
        $validOptions = [5, 10, 15, 20, 25, 50, 100];
        
        foreach ($validOptions as $index => $option) {
            // Reset component pour chaque test
            $this->setUp();
            
            $originalConfig = new DataTableConfiguration(User::class);
            $this->component->config = SerializableDataTableConfig::fromConfiguration('test_id_' . $index, $originalConfig);

            // Mock pour cette itération
            $this->columnFactory
                ->expects($this->once())
                ->method('reconstructConfiguration')
                ->willReturn(new DataTableConfiguration(User::class));

            $this->dataProvider
                ->expects($this->once())
                ->method('getData')
                ->willReturn($this->createMock(PaginationInterface::class));

            $this->component->itemsPerPageValue = $option;
            $this->component->changeItemsPerPage();
            $this->assertEquals($option, $this->component->config->itemsPerPage);
        }
    }
}
