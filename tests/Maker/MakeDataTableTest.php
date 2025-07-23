<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Maker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\Maker\MakeDataTable;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Tests unitaires pour la commande MakeDataTable
 */
class MakeDataTableTest extends TestCase
{
    private MakeDataTable $makeDataTable;
    private EntityManagerInterface $entityManager;
    private ClassMetadataFactory $metadataFactory;

    protected function setUp(): void
    {
        // Mock EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->metadataFactory = $this->createMock(ClassMetadataFactory::class);
        
        $this->entityManager
            ->method('getMetadataFactory')
            ->willReturn($this->metadataFactory);

        $this->makeDataTable = new MakeDataTable($this->entityManager);
    }

    public function testGetCommandName(): void
    {
        $this->assertEquals('make:datatable', MakeDataTable::getCommandName());
    }

    public function testGetCommandDescription(): void
    {
        $description = MakeDataTable::getCommandDescription();
        $this->assertStringContainsString('DataTable', $description);
        $this->assertStringContainsString('entité', $description);
    }

    public function testConfigureDependencies(): void
    {
        // DependencyBuilder est final, on teste indirectement
        $dependencies = new DependencyBuilder();
        
        // Le test vérifie que la méthode ne lève pas d'exception
        try {
            $this->makeDataTable->configureDependencies($dependencies);
            $this->assertTrue(true, 'configureDependencies s\'exécute sans erreur');
        } catch (\Exception $e) {
            $this->fail('configureDependencies a échoué: ' . $e->getMessage());
        }
    }

    public function testSkeletonTemplatesExist(): void
    {
        $skeletonDir = __DIR__ . '/../../src/Resources/skeleton/datatable';
        
        $this->assertDirectoryExists($skeletonDir, 'Le répertoire skeleton doit exister');
        $this->assertFileExists($skeletonDir . '/index.twig', 'Le template index.twig doit exister');
        $this->assertFileExists($skeletonDir . '/Controller.tpl.php', 'Le template Controller.tpl.php doit exister');
    }

    public function testSkeletonTemplateContent(): void
    {
        $indexTemplate = __DIR__ . '/../../src/Resources/skeleton/datatable/index.twig';
        $controllerTemplate = __DIR__ . '/../../src/Resources/skeleton/datatable/Controller.tpl.php';
        
        $indexContent = file_get_contents($indexTemplate);
        $controllerContent = file_get_contents($controllerTemplate);
        
        // Vérifier que les templates contiennent les variables attendues
        $this->assertStringContainsString('{{ page_title }}', $indexContent);
        $this->assertStringContainsString('{{ entity_class_name }}', $indexContent);
        $this->assertStringContainsString('<twig:SigmasoftDataTable', $indexContent);
        
        $this->assertStringContainsString('<?= $class_name ?>', $controllerContent);
        $this->assertStringContainsString('<?= $entity_full_class_name ?>', $controllerContent);
        $this->assertStringContainsString('<?= $route_name ?>', $controllerContent);
    }

    public function testEntityChoicesWithMockData(): void
    {
        $userMetadata = $this->createMock(ClassMetadata::class);
        $userMetadata->method('getName')->willReturn('App\\Entity\\User');
        
        $productMetadata = $this->createMock(ClassMetadata::class);
        $productMetadata->method('getName')->willReturn('App\\Entity\\Product');

        $this->metadataFactory
            ->method('getAllMetadata')
            ->willReturn([$userMetadata, $productMetadata]);

        // Utilisation de reflection pour tester la méthode privée
        $reflection = new \ReflectionClass($this->makeDataTable);
        $method = $reflection->getMethod('getEntityChoices');
        $method->setAccessible(true);

        $choices = $method->invoke($this->makeDataTable);

        $this->assertContains('App\\Entity\\User', $choices);
        $this->assertContains('User', $choices);
        $this->assertContains('App\\Entity\\Product', $choices);
        $this->assertContains('Product', $choices);
    }

    public function testResolveEntityClass(): void
    {
        $choices = [
            'App\\Entity\\User',
            'User',
            'App\\Entity\\Product', 
            'Product'
        ];

        $reflection = new \ReflectionClass($this->makeDataTable);
        $method = $reflection->getMethod('resolveEntityClass');
        $method->setAccessible(true);

        // Test résolution nom exact
        $this->assertEquals(
            'App\\Entity\\User',
            $method->invoke($this->makeDataTable, 'App\\Entity\\User', $choices)
        );

        // Test résolution nom court - la logique retourne le premier match
        $result = $method->invoke($this->makeDataTable, 'User', $choices);
        $this->assertContains($result, ['App\\Entity\\User', 'User']);
        $this->assertNotNull($result);

        // Test résolution insensible à la casse
        $this->assertEquals(
            'App\\Entity\\User',
            $method->invoke($this->makeDataTable, 'user', $choices)
        );

        // Test entité non trouvée
        $this->assertNull(
            $method->invoke($this->makeDataTable, 'NonExistent', $choices)
        );
    }

    public function testGetShortClassName(): void
    {
        $reflection = new \ReflectionClass($this->makeDataTable);
        $method = $reflection->getMethod('getShortClassName');
        $method->setAccessible(true);

        $this->assertEquals(
            'User',
            $method->invoke($this->makeDataTable, 'App\\Entity\\User')
        );

        $this->assertEquals(
            'DataTableService',
            $method->invoke($this->makeDataTable, 'Sigmasoft\\DataTableBundle\\Service\\DataTableService')
        );
    }

    public function testMapDoctrineTypeToDataTableType(): void
    {
        $reflection = new \ReflectionClass($this->makeDataTable);
        $method = $reflection->getMethod('mapDoctrineTypeToDataTableType');
        $method->setAccessible(true);

        $this->assertEquals('integer', $method->invoke($this->makeDataTable, 'integer'));
        $this->assertEquals('integer', $method->invoke($this->makeDataTable, 'bigint'));
        $this->assertEquals('currency', $method->invoke($this->makeDataTable, 'decimal'));
        $this->assertEquals('boolean', $method->invoke($this->makeDataTable, 'boolean'));
        $this->assertEquals('date', $method->invoke($this->makeDataTable, 'date'));
        $this->assertEquals('datetime', $method->invoke($this->makeDataTable, 'datetime'));
        $this->assertEquals('string', $method->invoke($this->makeDataTable, 'string'));
        $this->assertEquals('string', $method->invoke($this->makeDataTable, 'unknown_type'));
    }

    public function testGenerateFieldLabel(): void
    {
        $reflection = new \ReflectionClass($this->makeDataTable);
        $method = $reflection->getMethod('generateFieldLabel');
        $method->setAccessible(true);

        $this->assertEquals('First Name', $method->invoke($this->makeDataTable, 'firstName'));
        $this->assertEquals('Created At', $method->invoke($this->makeDataTable, 'createdAt'));
        $this->assertEquals('Email', $method->invoke($this->makeDataTable, 'email'));
        $this->assertEquals('Id', $method->invoke($this->makeDataTable, 'id'));
    }

    public function testPluralize(): void
    {
        $reflection = new \ReflectionClass($this->makeDataTable);
        $method = $reflection->getMethod('pluralize');
        $method->setAccessible(true);

        $this->assertEquals('users', $method->invoke($this->makeDataTable, 'User'));
        $this->assertEquals('products', $method->invoke($this->makeDataTable, 'Product'));
        $this->assertEquals('companys', $method->invoke($this->makeDataTable, 'Company')); // Pluralisation simple
        
        // Mots français
        $this->assertEquals('eaux', $method->invoke($this->makeDataTable, 'eau'));
        $this->assertEquals('animaux', $method->invoke($this->makeDataTable, 'animal'));
    }

    public function testNormalizeFieldMapping(): void
    {
        $reflection = new \ReflectionClass($this->makeDataTable);
        $method = $reflection->getMethod('normalizeFieldMapping');
        $method->setAccessible(true);

        // Test avec array (ancien format)
        $arrayMapping = [
            'type' => 'string',
            'fieldName' => 'name',
            'length' => 255,
            'nullable' => false
        ];

        $result = $method->invoke($this->makeDataTable, $arrayMapping);
        $this->assertEquals($arrayMapping, $result);

        // Test avec objet (nouveau format)
        $objectMapping = new \stdClass();
        $objectMapping->type = 'integer';
        $objectMapping->fieldName = 'id';
        $objectMapping->nullable = false;
        $objectMapping->unique = true;

        $result = $method->invoke($this->makeDataTable, $objectMapping);
        $this->assertEquals('integer', $result['type']);
        $this->assertEquals('id', $result['fieldName']);
        $this->assertFalse($result['nullable']);
        $this->assertTrue($result['unique']);
    }

    public function testAbsolutePathsUsedInGeneration(): void
    {
        // Test que les chemins absolus sont correctement formés
        $expectedIndexPath = __DIR__ . '/../../src/Resources/skeleton/datatable/index.twig';
        $expectedControllerPath = __DIR__ . '/../../src/Resources/skeleton/datatable/Controller.tpl.php';

        $this->assertFileExists($expectedIndexPath, 'Le chemin absolu vers index.twig doit être valide');
        $this->assertFileExists($expectedControllerPath, 'Le chemin absolu vers Controller.tpl.php doit être valide');

        // Vérifier que les chemins sont accessibles depuis le contexte de la classe
        $makeDataTablePath = dirname((new \ReflectionClass($this->makeDataTable))->getFileName());
        $skeletonPath = $makeDataTablePath . '/../Resources/skeleton/datatable/index.twig';
        
        $this->assertFileExists($skeletonPath, 'Le chemin relatif depuis MakeDataTable doit être valide');
    }

    /**
     * Test que les méthodes principales existent et sont appelables
     */
    public function testPublicMethodsAccessible(): void
    {
        $input = $this->createMock(InputInterface::class);
        $io = $this->createMock(\Symfony\Component\Console\Style\SymfonyStyle::class);
        
        // Test que les méthodes publiques existent
        $this->assertTrue(method_exists($this->makeDataTable, 'interact'));
        $this->assertTrue(method_exists($this->makeDataTable, 'generate'));
        $this->assertTrue(method_exists($this->makeDataTable, 'configureDependencies'));
        
        // Test que les méthodes statiques fonctionnent
        $this->assertEquals('make:datatable', MakeDataTable::getCommandName());
        $this->assertNotEmpty(MakeDataTable::getCommandDescription());
        
        $this->assertTrue(true, 'Toutes les méthodes publiques sont accessibles');
    }
}