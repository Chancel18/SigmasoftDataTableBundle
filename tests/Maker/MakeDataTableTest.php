<?php

/**
 * Tests for MakeDataTable command
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package App\Tests\SigmasoftDataTableBundle\Maker
 */

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Maker;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Maker\MakeDataTable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class MakeDataTableTest extends TestCase
{
    private MakeDataTable $maker;
    private EntityManagerInterface|MockObject $entityManager;
    private ParameterBagInterface|MockObject $parameterBag;
    private array $bundleConfig;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->bundleConfig = [
            'maker' => [
                'default_column_types' => [
                    'string' => 'text',
                    'datetime' => 'date',
                    'boolean' => 'badge',
                ],
                'excluded_properties' => ['password', 'plainPassword'],
                'auto_add_actions' => true,
                'default_actions' => [
                    'show' => ['icon' => 'bi bi-eye'],
                    'edit' => ['icon' => 'bi bi-pencil'],
                    'delete' => ['type' => 'delete']
                ]
            ]
        ];

        $this->maker = new MakeDataTable(
            $this->entityManager,
            $this->parameterBag,
            $this->bundleConfig
        );
    }

    public function testGetCommandName(): void
    {
        $this->assertEquals('make:datatable', MakeDataTable::getCommandName());
    }

    public function testGetCommandDescription(): void
    {
        $this->assertEquals(
            'Generate a SigmasoftDataTable for an entity with automatic column detection',
            MakeDataTable::getCommandDescription()
        );
    }

    public function testEntityExists(): void
    {
        $reflection = new \ReflectionClass($this->maker);
        $method = $reflection->getMethod('entityExists');
        $method->setAccessible(true);

        $this->entityManager
            ->method('getClassMetadata')
            ->with('App\Entity\User')
            ->willReturn($this->createMock(ClassMetadata::class));

        $result = $method->invoke($this->maker, 'App\Entity\User');
        $this->assertTrue($result);
    }

    public function testEntityNotExists(): void
    {
        $reflection = new \ReflectionClass($this->maker);
        $method = $reflection->getMethod('entityExists');
        $method->setAccessible(true);

        $this->entityManager
            ->method('getClassMetadata')
            ->willThrowException(new \Exception('Entity not found'));

        $result = $method->invoke($this->maker, 'App\Entity\NonExistent');
        $this->assertFalse($result);
    }

    /**
     * @dataProvider columnTypeProvider
     */
    public function testColumnTypeMapping(string $doctrineType, string $expectedColumnType): void
    {
        $reflection = new \ReflectionClass($this->maker);
        $method = $reflection->getMethod('getColumnTypeFromDoctrine');
        $method->setAccessible(true);

        $result = $method->invoke($this->maker, $doctrineType);
        
        $this->assertEquals($expectedColumnType, $result);
    }

    public function columnTypeProvider(): array
    {
        return [
            ['string', 'text'],
            ['text', 'text'],
            ['integer', 'text'],
            ['float', 'text'],
            ['decimal', 'text'],
            ['boolean', 'badge'],
            ['datetime', 'date'],
            ['datetime_immutable', 'date'],
            ['date', 'date'],
            ['date_immutable', 'date'],
            ['time', 'date'],
            ['time_immutable', 'date'],
            ['unknown_type', 'text'], // fallback
        ];
    }

    public function testColumnOptionsGeneration(): void
    {
        $reflection = new \ReflectionClass($this->maker);
        $method = $reflection->getMethod('getColumnOptions');
        $method->setAccessible(true);

        // Test boolean field
        $options = $method->invoke($this->maker, 'boolean', 'isActive');
        $this->assertArrayHasKey('value_mapping', $options);
        $this->assertEquals(['1' => 'Oui', '0' => 'Non'], $options['value_mapping']);
        $this->assertTrue($options['sortable']);

        // Test string field
        $options = $method->invoke($this->maker, 'string', 'name');
        $this->assertTrue($options['sortable']);
        $this->assertTrue($options['searchable']);

        // Test datetime field
        $options = $method->invoke($this->maker, 'datetime', 'createdAt');
        $this->assertEquals('d/m/Y H:i', $options['format']);

        // Test id field (should not be searchable)
        $options = $method->invoke($this->maker, 'integer', 'id');
        $this->assertFalse($options['searchable']);
    }

    public function testDefaultActionsGeneration(): void
    {
        $reflection = new \ReflectionClass($this->maker);
        $method = $reflection->getMethod('generateDefaultActions');
        $method->setAccessible(true);

        $actions = $method->invoke($this->maker, 'User');

        $this->assertArrayHasKey('show', $actions);
        $this->assertArrayHasKey('edit', $actions);
        $this->assertArrayHasKey('delete', $actions);

        $this->assertEquals('app_user_show', $actions['show']['route']);
        $this->assertEquals('app_user_edit', $actions['edit']['route']);
        $this->assertEquals('bi bi-eye', $actions['show']['icon']);
    }

    public function testDefaultActionsDisabled(): void
    {
        // Test with auto_add_actions disabled
        $this->bundleConfig['maker']['auto_add_actions'] = false;
        $maker = new MakeDataTable(
            $this->entityManager,
            $this->parameterBag,
            $this->bundleConfig
        );

        $reflection = new \ReflectionClass($maker);
        $method = $reflection->getMethod('generateDefaultActions');
        $method->setAccessible(true);

        $actions = $method->invoke($maker, 'User');

        $this->assertEmpty($actions);
    }

    public function testSearchableFieldsExtraction(): void
    {
        $reflection = new \ReflectionClass($this->maker);
        $method = $reflection->getMethod('getSearchableFields');
        $method->setAccessible(true);

        $columns = [
            ['name' => 'id', 'options' => ['searchable' => false]],
            ['name' => 'name', 'options' => ['searchable' => true]],
            ['name' => 'email', 'options' => ['searchable' => true]],
            ['name' => 'createdAt', 'options' => ['searchable' => false]],
        ];

        $result = $method->invoke($this->maker, $columns);

        $this->assertEquals("['name', 'email']", $result);
    }

    public function testColumnMethodNameMapping(): void
    {
        $reflection = new \ReflectionClass($this->maker);
        $method = $reflection->getMethod('getColumnMethodName');
        $method->setAccessible(true);

        $this->assertEquals('TextColumn', $method->invoke($this->maker, 'text'));
        $this->assertEquals('DateColumn', $method->invoke($this->maker, 'date'));
        $this->assertEquals('BadgeColumn', $method->invoke($this->maker, 'badge'));
        $this->assertEquals('ActionColumn', $method->invoke($this->maker, 'action'));
        $this->assertEquals('TextColumn', $method->invoke($this->maker, 'unknown'));
    }

    public function testTemplatePathGeneration(): void
    {
        $this->parameterBag
            ->method('get')
            ->with('kernel.project_dir')
            ->willReturn('/project/dir');

        $reflection = new \ReflectionClass($this->maker);
        $method = $reflection->getMethod('getTemplatePath');
        $method->setAccessible(true);

        $path = $method->invoke($this->maker, 'User');

        $this->assertEquals('/project/dir/templates/user/index.html.twig', $path);
    }

    public function testControllerPathGeneration(): void
    {
        $this->parameterBag
            ->method('get')
            ->with('kernel.project_dir')
            ->willReturn('/project/dir');

        $reflection = new \ReflectionClass($this->maker);
        $method = $reflection->getMethod('getControllerPath');
        $method->setAccessible(true);

        $path = $method->invoke($this->maker, 'App\Controller\UserController');

        $this->assertEquals('/project/dir/src/Controller/UserController.php', $path);
    }
}
