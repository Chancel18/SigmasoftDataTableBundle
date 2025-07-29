<?php

/**
 * Tests for DataTableException
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package App\Tests\SigmasoftDataTableBundle\Exception
 */

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Exception;

use Sigmasoft\DataTableBundle\Exception\DataTableException;
use PHPUnit\Framework\TestCase;

class DataTableExceptionTest extends TestCase
{
    public function testInvalidEntityClassException(): void
    {
        $exception = DataTableException::invalidEntityClass('InvalidClass');
        
        $this->assertInstanceOf(DataTableException::class, $exception);
        $this->assertEquals('Entity class "InvalidClass" not found or is not a valid Doctrine entity.', $exception->getMessage());
    }

    public function testInvalidSortDirectionException(): void
    {
        $exception = DataTableException::invalidSortDirection('invalid');
        
        $this->assertInstanceOf(DataTableException::class, $exception);
        $this->assertEquals('Invalid sort direction "invalid". Must be "asc" or "desc".', $exception->getMessage());
    }

    public function testInvalidPageException(): void
    {
        $exception = DataTableException::invalidPage(-1);
        
        $this->assertInstanceOf(DataTableException::class, $exception);
        $this->assertEquals('Invalid page number "-1". Must be greater than 0.', $exception->getMessage());
    }

    public function testInvalidItemsPerPageException(): void
    {
        $exception = DataTableException::invalidItemsPerPage(0);
        
        $this->assertInstanceOf(DataTableException::class, $exception);
        $this->assertEquals('Invalid items per page "0". Must be greater than 0.', $exception->getMessage());
    }

    public function testInvalidColumnTypeException(): void
    {
        $exception = DataTableException::invalidColumnType('InvalidType');
        
        $this->assertInstanceOf(DataTableException::class, $exception);
        $this->assertEquals('Invalid column type "InvalidType".', $exception->getMessage());
    }

    public function testConfigurationNotFoundException(): void
    {
        $exception = DataTableException::configurationNotFound('test_id');
        
        $this->assertInstanceOf(DataTableException::class, $exception);
        $this->assertEquals('DataTable configuration with ID "test_id" not found.', $exception->getMessage());
    }

    public function testInvalidFieldPathException(): void
    {
        $exception = DataTableException::invalidFieldPath('user.invalid', 'User');
        
        $this->assertInstanceOf(DataTableException::class, $exception);
        $this->assertEquals('Invalid field path "user.invalid" for entity "User".', $exception->getMessage());
    }

    public function testGenericExceptionWithMessage(): void
    {
        $message = 'Custom error message';
        $exception = new DataTableException($message);
        
        $this->assertInstanceOf(DataTableException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testGenericExceptionWithMessageAndPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $message = 'Custom error message';
        $exception = new DataTableException($message, 0, $previous);
        
        $this->assertInstanceOf(DataTableException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
