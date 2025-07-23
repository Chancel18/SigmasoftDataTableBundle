<?php
declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\Exception\EntityNotFoundException;
use Sigmasoft\DataTableBundle\Exception\EntityNotAllowedException;
use Sigmasoft\DataTableBundle\Exception\FormatterException;

class ExceptionsTest extends TestCase
{
    public function testEntityNotFoundException(): void
    {
        $entityClass = 'App\\Entity\\User';
        $entityId = 123;
        $message = 'Custom message';

        // Test constructeur simple
        $exception1 = new EntityNotFoundException($entityClass, $entityId);
        $this->assertStringContainsString($entityClass, $exception1->getMessage());
        $this->assertStringContainsString((string)$entityId, $exception1->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $exception1);
        $this->assertSame($entityClass, $exception1->getEntityClass());
        $this->assertSame($entityId, $exception1->getId());

        // Test avec message personnalisé
        $exception2 = new EntityNotFoundException($entityClass, $entityId, $message);
        $this->assertSame($message, $exception2->getMessage());

        // Test avec code d'erreur
        $exception3 = new EntityNotFoundException($entityClass, $entityId, $message, 404);
        $this->assertSame(404, $exception3->getCode());

        // Test avec exception précédente
        $previousException = new \Exception('Previous exception');
        $exception4 = new EntityNotFoundException($entityClass, $entityId, $message, 0, $previousException);
        $this->assertSame($previousException, $exception4->getPrevious());
    }

    public function testEntityNotAllowedException(): void
    {
        $entityClass = 'App\\Entity\\User';
        $message = 'Access denied';

        // Test constructeur avec message par défaut
        $exception1 = new EntityNotAllowedException($entityClass);
        $this->assertStringContainsString($entityClass, $exception1->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $exception1);
        $this->assertSame($entityClass, $exception1->getEntityClass());

        // Test avec message personnalisé
        $exception2 = new EntityNotAllowedException($entityClass, $message);
        $this->assertSame($message, $exception2->getMessage());

        // Test avec code d'erreur
        $exception3 = new EntityNotAllowedException($entityClass, $message, 403);
        $this->assertSame(403, $exception3->getCode());
    }

    public function testFormatterException(): void
    {
        $message = 'Invalid format';

        // Test constructeur simple
        $exception1 = new FormatterException($message);
        $this->assertSame($message, $exception1->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $exception1);

        // Test avec code d'erreur
        $exception2 = new FormatterException($message, 500);
        $this->assertSame($message, $exception2->getMessage());
        $this->assertSame(500, $exception2->getCode());

        // Test avec exception précédente
        $previousException = new \InvalidArgumentException('Invalid argument');
        $exception3 = new FormatterException($message, 0, $previousException);
        $this->assertSame($previousException, $exception3->getPrevious());
    }

    public function testExceptionChaining(): void
    {
        $originalException = new \InvalidArgumentException('Original error');
        $entityException = new EntityNotFoundException('App\\Entity\\User', 123, 'Entity not found', 404, $originalException);
        $formatterException = new FormatterException('Formatter error', 500, $entityException);

        // Vérifier la chaîne d'exceptions
        $this->assertSame($entityException, $formatterException->getPrevious());
        $this->assertSame($originalException, $formatterException->getPrevious()->getPrevious());
        $this->assertNull($formatterException->getPrevious()->getPrevious()->getPrevious());
    }

    public function testExceptionWithSpecialCharacters(): void
    {
        // Test avec des caractères spéciaux
        $entityClass = 'App\\Entity\\User"With\'Quotes';

        $exception1 = new EntityNotAllowedException($entityClass);
        $this->assertStringContainsString($entityClass, $exception1->getMessage());

        $exception2 = new EntityNotFoundException($entityClass, 1);
        $this->assertStringContainsString($entityClass, $exception2->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        // Vérifier que toutes nos exceptions héritent bien de RuntimeException
        $entityNotFound = new EntityNotFoundException('App\\Entity\\Test', 1);
        $entityNotAllowed = new EntityNotAllowedException('App\\Entity\\Test');
        $formatterException = new FormatterException('test');

        $this->assertInstanceOf(\RuntimeException::class, $entityNotFound);
        $this->assertInstanceOf(\RuntimeException::class, $entityNotAllowed);
        $this->assertInstanceOf(\RuntimeException::class, $formatterException);

        // Et donc de Exception
        $this->assertInstanceOf(\Exception::class, $entityNotFound);
        $this->assertInstanceOf(\Exception::class, $entityNotAllowed);
        $this->assertInstanceOf(\Exception::class, $formatterException);

        // Et donc de Throwable
        $this->assertInstanceOf(\Throwable::class, $entityNotFound);
        $this->assertInstanceOf(\Throwable::class, $entityNotAllowed);
        $this->assertInstanceOf(\Throwable::class, $formatterException);
    }

    public function testExceptionProperties(): void
    {
        // Test des propriétés d'exception
        $originalException = new EntityNotFoundException('App\\Entity\\User', 123);
        
        $this->assertSame('App\\Entity\\User', $originalException->getEntityClass());
        $this->assertSame(123, $originalException->getId());
        $this->assertStringContainsString('App\\Entity\\User', $originalException->getMessage());
        $this->assertStringContainsString('123', $originalException->getMessage());
    }

    public function testEntityNotFoundExceptionWithDifferentTypes(): void
    {
        // Test avec différents types d'ID
        $stringIdException = new EntityNotFoundException('App\\Entity\\User', 'user-uuid-123');
        $this->assertStringContainsString('user-uuid-123', $stringIdException->getMessage());
        $this->assertSame('user-uuid-123', $stringIdException->getId());

        $intIdException = new EntityNotFoundException('App\\Entity\\User', 456);
        $this->assertStringContainsString('456', $intIdException->getMessage());
        $this->assertSame(456, $intIdException->getId());
    }

    public function testDefaultMessages(): void
    {
        // Test des messages par défaut
        $entityNotFound = new EntityNotFoundException('App\\Entity\\User', 123);
        $this->assertStringContainsString('App\\Entity\\User', $entityNotFound->getMessage());
        $this->assertStringContainsString('123', $entityNotFound->getMessage());

        $entityNotAllowed = new EntityNotAllowedException('App\\Entity\\User');
        $this->assertStringContainsString('App\\Entity\\User', $entityNotAllowed->getMessage());
        $this->assertStringContainsString('autorisée', $entityNotAllowed->getMessage());
    }

    public function testExceptionCodes(): void
    {
        // Test avec des codes d'erreur spécifiques
        $notFoundException = new EntityNotFoundException('App\\Entity\\User', 1, 'Not found', 404);
        $this->assertSame(404, $notFoundException->getCode());

        $notAllowedException = new EntityNotAllowedException('App\\Entity\\User', 'Forbidden', 403);
        $this->assertSame(403, $notAllowedException->getCode());

        $formatterException = new FormatterException('Bad format', 422);
        $this->assertSame(422, $formatterException->getCode());
    }
}