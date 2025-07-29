<?php

/**
 * Tests for DataTableRegistry
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package App\Tests\SigmasoftDataTableBundle\Service
 */

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Service;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Exception\DataTableException;
use Sigmasoft\DataTableBundle\Service\DataTableRegistry;
use PHPUnit\Framework\TestCase;

class DataTableRegistryTest extends TestCase
{
    private DataTableRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new DataTableRegistry();
    }

    public function testGenerateId(): void
    {
        $id1 = $this->registry->generateId();
        $id2 = $this->registry->generateId();
        
        $this->assertIsString($id1);
        $this->assertIsString($id2);
        $this->assertNotEquals($id1, $id2);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $id1);
    }

    public function testRegisterAndRetrieve(): void
    {
        $configuration = new DataTableConfiguration(User::class);
        $id = 'test_id';
        
        $this->registry->register($id, $configuration);
        $retrieved = $this->registry->get($id);
        
        $this->assertSame($configuration, $retrieved);
    }

    public function testRetrieveNonExistentConfiguration(): void
    {
        $this->expectException(DataTableException::class);
        $this->expectExceptionMessage('DataTable configuration with ID "non_existent" not found.');
        
        $this->registry->get('non_existent');
    }

    public function testHasConfiguration(): void
    {
        $configuration = new DataTableConfiguration(User::class);
        $id = 'test_id';
        
        $this->assertFalse($this->registry->has($id));
        
        $this->registry->register($id, $configuration);
        
        $this->assertTrue($this->registry->has($id));
    }

    public function testUnregister(): void
    {
        $configuration = new DataTableConfiguration(User::class);
        $id = 'test_id';
        
        $this->registry->register($id, $configuration);
        $this->assertTrue($this->registry->has($id));
        
        $this->registry->unregister($id);
        $this->assertFalse($this->registry->has($id));
    }

    public function testUnregisterNonExistentConfiguration(): void
    {
        // Should not throw an exception
        $this->registry->unregister('non_existent');
        $this->assertFalse($this->registry->has('non_existent'));
    }

    public function testGetAll(): void
    {
        $config1 = new DataTableConfiguration(User::class);
        $config2 = new DataTableConfiguration(User::class);
        
        $this->registry->register('id1', $config1);
        $this->registry->register('id2', $config2);
        
        $all = $this->registry->getAll();
        
        $this->assertCount(2, $all);
        $this->assertArrayHasKey('id1', $all);
        $this->assertArrayHasKey('id2', $all);
        $this->assertSame($config1, $all['id1']);
        $this->assertSame($config2, $all['id2']);
    }

    public function testClear(): void
    {
        $configuration = new DataTableConfiguration(User::class);
        
        $this->registry->register('id1', $configuration);
        $this->registry->register('id2', $configuration);
        
        $this->assertCount(2, $this->registry->getAll());
        
        $this->registry->clear();
        
        $this->assertCount(0, $this->registry->getAll());
    }

    public function testOverwriteConfiguration(): void
    {
        $config1 = new DataTableConfiguration(User::class);
        $config2 = new DataTableConfiguration(User::class);
        $id = 'test_id';
        
        $this->registry->register($id, $config1);
        $this->assertSame($config1, $this->registry->get($id));
        
        // Overwrite with new configuration
        $this->registry->register($id, $config2);
        $this->assertSame($config2, $this->registry->get($id));
    }
}
