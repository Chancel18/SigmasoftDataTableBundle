<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Column;

final class BadgeColumn extends AbstractColumn
{
    protected function doRender(mixed $value, object $entity): string
    {
        if ($value === null) {
            return $this->getOption('empty_value', '');
        }

        $badgeClass = $this->getOption('badge_class', 'bg-secondary');
        $mapping = $this->getOption('value_mapping', []);
        
        if (is_iterable($value) && !is_string($value)) {
            $badges = [];
            foreach ($value as $item) {
                $displayValue = $mapping[$item] ?? $item;
                $badges[] = sprintf('<span class="badge %s me-1">%s</span>', $badgeClass, htmlspecialchars((string) $displayValue));
            }
            return implode('', $badges);
        }

        $displayValue = $mapping[$value] ?? $value;
        
        return sprintf('<span class="badge %s">%s</span>', $badgeClass, htmlspecialchars((string) $displayValue));
    }
}
