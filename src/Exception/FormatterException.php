<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Exception;

/**
 * Exception lancée lorsqu'une erreur se produit pendant le formatage des valeurs
 */
class FormatterException extends \RuntimeException
{
    /**
     * Crée une exception pour un formatter non trouvé
     */
    public static function formatterNotFound(string $type): self
    {
        return new self(sprintf('Le formatter "%s" n\'existe pas', $type));
    }

    /**
     * Crée une exception pour une configuration invalide
     */
    public static function invalidConfiguration(string $format, array $errors): self
    {
        $errorMessages = implode(', ', $errors);
        return new self(sprintf('Configuration invalide pour le format "%s": %s', $format, $errorMessages));
    }

    /**
     * Crée une exception pour une valeur non formatée
     */
    public static function unformattableValue(string $format, mixed $value): self
    {
        $type = is_object($value) ? get_class($value) : gettype($value);
        return new self(sprintf('Impossible de formater la valeur de type "%s" avec le format "%s"', $type, $format));
    }
}