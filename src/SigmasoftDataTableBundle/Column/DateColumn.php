<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Column;

final class DateColumn extends AbstractColumn
{
    protected function doRender(mixed $value, object $entity): string
    {
        if ($value === null) {
            return $this->getOption('empty_value', '');
        }

        if (!$value instanceof \DateTimeInterface) {
            return (string) $value;
        }

        $format = $this->getOption('format', 'd/m/Y');
        
        return $value->format($format);
    }
}
