<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Cache\CacheItemPoolInterface;
use Sigmasoft\DataTableBundle\Exception\FormatterException;

/**
 * Service pour formater les valeurs des colonnes dans les DataTables
 */
class ValueFormatter
{
    private PropertyAccessorInterface $propertyAccessor;
    private array $customFormatters = [];
    private array $formatCache = [];
    private ?CacheItemPoolInterface $cache = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly array $defaultConfig = [],
        ?PropertyAccessorInterface $propertyAccessor = null,
        ?CacheItemPoolInterface $cache = null
    ) {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
        $this->cache = $cache;
    }

    /**
     * Extrait une valeur d'un objet ou tableau en utilisant un chemin de propriété
     * 
     * @param mixed $item L'objet ou tableau source
     * @param string $field Le chemin de la propriété (peut être imbriqué avec '.')
     * @return mixed La valeur extraite ou null si non trouvée
     */
    public function extractValue(mixed $item, string $field): mixed
    {
        if (is_null($item)) {
            return null;
        }

        try {
            // Utilisation optimisée de PropertyAccessor pour les propriétés imbriquées
            if (str_contains($field, '.')) {
                return $this->getNestedProperty($item, $field);
            }
            
            return $this->getSimpleProperty($item, $field);
        } catch (\Throwable $e) {
            // On pourrait logger l'exception ici si un logger était disponible
            return null;
        }
    }

    /**
     * Formate une valeur selon le format spécifié
     * 
     * @param mixed $value La valeur à formater
     * @param string $field Le nom du champ (utile pour certains formatters)
     * @param array $formatConfig Configuration spécifique pour le formattage
     * @return mixed La valeur formatée
     */
    public function formatValue(mixed $value, string $field, array $formatConfig = []): mixed
    {
        // Traitement des valeurs null
        if (is_null($value)) {
            return $this->formatNull($formatConfig);
        }

        $config = array_merge($this->defaultConfig, $formatConfig);
        $format = $config['format'] ?? $this->detectFormat($value);
        
        // Utiliser un formatter personnalisé s'il existe
        if (isset($this->customFormatters[$format])) {
            return $this->formatWithCustomFormatter($format, $value, $config);
        }

        // Création d'une clé de cache pour les opérations coûteuses
        $cacheKey = md5(serialize([$format, $value, $config]));
        if (isset($this->formatCache[$cacheKey])) {
            return $this->formatCache[$cacheKey];
        }

        $result = match ($format) {
            'boolean', 'boolean_badge' => $this->formatBoolean($value, $config),
            'date' => $this->formatDate($value, $config),
            'datetime' => $this->formatDateTime($value, $config),
            'time' => $this->formatTime($value, $config),
            'currency', 'money' => $this->formatCurrency($value, $config),
            'number' => $this->formatNumber($value, $config),
            'percentage' => $this->formatPercentage($value, $config),
            'badge' => $this->formatBadge($value, $config),
            'array' => $this->formatArray($value, $config),
            'json' => $this->formatJson($value, $config),
            'email' => $this->formatEmail($value, $config),
            'url' => $this->formatUrl($value, $config),
            'phone' => $this->formatPhone($value, $config),
            'file_size' => $this->formatFileSize($value, $config),
            'duration' => $this->formatDuration($value, $config),
            'status_badge' => $this->formatStatusBadge($value, $config),
            'stock_level' => $this->formatStockLevel($value, $config),
            'truncate' => $this->formatTruncate($value, $config),
            'capitalize' => $this->formatCapitalize($value, $config),
            'uppercase' => strtoupper((string) $value),
            'lowercase' => strtolower((string) $value),
            'html' => $this->formatHtml($value, $config),
            'raw' => $value,
            default => $this->formatText($value, $config)
        };
        
        // Mise en cache du résultat pour les futures utilisations
        $this->formatCache[$cacheKey] = $result;
        if (count($this->formatCache) > 100) {
            // Limite la taille du cache
            array_shift($this->formatCache);
        }
        
        return $result;
    }

    /**
     * Récupère une propriété simple d'un objet ou d'un tableau
     */
    private function getSimpleProperty(mixed $item, string $property): mixed
    {
        if (is_array($item)) {
            return $item[$property] ?? null;
        }

        if (is_object($item)) {
            try {
                // Utilise PropertyAccessor si possible pour une meilleure performance
                if ($this->propertyAccessor->isReadable($item, $property)) {
                    return $this->propertyAccessor->getValue($item, $property);
                }
                
                // Fallback sur les méthodes d'accès standard
                $getter = 'get' . ucfirst($property);
                if (method_exists($item, $getter)) {
                    return $item->$getter();
                }

                // Méthodes pour les booléens
                $isMethod = 'is' . ucfirst($property);
                if (method_exists($item, $isMethod)) {
                    return $item->$isMethod();
                }

                $hasMethod = 'has' . ucfirst($property);
                if (method_exists($item, $hasMethod)) {
                    return $item->$hasMethod();
                }

                // Accès direct à la propriété si accessible
                if (property_exists($item, $property) && isset($item->$property)) {
                    return $item->$property;
                }
            } catch (\Throwable $e) {
                // Silencieux, retourne null
            }
        }

        return null;
    }

    /**
     * Récupère une propriété imbriquée en utilisant PropertyAccessor
     */
    private function getNestedProperty(mixed $item, string $field): mixed
    {
        try {
            // Conversion du format "user.profile.name" en "[user][profile][name]"
            $path = str_replace('.', '][', $field);
            $path = "[{$path}]";
            
            if ($this->propertyAccessor->isReadable($item, $path)) {
                return $this->propertyAccessor->getValue($item, $path);
            }
            
            // Fallback sur l'approche itérative si PropertyAccessor échoue
            $parts = explode('.', $field);
            $current = $item;

            foreach ($parts as $part) {
                if (is_null($current)) {
                    return null;
                }
                $current = $this->getSimpleProperty($current, $part);
            }

            return $current;
        } catch (\Throwable $e) {
            // Approche itérative en cas d'échec
            $parts = explode('.', $field);
            $current = $item;

            foreach ($parts as $part) {
                if (is_null($current)) {
                    return null;
                }
                $current = $this->getSimpleProperty($current, $part);
            }

            return $current;
        }
    }

    /**
     * Détecte automatiquement le format d'une valeur
     */
    private function detectFormat(mixed $value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if ($value instanceof \DateTimeInterface) {
            return 'datetime';
        }

        if (is_numeric($value)) {
            return 'number';
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_string($value)) {
            // Vérifications plus efficaces pour les types communs
            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return 'email';
            }
            
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return 'url';
            }
            
            // Détection de format JSON
            if (str_starts_with(trim($value), '{') && json_decode($value) !== null) {
                return 'json';
            }
            
            // Détection format date
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return 'date';
            }
        }

        return 'text';
    }

    private function formatNull(array $config): string
    {
        return $config['null_display'] ?? '-';
    }

    private function formatBoolean(mixed $value, array $config): string
    {
        $boolValue = (bool) $value;
        
        if ($config['format'] === 'boolean_badge') {
            $trueClass = $config['true_class'] ?? 'badge bg-success';
            $falseClass = $config['false_class'] ?? 'badge bg-secondary';
            $trueLabel = $config['true_label'] ?? $this->translator->trans('datatable.boolean.yes', [], 'datatable');
            $falseLabel = $config['false_label'] ?? $this->translator->trans('datatable.boolean.no', [], 'datatable');
            
            $class = $boolValue ? $trueClass : $falseClass;
            $label = $boolValue ? $trueLabel : $falseLabel;
            
            return "<span class=\"{$class}\">{$label}</span>";
        }

        return $boolValue 
            ? ($config['true_label'] ?? $this->translator->trans('datatable.boolean.yes', [], 'datatable'))
            : ($config['false_label'] ?? $this->translator->trans('datatable.boolean.no', [], 'datatable'));
    }

    /**
     * Convertit une valeur en objet DateTime si nécessaire
     */
    private function toDateTime(mixed $value): ?\DateTimeInterface
    {
        if ($value instanceof \DateTimeInterface) {
            return $value;
        }
        
        if (is_string($value) && !empty($value)) {
            try {
                return new \DateTime($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    private function formatDate(mixed $value, array $config): string
    {
        $dateTime = $this->toDateTime($value);
        if (!$dateTime) {
            return (string) $value;
        }

        $format = $config['date_format'] ?? $this->defaultConfig['format_date']['date_format'] ?? 'd/m/Y';
        return $dateTime->format($format);
    }

    private function formatDateTime(mixed $value, array $config): string
    {
        $dateTime = $this->toDateTime($value);
        if (!$dateTime) {
            return (string) $value;
        }

        $format = $config['datetime_format'] ?? $this->defaultConfig['format_date']['datetime_format'] ?? 'd/m/Y H:i';
        return $dateTime->format($format);
    }

    private function formatTime(mixed $value, array $config): string
    {
        $dateTime = $this->toDateTime($value);
        if (!$dateTime) {
            return (string) $value;
        }

        $format = $config['time_format'] ?? $this->defaultConfig['format_date']['time_format'] ?? 'H:i';
        return $dateTime->format($format);
    }

    private function formatCurrency(mixed $value, array $config): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        $currency = $config['currency'] ?? 'EUR';
        $decimals = $config['decimal'] ?? $this->defaultConfig['format_number']['decimal'] ?? 2;
        $decimalSeparator = $config['decimal_separator'] ?? $this->defaultConfig['format_number']['decimal_separator'] ?? ',';
        $thousandSeparator = $config['thousand_separator'] ?? $this->defaultConfig['format_number']['thousand_separator'] ?? ' ';

        $formatted = number_format((float) $value, $decimals, $decimalSeparator, $thousandSeparator);
        
        return match ($currency) {
            'EUR' => $formatted . ' €',
            'USD' => '$' . $formatted,
            'GBP' => '£' . $formatted,
            default => $formatted . ' ' . $currency
        };
    }

    private function formatNumber(mixed $value, array $config): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        $decimals = $config['decimal'] ?? $this->defaultConfig['format_number']['decimal'] ?? 2;
        $decimalSeparator = $config['decimal_separator'] ?? $this->defaultConfig['format_number']['decimal_separator'] ?? ',';
        $thousandSeparator = $config['thousand_separator'] ?? $this->defaultConfig['format_number']['thousand_separator'] ?? ' ';

        return number_format((float) $value, $decimals, $decimalSeparator, $thousandSeparator);
    }

    private function formatPercentage(mixed $value, array $config): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        $decimals = $config['decimal'] ?? 1;
        $decimalSeparator = $config['decimal_separator'] ?? $this->defaultConfig['format_number']['decimal_separator'] ?? ',';
        $thousandSeparator = $config['thousand_separator'] ?? $this->defaultConfig['format_number']['thousand_separator'] ?? ' ';
        
        $multiplier = $config['multiply'] ?? true ? 100 : 1;
        $formatted = number_format((float) $value * $multiplier, $decimals, $decimalSeparator, $thousandSeparator);
        
        return $formatted . ' %';
    }

    private function formatBadge(mixed $value, array $config): string
    {
        $variant = $config['badge_variant'] ?? 'primary';
        $class = $config['badge_class'] ?? "badge bg-{$variant}";
        
        if (is_array($value)) {
            $badges = array_map(fn($v) => "<span class=\"{$class}\">" . htmlspecialchars((string) $v) . "</span>", $value);
            return implode(' ', $badges);
        }

        return "<span class=\"{$class}\">" . htmlspecialchars((string) $value) . "</span>";
    }

    private function formatArray(mixed $value, array $config): string
    {
        if (!is_array($value)) {
            return (string) $value;
        }

        $separator = $config['separator'] ?? ', ';
        $maxItems = $config['max_items'] ?? null;
        
        if ($maxItems && count($value) > $maxItems) {
            $displayed = array_slice($value, 0, $maxItems);
            $remaining = count($value) - $maxItems;
            return implode($separator, $displayed) . $separator . "... (+{$remaining})";
        }

        return implode($separator, $value);
    }

    private function formatJson(mixed $value, array $config): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        if ($config['html_safe'] ?? true) {
            $flags |= JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        }

        return json_encode($value, $flags);
    }

    private function formatEmail(mixed $value, array $config): string
    {
        if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return (string) $value;
        }

        if ($config['clickable'] ?? true) {
            return "<a href=\"mailto:{$value}\">{$value}</a>";
        }

        return $value;
    }

    private function formatUrl(mixed $value, array $config): string
    {
        if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_URL)) {
            return (string) $value;
        }

        if ($config['clickable'] ?? true) {
            $target = $config['target'] ?? '_blank';
            $text = $config['text'] ?? $value;
            $rel = $config['rel'] ?? 'noopener noreferrer';
            return "<a href=\"{$value}\" target=\"{$target}\" rel=\"{$rel}\">{$text}</a>";
        }

        return $value;
    }

    private function formatPhone(mixed $value, array $config): string
    {
        $phone = preg_replace('/[^+\d]/', '', (string) $value);
        
        if ($config['clickable'] ?? true) {
            return "<a href=\"tel:{$phone}\">{$value}</a>";
        }

        return (string) $value;
    }

    private function formatFileSize(mixed $value, array $config): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        $bytes = (int) $value;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $precision = $config['precision'] ?? 2;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function formatDuration(mixed $value, array $config): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        $seconds = (int) $value;
        $format = $config['format'] ?? 'auto';

        if ($format === 'auto') {
            if ($seconds < 60) {
                return $seconds . 's';
            } elseif ($seconds < 3600) {
                return floor($seconds / 60) . 'min ' . ($seconds % 60) . 's';
            } else {
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                return "{$hours}h {$minutes}min";
            }
        }

        return gmdate($format, $seconds);
    }

    private function formatStatusBadge(mixed $value, array $config): string
    {
        $statusMap = $config['status_map'] ?? [
            'active' => 'success',
            'inactive' => 'secondary',
            'pending' => 'warning',
            'error' => 'danger',
            'success' => 'success',
            'failed' => 'danger',
        ];

        $valueString = strtolower((string) $value);
        $variant = $statusMap[$valueString] ?? 'primary';
        $class = "badge bg-{$variant}";
        $label = $config['labels'][$valueString] ?? ucfirst((string) $value);

        return "<span class=\"{$class}\">{$label}</span>";
    }

    private function formatStockLevel(mixed $value, array $config): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        $stock = (int) $value;
        $lowThreshold = $config['low_threshold'] ?? 10;
        $outOfStockThreshold = $config['out_of_stock_threshold'] ?? 0;

        if ($stock <= $outOfStockThreshold) {
            $class = 'badge bg-danger';
            $label = $config['out_of_stock_label'] ?? 'Rupture';
        } elseif ($stock <= $lowThreshold) {
            $class = 'badge bg-warning';
            $label = $config['low_stock_label'] ?? 'Stock faible';
        } else {
            $class = 'badge bg-success';
            $label = $config['in_stock_label'] ?? 'En stock';
        }

        if ($config['show_quantity'] ?? true) {
            $label .= " ({$stock})";
        }

        return "<span class=\"{$class}\">{$label}</span>";
    }

    private function formatTruncate(mixed $value, array $config): string
    {
        $text = (string) $value;
        $length = $config['length'] ?? 50;
        $suffix = $config['suffix'] ?? '...';
        $preserveWords = $config['preserve_words'] ?? true;

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        if ($preserveWords) {
            $truncated = mb_substr($text, 0, $length);
            $lastSpace = mb_strrpos($truncated, ' ');
            if ($lastSpace !== false) {
                $truncated = mb_substr($truncated, 0, $lastSpace);
            }
        } else {
            $truncated = mb_substr($text, 0, $length);
        }

        return $truncated . $suffix;
    }

    private function formatCapitalize(mixed $value, array $config): string
    {
        $text = (string) $value;
        $mode = $config['mode'] ?? 'words'; // 'words', 'first', 'sentences'

        return match ($mode) {
            'words' => mb_convert_case($text, MB_CASE_TITLE),
            'first' => mb_convert_case(mb_substr($text, 0, 1), MB_CASE_UPPER) . mb_substr($text, 1),
            'sentences' => $this->capitalizeSentences($text),
            default => $text
        };
    }

    private function formatHtml(mixed $value, array $config): string
    {
        $html = (string) $value;

        if ($config['strip_tags'] ?? false) {
            $allowedTags = $config['allowed_tags'] ?? '';
            return strip_tags($html, $allowedTags);
        }

        if ($config['escape'] ?? false) {
            return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5);
        }

        return $html;
    }

    private function formatText(mixed $value, array $config): string
    {
        $text = (string) $value;

        if ($config['escape'] ?? true) {
            $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);
        }

        if ($config['nl2br'] ?? false) {
            $text = nl2br($text);
        }

        return $text;
    }

    private function capitalizeSentences(string $text): string
    {
        $sentences = preg_split('/([.!?]+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';
        
        for ($i = 0; $i < count($sentences); $i += 2) {
            if (isset($sentences[$i]) && mb_strlen($sentences[$i])) {
                $sentences[$i] = mb_strtoupper(mb_substr($sentences[$i], 0, 1)) . mb_substr($sentences[$i], 1);
            }
            
            $result .= $sentences[$i] . ($sentences[$i + 1] ?? '');
        }
        
        return $result;
    }

    /**
     * Formate plusieurs valeurs en une seule opération (optimisé)
     */
    public function formatMultipleValues(array $items, string $field, array $formatConfig = []): array
    {
        $result = [];
        $count = count($items);
        
        // Pré-allocation du tableau pour éviter les redimensionnements
        $result = array_fill(0, $count, null);
        
        $i = 0;
        foreach ($items as $item) {
            $value = $this->extractValue($item, $field);
            $result[$i++] = $this->formatValue($value, $field, $formatConfig);
        }
        
        return $result;
    }

    /**
     * Ajoute un formatter personnalisé
     */
    public function addCustomFormatter(string $type, callable $formatter): void
    {
        $this->customFormatters[$type] = $formatter;
    }

    /**
     * Vérifie si un formatter personnalisé existe
     */
    public function hasCustomFormatter(string $type): bool
    {
        return isset($this->customFormatters[$type]);
    }

    /**
     * Récupère un formatter personnalisé
     */
    public function getCustomFormatter(string $type): ?callable
    {
        return $this->customFormatters[$type] ?? null;
    }

    /**
     * Utilise un formatter personnalisé
     * 
     * @throws \InvalidArgumentException Si le formatter n'existe pas
     */
    public function formatWithCustomFormatter(string $type, mixed $value, array $config = []): mixed
    {
        $formatter = $this->getCustomFormatter($type);
        if (!$formatter) {
            throw new \InvalidArgumentException("Custom formatter '{$type}' not found");
        }

        return $formatter($value, $config, $this);
    }

    /**
     * Retourne la liste des formats disponibles
     */
    public function getAvailableFormats(): array
    {
        return [
            'boolean',
            'boolean_badge',
            'date',
            'datetime',
            'time',
            'currency',
            'money',
            'number',
            'percentage',
            'badge',
            'array',
            'json',
            'email',
            'url',
            'phone',
            'file_size',
            'duration',
            'status_badge',
            'stock_level',
            'truncate',
            'capitalize',
            'uppercase',
            'lowercase',
            'html',
            'raw',
            'text',
            ...array_keys($this->customFormatters)
        ];
    }

    /**
     * Valide la configuration d'un format
     */
    public function validateFormatConfig(string $format, array $config): array
    {
        $errors = [];

        switch ($format) {
            case 'currency':
            case 'money':
                if (isset($config['currency']) && !in_array($config['currency'], ['EUR', 'USD', 'GBP'])) {
                    $errors[] = "Devise non supportée: {$config['currency']}";
                }
                break;

            case 'truncate':
                if (isset($config['length']) && (!is_int($config['length']) || $config['length'] <= 0)) {
                    $errors[] = "La longueur de troncature doit être un entier positif";
                }
                break;

            case 'date':
            case 'datetime':
            case 'time':
                $formatKey = $format . '_format';
                if (isset($config[$formatKey])) {
                    try {
                        (new \DateTime())->format($config[$formatKey]);
                    } catch (\Exception $e) {
                        $errors[] = "Format de {$format} invalide: {$config[$formatKey]}";
                    }
                }
                break;

            case 'stock_level':
                if (isset($config['low_threshold']) && (!is_int($config['low_threshold']) || $config['low_threshold'] < 0)) {
                    $errors[] = "Le seuil bas doit être un entier non négatif";
                }
                break;
        }

        return $errors;
    }

    /**
     * Crée une chaîne de formatters à appliquer séquentiellement
     */
    public function createFormatterChain(array $formatters): callable
    {
        return function (mixed $value, string $field, array $config = []) use ($formatters) {
            foreach ($formatters as $formatter) {
                if (is_string($formatter)) {
                    $value = $this->formatValue($value, $field, array_merge($config, ['format' => $formatter]));
                } elseif (is_array($formatter)) {
                    $format = $formatter['format'] ?? 'text';
                    $formatConfig = array_merge($config, $formatter['config'] ?? []);
                    $value = $this->formatValue($value, $field, array_merge($formatConfig, ['format' => $format]));
                } elseif (is_callable($formatter)) {
                    $value = $formatter($value, $field, $config);
                }
            }
            return $value;
        };
    }

    /**
     * Crée une instance avec la configuration par défaut
     */
    public static function createWithDefaults(TranslatorInterface $translator): self
    {
        $defaultConfig = [
            'format_number' => [
                'decimal' => 2,
                'decimal_separator' => ',',
                'thousand_separator' => ' ',
            ],
            'format_date' => [
                'date_format' => 'd/m/Y',
                'datetime_format' => 'd/m/Y H:i',
                'time_format' => 'H:i',
            ],
            'escape' => true,
            'null_display' => '-',
        ];

        return new self($translator, $defaultConfig);
    }

    /**
     * Vide le cache interne
     */
    public function clearCache(): void
    {
        $this->formatCache = [];
    }
    
    /**
     * Vérifie si une valeur peut être formatée avec un format spécifique
     *
     * @param string $format Le format à vérifier
     * @param mixed $value La valeur à vérifier
     * @return bool True si la valeur peut être formatée, false sinon
     */
    public function canFormat(string $format, mixed $value): bool
    {
        if ($format === 'raw') {
            return true;
        }
        
        return match ($format) {
            'boolean', 'boolean_badge' => is_bool($value) || is_numeric($value) || is_string($value),
            'date', 'datetime', 'time' => $value instanceof \DateTimeInterface || (is_string($value) && strtotime($value) !== false),
            'currency', 'money', 'number', 'percentage' => is_numeric($value),
            'array' => is_array($value),
            'json' => is_array($value) || (is_string($value) && json_decode($value) !== null),
            'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL),
            'url' => is_string($value) && filter_var($value, FILTER_VALIDATE_URL),
            'badge', 'status_badge', 'truncate', 'capitalize', 'uppercase', 'lowercase', 'html', 'text', 'phone' => is_scalar($value),
            'file_size', 'duration', 'stock_level' => is_numeric($value),
            default => isset($this->customFormatters[$format])
        };
    }
}