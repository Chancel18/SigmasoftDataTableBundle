<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Column;

final class TextColumn extends AbstractColumn
{
    protected function doRender(mixed $value, object $entity): string
    {
        if ($value === null) {
            return $this->getOption('empty_value', '');
        }

        $text = (string) $value;
        
        if ($this->getOption('truncate')) {
            $length = $this->getOption('truncate_length', 50);
            if (strlen($text) > $length) {
                $text = substr($text, 0, $length) . '...';
            }
        }

        if ($this->getOption('escape', true)) {
            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }

        return $text;
    }
}
