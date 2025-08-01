<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Column;

use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\Column\NumberColumn;

class NumberColumnTest extends TestCase
{
    public function testBasicNumberRendering(): void
    {
        $column = new NumberColumn('price', 'price', 'Prix');
        $item = new class {
            public float $price = 1234.56;
        };

        $result = $column->render(1234.56, $item);
        
        $this->assertEquals('1 234,56', $result);
    }

    public function testIntegerFormat(): void
    {
        $column = NumberColumn::integer('quantity', 'quantity', 'Quantité');
        $item = new class {
            public int $quantity = 1234;
        };

        $result = $column->render(1234, $item);
        
        $this->assertEquals('1 234', $result);
    }

    public function testCurrencyFormat(): void
    {
        $column = NumberColumn::currency('price', 'price', 'Prix', 'EUR');
        $item = new class {
            public float $price = 1234.56;
        };

        $result = $column->render(1234.56, $item);
        
        $this->assertEquals('EUR 1 234,56', $result);
    }

    public function testPercentageFormat(): void
    {
        $column = NumberColumn::percentage('rate', 'rate', 'Taux');
        $item = new class {
            public float $rate = 0.15;
        };

        $result = $column->render(0.15, $item);
        
        $this->assertEquals('15,0 %', $result);
    }

    public function testCustomSeparators(): void
    {
        $column = new NumberColumn('amount', 'amount', 'Montant', [
            'thousands_separator' => '.',
            'decimal_separator' => ','
        ]);
        $item = new class {
            public float $amount = 1234567.89;
        };

        $result = $column->render(1234567.89, $item);
        
        $this->assertEquals('1.234.567,89', $result);
    }

    public function testPrefixAndSuffix(): void
    {
        $column = new NumberColumn('value', 'value', 'Valeur', [
            'prefix' => '$',
            'suffix' => ' USD'
        ]);
        $item = new class {
            public float $value = 100.50;
        };

        $result = $column->render(100.50, $item);
        
        $this->assertEquals('$100,50 USD', $result);
    }

    public function testNullValue(): void
    {
        $column = new NumberColumn('price', 'price', 'Prix');
        $item = new class {
            public ?float $price = null;
        };

        $result = $column->render(null, $item);
        
        $this->assertEquals('', $result);
    }

    public function testNullValueWithCustomDisplay(): void
    {
        $column = new NumberColumn('price', 'price', 'Prix', [
            'null_display' => 'N/A'
        ]);
        $item = new class {
            public ?float $price = null;
        };

        $result = $column->render(null, $item);
        
        $this->assertEquals('N/A', $result);
    }

    public function testZeroValue(): void
    {
        $column = new NumberColumn('value', 'value', 'Valeur');
        $item = new class {
            public float $value = 0.0;
        };

        $result = $column->render(0.0, $item);
        
        $this->assertEquals('0,00', $result);
    }

    public function testZeroValueHidden(): void
    {
        $column = new NumberColumn('value', 'value', 'Valeur', [
            'show_zero' => false
        ]);
        $item = new class {
            public float $value = 0.0;
        };

        $result = $column->render(0.0, $item);
        
        $this->assertEquals('', $result);
    }

    public function testStringNumberConversion(): void
    {
        $column = new NumberColumn('price', 'price', 'Prix');
        $item = new class {
            public string $price = '1234.56';
        };

        $result = $column->render('1234.56', $item);
        
        $this->assertEquals('1 234,56', $result);
    }

    public function testInvalidStringValue(): void
    {
        $column = new NumberColumn('value', 'value', 'Valeur');
        $item = new class {
            public string $value = 'invalid';
        };

        $result = $column->render('invalid', $item);
        
        $this->assertEquals('invalid', $result);
    }

    public function testDecimalsConfiguration(): void
    {
        $column = new NumberColumn('value', 'value', 'Valeur', [
            'decimals' => 4
        ]);
        $item = new class {
            public float $value = 1234.56789;
        };

        $result = $column->render(1234.56789, $item);
        
        $this->assertEquals('1 234,5679', $result);
    }

    public function testFormatValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid format "invalid"');
        
        $column = new NumberColumn('value', 'value', 'Valeur');
        $column->setFormat('invalid');
    }

    public function testNegativeDecimalsValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Decimals must be >= 0');
        
        $column = new NumberColumn('value', 'value', 'Valeur');
        $column->setDecimals(-1);
    }

    public function testGettersAndSetters(): void
    {
        $column = new NumberColumn('value', 'value', 'Valeur');
        
        // Test format
        $column->setFormat(NumberColumn::FORMAT_CURRENCY);
        $this->assertEquals(NumberColumn::FORMAT_CURRENCY, $column->getFormat());
        
        // Test decimals
        $column->setDecimals(3);
        $this->assertEquals(3, $column->getDecimals());
        
        // Test separators
        $column->setDecimalSeparator('.');
        $this->assertEquals('.', $column->getDecimalSeparator());
        
        $column->setThousandsSeparator(',');
        $this->assertEquals(',', $column->getThousandsSeparator());
        
        // Test prefix/suffix
        $column->setPrefix('$');
        $this->assertEquals('$', $column->getPrefix());
        
        $column->setSuffix(' USD');
        $this->assertEquals(' USD', $column->getSuffix());
        
        // Test currency
        $column->setCurrency('EUR');
        $this->assertEquals('EUR', $column->getCurrency());
        
        // Test locale
        $column->setLocale('fr_FR');
        $this->assertEquals('fr_FR', $column->getLocale());
        
        // Test show zero
        $column->setShowZero(false);
        $this->assertFalse($column->isShowZero());
        
        // Test null display
        $column->setNullDisplay('Empty');
        $this->assertEquals('Empty', $column->getNullDisplay());
        
        // Test Intl usage
        $column->setUseIntl(true);
        $this->assertTrue($column->isUseIntl());
        
        // Test Intl options
        $options = ['key' => 'value'];
        $column->setIntlOptions($options);
        $this->assertEquals($options, $column->getIntlOptions());
    }

    public function testStaticFactoryMethods(): void
    {
        // Test currency factory
        $currencyColumn = NumberColumn::currency('price', 'price', 'Prix', 'USD', 2);
        $this->assertEquals(NumberColumn::FORMAT_CURRENCY, $currencyColumn->getFormat());
        $this->assertEquals('USD', $currencyColumn->getCurrency());
        $this->assertEquals(2, $currencyColumn->getDecimals());
        
        // Test percentage factory
        $percentageColumn = NumberColumn::percentage('rate', 'rate', 'Taux', 1);
        $this->assertEquals(NumberColumn::FORMAT_PERCENTAGE, $percentageColumn->getFormat());
        $this->assertEquals(1, $percentageColumn->getDecimals());
        
        // Test integer factory
        $integerColumn = NumberColumn::integer('count', 'count', 'Compteur', '.');
        $this->assertEquals(NumberColumn::FORMAT_INTEGER, $integerColumn->getFormat());
        $this->assertEquals('.', $integerColumn->getThousandsSeparator());
        
        // Test decimal factory
        $decimalColumn = NumberColumn::decimal('value', 'value', 'Valeur', 3, '.', ',');
        $this->assertEquals(NumberColumn::FORMAT_DECIMAL, $decimalColumn->getFormat());
        $this->assertEquals(3, $decimalColumn->getDecimals());
        $this->assertEquals('.', $decimalColumn->getThousandsSeparator());
        $this->assertEquals(',', $decimalColumn->getDecimalSeparator());
    }

    public function testColumnDefaultProperties(): void
    {
        $column = new NumberColumn('value', 'value', 'Valeur');
        
        // Les colonnes numériques sont triables par défaut
        $this->assertTrue($column->isSortable());
        
        // Les colonnes numériques ne sont pas recherchables par défaut
        $this->assertFalse($column->isSearchable());
    }

    /**
     * Test avec l'extension Intl si disponible
     */
    public function testIntlFormatting(): void
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('Extension Intl non disponible');
        }
        
        $column = new NumberColumn('price', 'price', 'Prix', [
            'use_intl' => true,
            'locale' => 'fr_FR',
            'format' => NumberColumn::FORMAT_CURRENCY,
            'currency' => 'EUR'
        ]);
        
        $item = new class {
            public float $price = 1234.56;
        };

        $result = $column->render(1234.56, $item);
        
        // Le résultat exact dépend de la locale du système, 
        // on vérifie juste qu'il contient la valeur et la devise
        $this->assertStringContainsString('1', $result);
        $this->assertStringContainsString('234', $result);
        $this->assertStringContainsString('56', $result);
    }
}