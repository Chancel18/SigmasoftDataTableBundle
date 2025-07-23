<?php

namespace Sigmasoft\DataTableBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\Service\ValueFormatter;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Cache\CacheItemPoolInterface;

class ValueFormatterTest extends TestCase
{
    private ValueFormatter $formatter;
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')
            ->willReturnCallback(function ($id, $params = [], $domain = null) {
                return match ($id) {
                    'datatable.boolean.yes' => 'Oui',
                    'datatable.boolean.no' => 'Non',
                    default => $id
                };
            });

        $this->formatter = new ValueFormatter($this->translator);
    }

    public function testExtractValueFromArray(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30
        ];

        $this->assertSame('John Doe', $this->formatter->extractValue($data, 'name'));
        $this->assertSame('john@example.com', $this->formatter->extractValue($data, 'email'));
        $this->assertSame(30, $this->formatter->extractValue($data, 'age'));
        $this->assertNull($this->formatter->extractValue($data, 'nonexistent'));
    }

    public function testExtractValueFromObject(): void
    {
        $object = new class {
            private string $name = 'Jane Doe';
            private int $age = 25;
            private bool $active = true;

            public function getName(): string
            {
                return $this->name;
            }

            public function getAge(): int
            {
                return $this->age;
            }

            public function isActive(): bool
            {
                return $this->active;
            }
        };

        $this->assertSame('Jane Doe', $this->formatter->extractValue($object, 'name'));
        $this->assertSame(25, $this->formatter->extractValue($object, 'age'));
        $this->assertTrue($this->formatter->extractValue($object, 'active'));
    }

    public function testExtractNestedValue(): void
    {
        $data = [
            'user' => [
                'profile' => [
                    'name' => 'John Smith',
                    'address' => [
                        'city' => 'Paris'
                    ]
                ]
            ]
        ];

        $this->assertSame('John Smith', $this->formatter->extractValue($data, 'user.profile.name'));
        $this->assertSame('Paris', $this->formatter->extractValue($data, 'user.profile.address.city'));
        $this->assertNull($this->formatter->extractValue($data, 'user.profile.nonexistent'));
    }

    public function testExtractValueFromNull(): void
    {
        $this->assertNull($this->formatter->extractValue(null, 'any'));
    }

    public function testFormatBoolean(): void
    {
        $this->assertSame('Oui', $this->formatter->formatValue(true, 'field'));
        $this->assertSame('Non', $this->formatter->formatValue(false, 'field'));
        $this->assertSame('Oui', $this->formatter->formatValue(1, 'field', ['format' => 'boolean']));
        $this->assertSame('Non', $this->formatter->formatValue(0, 'field', ['format' => 'boolean']));
    }

    public function testFormatBooleanBadge(): void
    {
        $result = $this->formatter->formatValue(true, 'field', ['format' => 'boolean_badge']);
        $this->assertStringContainsString('badge bg-success', $result);
        $this->assertStringContainsString('Oui', $result);

        $result = $this->formatter->formatValue(false, 'field', ['format' => 'boolean_badge']);
        $this->assertStringContainsString('badge bg-secondary', $result);
        $this->assertStringContainsString('Non', $result);
    }

    public function testFormatDate(): void
    {
        $date = new \DateTime('2023-12-25 15:30:00');
        
        $this->assertSame('25/12/2023', $this->formatter->formatValue($date, 'field', ['format' => 'date']));
        $this->assertSame('25-12-2023', $this->formatter->formatValue($date, 'field', [
            'format' => 'date',
            'date_format' => 'd-m-Y'
        ]));
    }

    public function testFormatDateTime(): void
    {
        $date = new \DateTime('2023-12-25 15:30:00');
        
        $this->assertSame('25/12/2023 15:30', $this->formatter->formatValue($date, 'field', ['format' => 'datetime']));
        $this->assertSame('2023-12-25 15:30:00', $this->formatter->formatValue($date, 'field', [
            'format' => 'datetime',
            'datetime_format' => 'Y-m-d H:i:s'
        ]));
    }

    public function testFormatTime(): void
    {
        $date = new \DateTime('2023-12-25 15:30:45');
        
        $this->assertSame('15:30', $this->formatter->formatValue($date, 'field', ['format' => 'time']));
        $this->assertSame('15:30:45', $this->formatter->formatValue($date, 'field', [
            'format' => 'time',
            'time_format' => 'H:i:s'
        ]));
    }

    public function testFormatCurrency(): void
    {
        $this->assertSame('100,00 €', $this->formatter->formatValue(100, 'field', ['format' => 'currency']));
        $this->assertSame('1 234,56 €', $this->formatter->formatValue(1234.56, 'field', ['format' => 'currency']));
        $this->assertSame('$1,234.56', $this->formatter->formatValue(1234.56, 'field', [
            'format' => 'currency',
            'currency' => 'USD',
            'decimal_separator' => '.',
            'thousand_separator' => ','
        ]));
        $this->assertSame('£99.99', $this->formatter->formatValue(99.99, 'field', [
            'format' => 'currency',
            'currency' => 'GBP',
            'decimal_separator' => '.',
            'thousand_separator' => ''
        ]));
    }

    public function testFormatNumber(): void
    {
        $this->assertSame('1 234,56', $this->formatter->formatValue(1234.56, 'field', ['format' => 'number']));
        $this->assertSame('1 234,6', $this->formatter->formatValue(1234.56, 'field', [
            'format' => 'number',
            'decimal' => 1
        ]));
        $this->assertSame('1234', $this->formatter->formatValue(1234.56, 'field', [
            'format' => 'number',
            'decimal' => 0,
            'thousand_separator' => ''
        ]));
    }

    public function testFormatPercentage(): void
    {
        $this->assertSame('50,0 %', $this->formatter->formatValue(0.5, 'field', ['format' => 'percentage']));
        $this->assertSame('75,50 %', $this->formatter->formatValue(0.755, 'field', [
            'format' => 'percentage',
            'decimal' => 2
        ]));
        $this->assertSame('25,0 %', $this->formatter->formatValue(25, 'field', [
            'format' => 'percentage',
            'multiply' => false
        ]));
    }

    public function testFormatBadge(): void
    {
        $result = $this->formatter->formatValue('Active', 'field', ['format' => 'badge']);
        $this->assertStringContainsString('badge bg-primary', $result);
        $this->assertStringContainsString('Active', $result);

        $result = $this->formatter->formatValue('Warning', 'field', [
            'format' => 'badge',
            'badge_variant' => 'warning'
        ]);
        $this->assertStringContainsString('badge bg-warning', $result);
    }

    public function testFormatArray(): void
    {
        $array = ['Apple', 'Banana', 'Orange'];
        
        $this->assertSame('Apple, Banana, Orange', $this->formatter->formatValue($array, 'field', ['format' => 'array']));
        $this->assertSame('Apple|Banana|Orange', $this->formatter->formatValue($array, 'field', [
            'format' => 'array',
            'separator' => '|'
        ]));
        $this->assertSame('Apple, Banana, ... (+1)', $this->formatter->formatValue($array, 'field', [
            'format' => 'array',
            'max_items' => 2
        ]));
    }

    public function testFormatEmail(): void
    {
        $email = 'test@example.com';
        
        $result = $this->formatter->formatValue($email, 'field', ['format' => 'email']);
        $this->assertStringContainsString('mailto:test@example.com', $result);
        $this->assertStringContainsString('test@example.com', $result);

        $result = $this->formatter->formatValue($email, 'field', [
            'format' => 'email',
            'clickable' => false
        ]);
        $this->assertSame('test@example.com', $result);
    }

    public function testFormatUrl(): void
    {
        $url = 'https://example.com';
        
        $result = $this->formatter->formatValue($url, 'field', ['format' => 'url']);
        $this->assertStringContainsString('href="https://example.com"', $result);
        $this->assertStringContainsString('target="_blank"', $result);
        $this->assertStringContainsString('rel="noopener noreferrer"', $result);

        $result = $this->formatter->formatValue($url, 'field', [
            'format' => 'url',
            'text' => 'Visit Site',
            'target' => '_self'
        ]);
        $this->assertStringContainsString('Visit Site', $result);
        $this->assertStringContainsString('target="_self"', $result);
    }

    public function testFormatPhone(): void
    {
        $phone = '+33 6 12 34 56 78';
        
        $result = $this->formatter->formatValue($phone, 'field', ['format' => 'phone']);
        $this->assertStringContainsString('tel:+33612345678', $result);
        $this->assertStringContainsString('+33 6 12 34 56 78', $result);
    }

    public function testFormatFileSize(): void
    {
        $this->assertSame('1 KB', $this->formatter->formatValue(1024, 'field', ['format' => 'file_size']));
        $this->assertSame('1.5 MB', $this->formatter->formatValue(1572864, 'field', ['format' => 'file_size']));
        $this->assertSame('2.34 GB', $this->formatter->formatValue(2515396075, 'field', ['format' => 'file_size']));
        $this->assertSame('1.205 KB', $this->formatter->formatValue(1234, 'field', [
            'format' => 'file_size',
            'precision' => 3
        ]));
    }

    public function testFormatDuration(): void
    {
        $this->assertSame('45s', $this->formatter->formatValue(45, 'field', ['format' => 'duration']));
        $this->assertSame('2min 30s', $this->formatter->formatValue(150, 'field', ['format' => 'duration']));
        $this->assertSame('1h 30min', $this->formatter->formatValue(5400, 'field', ['format' => 'duration']));
        $this->assertSame('02:30:00', $this->formatter->formatValue(9000, 'field', [
            'format' => 'duration',
            'format' => 'H:i:s'
        ]));
    }

    public function testFormatTruncate(): void
    {
        $text = 'This is a very long text that needs to be truncated for display purposes.';
        
        $result = $this->formatter->formatValue($text, 'field', [
            'format' => 'truncate',
            'length' => 20
        ]);
        $this->assertSame('This is a very long...', $result);

        $result = $this->formatter->formatValue($text, 'field', [
            'format' => 'truncate',
            'length' => 30,
            'suffix' => ' [...]'
        ]);
        $this->assertStringEndsWith(' [...]', $result);
    }

    public function testFormatCapitalize(): void
    {
        $this->assertSame('Hello World', $this->formatter->formatValue('hello world', 'field', [
            'format' => 'capitalize',
            'mode' => 'words'
        ]));
        
        $this->assertSame('Hello world', $this->formatter->formatValue('hello world', 'field', [
            'format' => 'capitalize',
            'mode' => 'first'
        ]));
    }

    public function testFormatUppercaseAndLowercase(): void
    {
        $this->assertSame('HELLO WORLD', $this->formatter->formatValue('Hello World', 'field', ['format' => 'uppercase']));
        $this->assertSame('hello world', $this->formatter->formatValue('Hello World', 'field', ['format' => 'lowercase']));
    }

    public function testFormatNull(): void
    {
        $this->assertSame('-', $this->formatter->formatValue(null, 'field'));
        $this->assertSame('N/A', $this->formatter->formatValue(null, 'field', ['null_display' => 'N/A']));
    }

    public function testAddCustomFormatter(): void
    {
        $customFormatter = function ($value, $config, $formatter) {
            return strtoupper($value) . '!!!';
        };

        $this->formatter->addCustomFormatter('exclamation', $customFormatter);
        
        $this->assertTrue($this->formatter->hasCustomFormatter('exclamation'));
        $this->assertSame($customFormatter, $this->formatter->getCustomFormatter('exclamation'));
        
        $result = $this->formatter->formatValue('hello', 'field', ['format' => 'exclamation']);
        $this->assertSame('HELLO!!!', $result);
    }

    public function testFormatMultipleValues(): void
    {
        $items = [
            ['price' => 100],
            ['price' => 200],
            ['price' => 300]
        ];

        $results = $this->formatter->formatMultipleValues($items, 'price', ['format' => 'currency']);
        
        $this->assertCount(3, $results);
        $this->assertSame('100,00 €', $results[0]);
        $this->assertSame('200,00 €', $results[1]);
        $this->assertSame('300,00 €', $results[2]);
    }

    public function testGetAvailableFormats(): void
    {
        $formats = $this->formatter->getAvailableFormats();
        
        $this->assertContains('boolean', $formats);
        $this->assertContains('date', $formats);
        $this->assertContains('currency', $formats);
        $this->assertContains('email', $formats);
        $this->assertContains('truncate', $formats);
    }

    public function testValidateFormatConfig(): void
    {
        // Test valid currency config
        $errors = $this->formatter->validateFormatConfig('currency', ['currency' => 'EUR']);
        $this->assertEmpty($errors);

        // Test invalid currency
        $errors = $this->formatter->validateFormatConfig('currency', ['currency' => 'XYZ']);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Devise non supportée', $errors[0]);

        // Test invalid truncate length
        $errors = $this->formatter->validateFormatConfig('truncate', ['length' => -5]);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('entier positif', $errors[0]);

        // Test invalid date format
        $errors = $this->formatter->validateFormatConfig('date', ['date_format' => 'invalid']);
        $this->assertNotEmpty($errors);
    }

    public function testCanFormat(): void
    {
        $this->assertTrue($this->formatter->canFormat('boolean', true));
        $this->assertTrue($this->formatter->canFormat('boolean', 1));
        $this->assertTrue($this->formatter->canFormat('boolean', 'yes'));
        
        $this->assertTrue($this->formatter->canFormat('date', new \DateTime()));
        $this->assertTrue($this->formatter->canFormat('date', '2023-12-25'));
        $this->assertFalse($this->formatter->canFormat('date', 'invalid date'));
        
        $this->assertTrue($this->formatter->canFormat('currency', 100));
        $this->assertTrue($this->formatter->canFormat('currency', '100.50'));
        $this->assertFalse($this->formatter->canFormat('currency', 'not a number'));
        
        $this->assertTrue($this->formatter->canFormat('email', 'test@example.com'));
        $this->assertFalse($this->formatter->canFormat('email', 'not an email'));
        
        $this->assertTrue($this->formatter->canFormat('array', ['a', 'b', 'c']));
        $this->assertFalse($this->formatter->canFormat('array', 'not an array'));
    }

    public function testCreateFormatterChain(): void
    {
        $chain = $this->formatter->createFormatterChain([
            'uppercase',
            ['format' => 'truncate', 'config' => ['length' => 10]],
            function ($value) { return $value . '!'; }
        ]);

        $result = $chain('hello world', 'field', []);
        $this->assertSame('HELLO WORL...!', $result);
    }

    public function testClearCache(): void
    {
        // Format some values to populate cache
        $this->formatter->formatValue('test', 'field', ['format' => 'uppercase']);
        $this->formatter->formatValue('test2', 'field', ['format' => 'uppercase']);
        
        // Clear cache
        $this->formatter->clearCache();
        
        // This test mainly ensures the method exists and doesn't throw errors
        $this->assertTrue(true);
    }

    public function testCreateWithDefaults(): void
    {
        $formatter = ValueFormatter::createWithDefaults($this->translator);
        
        // Test that default config is applied
        $result = $formatter->formatValue(1234.56, 'field', ['format' => 'number']);
        $this->assertSame('1 234,56', $result);
    }

    public function testFormatStatusBadge(): void
    {
        $result = $this->formatter->formatValue('active', 'field', ['format' => 'status_badge']);
        $this->assertStringContainsString('badge bg-success', $result);
        $this->assertStringContainsString('Active', $result);

        $result = $this->formatter->formatValue('pending', 'field', ['format' => 'status_badge']);
        $this->assertStringContainsString('badge bg-warning', $result);

        $result = $this->formatter->formatValue('custom', 'field', [
            'format' => 'status_badge',
            'status_map' => ['custom' => 'info'],
            'labels' => ['custom' => 'Custom Status']
        ]);
        $this->assertStringContainsString('badge bg-info', $result);
        $this->assertStringContainsString('Custom Status', $result);
    }

    public function testFormatStockLevel(): void
    {
        $result = $this->formatter->formatValue(0, 'field', ['format' => 'stock_level']);
        $this->assertStringContainsString('badge bg-danger', $result);
        $this->assertStringContainsString('Rupture', $result);
        $this->assertStringContainsString('(0)', $result);

        $result = $this->formatter->formatValue(5, 'field', ['format' => 'stock_level']);
        $this->assertStringContainsString('badge bg-warning', $result);
        $this->assertStringContainsString('Stock faible', $result);

        $result = $this->formatter->formatValue(50, 'field', ['format' => 'stock_level']);
        $this->assertStringContainsString('badge bg-success', $result);
        $this->assertStringContainsString('En stock', $result);

        $result = $this->formatter->formatValue(100, 'field', [
            'format' => 'stock_level',
            'show_quantity' => false
        ]);
        $this->assertStringNotContainsString('(100)', $result);
    }

    public function testFormatHtml(): void
    {
        $html = '<p>Hello <strong>World</strong></p>';
        
        // Test with strip_tags
        $result = $this->formatter->formatValue($html, 'field', [
            'format' => 'html',
            'strip_tags' => true
        ]);
        $this->assertSame('Hello World', $result);

        // Test with allowed tags
        $result = $this->formatter->formatValue($html, 'field', [
            'format' => 'html',
            'strip_tags' => true,
            'allowed_tags' => '<strong>'
        ]);
        $this->assertSame('Hello <strong>World</strong>', $result);

        // Test with escape
        $result = $this->formatter->formatValue($html, 'field', [
            'format' => 'html',
            'escape' => true
        ]);
        $this->assertStringContainsString('&lt;p&gt;', $result);
        $this->assertStringContainsString('&lt;/p&gt;', $result);
    }

    public function testFormatJson(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        
        $result = $this->formatter->formatValue($data, 'field', ['format' => 'json']);
        $this->assertJson($result);
        $this->assertStringContainsString('"name": "John"', $result);
        $this->assertStringContainsString('"age": 30', $result);

        // Test with JSON string input
        $jsonString = '{"city":"Paris","country":"France"}';
        $result = $this->formatter->formatValue($jsonString, 'field', ['format' => 'json']);
        $this->assertJson($result);
        $this->assertStringContainsString('"city": "Paris"', $result);
    }

    public function testDetectFormat(): void
    {
        // This is a private method, so we test it indirectly through formatValue
        
        // Boolean detection
        $result = $this->formatter->formatValue(true, 'field');
        $this->assertSame('Oui', $result);

        // Date detection
        $result = $this->formatter->formatValue(new \DateTime('2023-12-25'), 'field');
        $this->assertSame('25/12/2023 00:00', $result);

        // Number detection
        $result = $this->formatter->formatValue(123.45, 'field');
        $this->assertSame('123,45', $result);

        // Email detection
        $result = $this->formatter->formatValue('test@example.com', 'field');
        $this->assertStringContainsString('mailto:', $result);

        // URL detection
        $result = $this->formatter->formatValue('https://example.com', 'field');
        $this->assertStringContainsString('href=', $result);
    }
}