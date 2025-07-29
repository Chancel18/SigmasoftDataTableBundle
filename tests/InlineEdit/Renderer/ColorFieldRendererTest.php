<?php

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\InlineEdit\Renderer;

use App\Entity\User;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;
use Sigmasoft\DataTableBundle\InlineEdit\Renderer\ColorFieldRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Tests pour le ColorFieldRenderer
 */
class ColorFieldRendererTest extends TestCase
{
    private ColorFieldRenderer $renderer;
    private PropertyAccessorInterface $propertyAccessor;

    protected function setUp(): void
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->renderer = new ColorFieldRenderer($this->propertyAccessor);
    }

    public function testSupports(): void
    {
        // Test support pour le type COLOR
        $colorConfig = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR);
        $this->assertTrue($this->renderer->supports($colorConfig));

        // Test non-support pour d'autres types
        $textConfig = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_TEXT);
        $this->assertFalse($this->renderer->supports($textConfig));
    }

    public function testGetPriority(): void
    {
        $this->assertEquals(100, $this->renderer->getPriority());
    }

    public function testRender(): void
    {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR);
        $user = new User();
        $user->setPreferredColor('#FF0000');

        $html = $this->renderer->render($config, '#FF0000', $user, 'preferredColor');

        // Vérifications du HTML généré
        $this->assertStringContainsString('color-field-wrapper', $html);
        $this->assertStringContainsString('type="color"', $html);
        $this->assertStringContainsString('value="#FF0000"', $html);
        $this->assertStringContainsString('color-preview', $html);
        $this->assertStringContainsString('background-color: #FF0000', $html);
        $this->assertStringContainsString('color-presets', $html);
        $this->assertStringContainsString('fas fa-palette', $html);
    }

    public function testRenderWithoutPresets(): void
    {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR)
            ->dataAttributes(['show_presets' => false]);
        $user = new User();

        $html = $this->renderer->render($config, '#00FF00', $user, 'preferredColor');

        $this->assertStringContainsString('color-field-wrapper', $html);
        $this->assertStringContainsString('value="#00FF00"', $html);
        // Ne doit pas contenir les presets
        $this->assertStringNotContainsString('color-presets', $html);
    }

    public function testRenderWithEmptyValue(): void
    {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR);
        $user = new User();

        $html = $this->renderer->render($config, null, $user, 'preferredColor');

        // Doit utiliser la couleur par défaut
        $this->assertStringContainsString('value="#000000"', $html);
        $this->assertStringContainsString('background-color: #000000', $html);
    }

    public function testValidateValue(): void
    {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR);

        // Test valeur valide
        $errors = $this->renderer->validateValue('#FF0000', $config);
        $this->assertEmpty($errors);

        // Test valeur invalide
        $errors = $this->renderer->validateValue('invalid-color', $config);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Format de couleur invalide', $errors[0]);

        // Test champ requis vide
        $requiredConfig = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR)
            ->required(true);
        $errors = $this->renderer->validateValue('', $requiredConfig);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Une couleur est requise', $errors[0]);
    }

    public function testColorNormalization(): void
    {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR);
        $user = new User();

        // Test couleur sans #
        $html = $this->renderer->render($config, 'FF0000', $user, 'preferredColor');
        $this->assertStringContainsString('value="#FF0000"', $html);

        // Test couleur nommée
        $html = $this->renderer->render($config, 'red', $user, 'preferredColor');
        $this->assertStringContainsString('value="#FF0000"', $html);

        // Test couleur en minuscules
        $html = $this->renderer->render($config, '#ff0000', $user, 'preferredColor');
        $this->assertStringContainsString('value="#FF0000"', $html);
    }

    public function testPresetColors(): void
    {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR);
        $user = new User();

        $html = $this->renderer->render($config, '#FF0000', $user, 'preferredColor');

        // Vérifier que les couleurs prédéfinies sont présentes
        $this->assertStringContainsString('data-color="#FF0000"', $html);
        $this->assertStringContainsString('data-color="#00FF00"', $html);
        $this->assertStringContainsString('data-color="#0000FF"', $html);
        $this->assertStringContainsString('title="Rouge"', $html);
        $this->assertStringContainsString('title="Vert"', $html);
        $this->assertStringContainsString('title="Bleu"', $html);
    }

    public function testDataAttributes(): void
    {
        $config = EditableFieldConfiguration::create(EditableFieldConfiguration::FIELD_TYPE_COLOR);
        $user = new User();
        $user->setPreferredColor('#FF0000');

        // Mock de l'ID utilisateur
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 123);

        $html = $this->renderer->render($config, '#FF0000', $user, 'preferredColor');

        // Vérifier les data-attributes
        $this->assertStringContainsString('data-entity-id="123"', $html);
        $this->assertStringContainsString('data-field-name="preferredColor"', $html);
        $this->assertStringContainsString('data-original-value="#FF0000"', $html);
    }
}
