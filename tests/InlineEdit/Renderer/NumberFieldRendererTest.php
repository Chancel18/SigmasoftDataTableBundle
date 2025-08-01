<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\InlineEdit\Renderer;

use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;
use Sigmasoft\DataTableBundle\InlineEdit\Renderer\NumberFieldRenderer;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class NumberFieldRendererTest extends TestCase
{
    private NumberFieldRenderer $renderer;
    private PropertyAccessor $propertyAccessor;

    protected function setUp(): void
    {
        $this->propertyAccessor = new PropertyAccessor();
        $this->renderer = new NumberFieldRenderer($this->propertyAccessor);
    }

    public function testSupportsNumberField(): void
    {
        $config = EditableFieldConfiguration::create('number');
        
        $this->assertTrue($this->renderer->supports($config));
    }

    public function testDoesNotSupportOtherFieldTypes(): void
    {
        $config = EditableFieldConfiguration::create('text');
        
        $this->assertFalse($this->renderer->supports($config));
    }

    public function testRenderBasicNumberField(): void
    {
        $config = EditableFieldConfiguration::create('number');
        $entity = new class {
            public int $id = 1;
            public float $price = 123.45;
        };

        $html = $this->renderer->render($config, 123.45, $entity, 'price');

        $this->assertStringContainsString('type="number"', $html);
        $this->assertStringContainsString('value="123.45"', $html);
        $this->assertStringContainsString('class="form-control editable-number-field"', $html);
        $this->assertStringContainsString('data-field-type="number"', $html);
    }

    public function testRenderWithMinMax(): void
    {
        $config = EditableFieldConfiguration::create('number')
            ->dataAttributes([
                'min' => 0,
                'max' => 1000
            ]);
        
        $entity = new class {
            public int $id = 1;
            public float $value = 500;
        };

        $html = $this->renderer->render($config, 500, $entity, 'value');

        $this->assertStringContainsString('min="0"', $html);
        $this->assertStringContainsString('max="1000"', $html);
    }

    public function testRenderWithStep(): void
    {
        $config = EditableFieldConfiguration::create('number')
            ->dataAttributes(['step' => 0.01]);
        
        $entity = new class {
            public int $id = 1;
            public float $value = 10.99;
        };

        $html = $this->renderer->render($config, 10.99, $entity, 'value');

        $this->assertStringContainsString('step="0.01"', $html);
    }

    public function testRenderWithPlaceholder(): void
    {
        $config = EditableFieldConfiguration::create('number')
            ->dataAttributes(['placeholder' => 'Entrez un nombre']);
        
        $entity = new class {
            public int $id = 1;
            public ?float $value = null;
        };

        $html = $this->renderer->render($config, null, $entity, 'value');

        $this->assertStringContainsString('placeholder="Entrez un nombre"', $html);
    }

    public function testRenderRequiredField(): void
    {
        $config = EditableFieldConfiguration::create('number')
            ->required(true);
        
        $entity = new class {
            public int $id = 1;
            public float $value = 0;
        };

        $html = $this->renderer->render($config, 0, $entity, 'value');

        $this->assertStringContainsString('required="required"', $html);
        $this->assertStringContainsString('class="form-control editable-number-field required"', $html);
    }

    public function testRenderWithCurrencyFormat(): void
    {
        $config = EditableFieldConfiguration::create('number')
            ->dataAttributes([
                'format' => 'currency',
                'currency' => 'EUR',
                'decimals' => 2
            ]);
        
        $entity = new class {
            public int $id = 1;
            public float $price = 99.99;
        };

        $html = $this->renderer->render($config, 99.99, $entity, 'price');

        $this->assertStringContainsString('data-format="currency"', $html);
        $this->assertStringContainsString('data-decimals="2"', $html);
        $this->assertStringContainsString('"currency":"EUR"', $html);
    }

    public function testProcessValidNumericValue(): void
    {
        $config = EditableFieldConfiguration::create('number');
        
        $result = $this->renderer->processValue($config, '123.45');
        
        $this->assertEquals(123.45, $result);
    }

    public function testProcessIntegerValue(): void
    {
        $config = EditableFieldConfiguration::create('number');
        
        $result = $this->renderer->processValue($config, 456);
        
        $this->assertEquals(456.0, $result);
    }

    public function testProcessNullValue(): void
    {
        $config = EditableFieldConfiguration::create('number');
        
        $result = $this->renderer->processValue($config, null);
        
        $this->assertNull($result);
    }

    public function testProcessEmptyStringValue(): void
    {
        $config = EditableFieldConfiguration::create('number');
        
        $result = $this->renderer->processValue($config, '');
        
        $this->assertNull($result);
    }

    public function testProcessValueWithCommaDecimalSeparator(): void
    {
        $config = EditableFieldConfiguration::create('number');
        
        $result = $this->renderer->processValue($config, '123,45');
        
        $this->assertEquals(123.45, $result);
    }

    public function testProcessValueWithThousandsSeparator(): void
    {
        $config = EditableFieldConfiguration::create('number');
        
        $result = $this->renderer->processValue($config, '1 234,56');
        
        $this->assertEquals(1234.56, $result);
    }

    public function testProcessInvalidValueThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Valeur numérique invalide');
        
        $config = EditableFieldConfiguration::create('number');
        
        $this->renderer->processValue($config, 'not-a-number');
    }

    public function testProcessValueBelowMinThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('inférieure au minimum autorisé');
        
        $config = EditableFieldConfiguration::create('number')
            ->dataAttributes(['min' => 10]);
        
        $this->renderer->processValue($config, 5);
    }

    public function testProcessValueAboveMaxThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('supérieure au maximum autorisé');
        
        $config = EditableFieldConfiguration::create('number')
            ->dataAttributes(['max' => 100]);
        
        $this->renderer->processValue($config, 150);
    }

    public function testGetValidationRules(): void
    {
        $config = EditableFieldConfiguration::create('number')
            ->required(true)
            ->dataAttributes([
                'min' => 0,
                'max' => 1000,
                'step' => 0.1
            ]);
        
        $rules = $this->renderer->getValidationRules($config);
        
        $this->assertTrue($rules['required']);
        $this->assertTrue($rules['numeric']);
        $this->assertEquals(0, $rules['min']);
        $this->assertEquals(1000, $rules['max']);
        $this->assertEquals(0.1, $rules['step']);
    }

    public function testGetValidationRulesWithoutConstraints(): void
    {
        $config = EditableFieldConfiguration::create('number');
        
        $rules = $this->renderer->getValidationRules($config);
        
        $this->assertFalse($rules['required']);
        $this->assertTrue($rules['numeric']);
        $this->assertArrayNotHasKey('min', $rules);
        $this->assertArrayNotHasKey('max', $rules);
    }

    public function testGetJavaScriptInit(): void
    {
        $js = $this->renderer->getJavaScriptInit();
        
        $this->assertStringContainsString('editable-number-field', $js);
        $this->assertStringContainsString('addEventListener', $js);
        $this->assertStringContainsString('blur', $js);
        $this->assertStringContainsString('input', $js);
        $this->assertStringContainsString('focus', $js);
    }

    public function testRenderContainsValidationFeedback(): void
    {
        $config = EditableFieldConfiguration::create('number');
        $entity = new class {
            public int $id = 1;
            public float $value = 0;
        };

        $html = $this->renderer->render($config, 0, $entity, 'value');

        $this->assertStringContainsString('invalid-feedback', $html);
        $this->assertStringContainsString('number-format-hint', $html);
    }

    public function testRenderContainsDataConfig(): void
    {
        $config = EditableFieldConfiguration::create('number')
            ->dataAttributes([
                'format' => 'decimal',
                'decimals' => 2,
                'thousands_separator' => ' ',
                'decimal_separator' => ','
            ]);
        
        $entity = new class {
            public int $id = 1;
            public float $value = 1234.56;
        };

        $html = $this->renderer->render($config, 1234.56, $entity, 'value');

        $this->assertStringContainsString('data-config=', $html);
        $this->assertStringContainsString('"format":"decimal"', $html);
        $this->assertStringContainsString('"decimals":2', $html);
        $this->assertStringContainsString('"thousandsSeparator":" "', $html);
        $this->assertStringContainsString('"decimalSeparator":","', $html);
    }
}