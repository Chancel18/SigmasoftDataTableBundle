<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Exception;

/**
 * Exception lancée lorsqu'une entité n'est pas autorisée à être utilisée avec le DataTableService
 */
class EntityNotAllowedException extends \RuntimeException
{
    public function __construct(
        private readonly string $entityClass, 
        string $message = null, 
        int $code = 0, 
        \Throwable $previous = null
    ) {
        $message = $message ?? sprintf('L\'entité %s n\'est pas autorisée à être utilisée avec le DataTableService', $entityClass);
        parent::__construct($message, $code, $previous);
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}