<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Exception;

use Exception;

class DataTableException extends Exception
{
    public static function invalidEntityClass(string $entityClass): self
    {
        return new self(sprintf('Entity class "%s" not found or is not a valid Doctrine entity.', $entityClass));
    }

    public static function columnNotFound(string $columnName): self
    {
        return new self(sprintf('Column "%s" not found in configuration.', $columnName));
    }

    public static function invalidSortDirection(string $direction): self
    {
        return new self(sprintf('Invalid sort direction "%s". Must be "asc" or "desc".', $direction));
    }

    public static function invalidPage(int $page): self
    {
        return new self(sprintf('Invalid page number "%d". Must be greater than 0.', $page));
    }

    public static function invalidItemsPerPage(int $itemsPerPage): self
    {
        return new self(sprintf('Invalid items per page "%d". Must be greater than 0.', $itemsPerPage));
    }

    public static function invalidColumnType(string $columnType): self
    {
        return new self(sprintf('Invalid column type "%s".', $columnType));
    }

    public static function configurationNotFound(string $id): self
    {
        return new self(sprintf('DataTable configuration with ID "%s" not found.', $id));
    }

    public static function invalidFieldPath(string $fieldPath, string $entityClass): self
    {
        return new self(sprintf('Invalid field path "%s" for entity "%s".', $fieldPath, $entityClass));
    }
}
