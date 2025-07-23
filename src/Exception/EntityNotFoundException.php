<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Exception;

/**
 * Exception lancée lorsqu'une entité n'est pas trouvée
 */
class EntityNotFoundException extends \RuntimeException
{
    public function __construct(
        private readonly string $entityClass, 
        private readonly int|string $id, 
        string $message = null, 
        int $code = 0, 
        \Throwable $previous = null
    ) {
        $message = $message ?? sprintf('L\'entité %s avec l\'identifiant %s n\'a pas été trouvée', $entityClass, $id);
        parent::__construct($message, $code, $previous);
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getId(): int|string
    {
        return $this->id;
    }
}