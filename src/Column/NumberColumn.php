<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Column;

/**
 * Colonne pour l'affichage de nombres avec formatage personnalisé
 * 
 * Fonctionnalités :
 * - Formatage des nombres avec séparateurs de milliers
 * - Contrôle du nombre de décimales
 * - Support des devises et unités
 * - Formatage selon les locales
 * - Support de l'édition inline
 */
class NumberColumn extends AbstractColumn
{
    public const FORMAT_INTEGER = 'integer';
    public const FORMAT_DECIMAL = 'decimal';
    public const FORMAT_CURRENCY = 'currency';
    public const FORMAT_PERCENTAGE = 'percentage';
    public const FORMAT_CUSTOM = 'custom';

    private string $format = self::FORMAT_DECIMAL;
    private int $decimals = 2;
    private string $decimalSeparator = ',';
    private string $thousandsSeparator = ' ';
    private ?string $prefix = null;
    private ?string $suffix = null;
    private ?string $currency = null;
    private ?string $locale = null;
    private bool $showZero = true;
    private ?string $nullDisplay = null;
    private bool $useIntl = false;
    private array $intlOptions = [];

    public function __construct(
        string $name,
        string $propertyPath,
        string $label,
        array $options = []
    ) {
        // Extraire les options de base pour AbstractColumn
        $sortable = $options['sortable'] ?? true;
        $searchable = $options['searchable'] ?? false;
        
        parent::__construct($name, $propertyPath, $label, $sortable, $searchable, $options);
        
        // Appliquer les options spécifiques aux nombres
        if (isset($options['format'])) {
            $this->setFormat($options['format']);
        }
        
        if (isset($options['decimals'])) {
            $this->setDecimals($options['decimals']);
        }
        
        if (isset($options['decimal_separator'])) {
            $this->setDecimalSeparator($options['decimal_separator']);
        }
        
        if (isset($options['thousands_separator'])) {
            $this->setThousandsSeparator($options['thousands_separator']);
        }
        
        if (isset($options['prefix'])) {
            $this->setPrefix($options['prefix']);
        }
        
        if (isset($options['suffix'])) {
            $this->setSuffix($options['suffix']);
        }
        
        if (isset($options['currency'])) {
            $this->setCurrency($options['currency']);
        }
        
        if (isset($options['locale'])) {
            $this->setLocale($options['locale']);
        }
        
        if (isset($options['show_zero'])) {
            $this->setShowZero($options['show_zero']);
        }
        
        if (isset($options['null_display'])) {
            $this->setNullDisplay($options['null_display']);
        }
        
        if (isset($options['use_intl'])) {
            $this->setUseIntl($options['use_intl']);
        }
        
        if (isset($options['intl_options'])) {
            $this->setIntlOptions($options['intl_options']);
        }
    }

    protected function doRender(mixed $value, object $entity): string
    {
        if ($value === null) {
            return $this->nullDisplay ?? '';
        }

        if (!$this->showZero && ($value == 0 || $value === '0')) {
            return '';
        }

        // Conversion en nombre si ce n'est pas déjà fait
        $numericValue = $this->convertToNumeric($value);
        
        if ($numericValue === null) {
            return $this->nullDisplay ?? (string)$value;
        }

        return $this->formatNumber($numericValue);
    }

    private function convertToNumeric(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        if (is_string($value)) {
            // Nettoyage basique des chaînes de caractères
            $cleaned = preg_replace('/[^\d,.-]/', '', $value);
            if (is_numeric($cleaned)) {
                return (float)$cleaned;
            }
        }

        return null;
    }

    private function formatNumber(float $value): string
    {
        // Utilisation de l'extension Intl si disponible et demandée
        if ($this->useIntl && extension_loaded('intl')) {
            return $this->formatWithIntl($value);
        }

        // Formatage manuel
        return $this->formatManually($value);
    }

    private function formatWithIntl(float $value): string
    {
        $locale = $this->locale ?? \Locale::getDefault();
        
        switch ($this->format) {
            case self::FORMAT_CURRENCY:
                $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
                if ($this->currency) {
                    return $formatter->formatCurrency($value, $this->currency);
                }
                break;
                
            case self::FORMAT_PERCENTAGE:
                $formatter = new \NumberFormatter($locale, \NumberFormatter::PERCENT);
                break;
                
            case self::FORMAT_INTEGER:
                $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
                $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
                break;
                
            default:
                $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
                $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->decimals);
                break;
        }

        // Appliquer les options Intl supplémentaires
        if (isset($formatter) && !empty($this->intlOptions)) {
            foreach ($this->intlOptions as $attribute => $attributeValue) {
                $formatter->setAttribute($attribute, $attributeValue);
            }
        }

        $formatted = isset($formatter) ? $formatter->format($value) : (string)$value;
        
        return $this->addPrefixSuffix($formatted);
    }

    private function formatManually(float $value): string
    {
        switch ($this->format) {
            case self::FORMAT_INTEGER:
                $formatted = number_format($value, 0, $this->decimalSeparator, $this->thousandsSeparator);
                break;
                
            case self::FORMAT_PERCENTAGE:
                $formatted = number_format($value * 100, $this->decimals, $this->decimalSeparator, $this->thousandsSeparator) . ' %';
                break;
                
            case self::FORMAT_CURRENCY:
                $formatted = number_format($value, $this->decimals, $this->decimalSeparator, $this->thousandsSeparator);
                if ($this->currency) {
                    $formatted = $this->currency . ' ' . $formatted;
                }
                break;
                
            default:
                $formatted = number_format($value, $this->decimals, $this->decimalSeparator, $this->thousandsSeparator);
                break;
        }

        return $this->addPrefixSuffix($formatted);
    }

    private function addPrefixSuffix(string $formatted): string
    {
        if ($this->prefix) {
            $formatted = $this->prefix . $formatted;
        }
        
        if ($this->suffix) {
            $formatted = $formatted . $this->suffix;
        }
        
        return $formatted;
    }

    // Getters et Setters

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $validFormats = [
            self::FORMAT_INTEGER,
            self::FORMAT_DECIMAL,
            self::FORMAT_CURRENCY,
            self::FORMAT_PERCENTAGE,
            self::FORMAT_CUSTOM
        ];
        
        if (!in_array($format, $validFormats)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid format "%s". Valid formats are: %s',
                $format,
                implode(', ', $validFormats)
            ));
        }
        
        $this->format = $format;
        return $this;
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function setDecimals(int $decimals): self
    {
        if ($decimals < 0) {
            throw new \InvalidArgumentException('Decimals must be >= 0');
        }
        
        $this->decimals = $decimals;
        return $this;
    }

    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    public function setDecimalSeparator(string $decimalSeparator): self
    {
        $this->decimalSeparator = $decimalSeparator;
        return $this;
    }

    public function getThousandsSeparator(): string
    {
        return $this->thousandsSeparator;
    }

    public function setThousandsSeparator(string $thousandsSeparator): self
    {
        $this->thousandsSeparator = $thousandsSeparator;
        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function setSuffix(?string $suffix): self
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function isShowZero(): bool
    {
        return $this->showZero;
    }

    public function setShowZero(bool $showZero): self
    {
        $this->showZero = $showZero;
        return $this;
    }

    public function getNullDisplay(): ?string
    {
        return $this->nullDisplay;
    }

    public function setNullDisplay(?string $nullDisplay): self
    {
        $this->nullDisplay = $nullDisplay;
        return $this;
    }

    public function isUseIntl(): bool
    {
        return $this->useIntl;
    }

    public function setUseIntl(bool $useIntl): self
    {
        $this->useIntl = $useIntl;
        return $this;
    }

    public function getIntlOptions(): array
    {
        return $this->intlOptions;
    }

    public function setIntlOptions(array $intlOptions): self
    {
        $this->intlOptions = $intlOptions;
        return $this;
    }

    // Méthodes de convenance pour créer des colonnes préconfigurées

    public static function currency(
        string $name,
        string $propertyPath,
        string $label,
        string $currency = 'EUR',
        int $decimals = 2
    ): self {
        return new self($name, $propertyPath, $label, [
            'format' => self::FORMAT_CURRENCY,
            'currency' => $currency,
            'decimals' => $decimals,
        ]);
    }

    public static function percentage(
        string $name,
        string $propertyPath,
        string $label,
        int $decimals = 1
    ): self {
        return new self($name, $propertyPath, $label, [
            'format' => self::FORMAT_PERCENTAGE,
            'decimals' => $decimals,
        ]);
    }

    public static function integer(
        string $name,
        string $propertyPath,
        string $label,
        string $thousandsSeparator = ' '
    ): self {
        return new self($name, $propertyPath, $label, [
            'format' => self::FORMAT_INTEGER,
            'thousands_separator' => $thousandsSeparator,
        ]);
    }

    public static function decimal(
        string $name,
        string $propertyPath,
        string $label,
        int $decimals = 2,
        string $thousandsSeparator = ' ',
        string $decimalSeparator = ','
    ): self {
        return new self($name, $propertyPath, $label, [
            'format' => self::FORMAT_DECIMAL,
            'decimals' => $decimals,
            'thousands_separator' => $thousandsSeparator,
            'decimal_separator' => $decimalSeparator,
        ]);
    }
}