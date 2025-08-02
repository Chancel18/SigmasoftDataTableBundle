<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Column;

use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\Column\EditableColumnV2;
use Sigmasoft\DataTableBundle\InlineEdit\Configuration\EditableFieldConfiguration;
use Sigmasoft\DataTableBundle\Service\ColumnFactory;
use Sigmasoft\DataTableBundle\InlineEdit\Renderer\FieldRendererRegistry;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Test de sérialisation pour EditableColumnV2
 * Vérifie que les types de champs sont conservés après sérialisation/désérialisation
 */
class EditableColumnV2SerializationTest extends TestCase
{
    private ColumnFactory $columnFactory;
    private FieldRendererRegistry $rendererRegistry;

    protected function setUp(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->rendererRegistry = new FieldRendererRegistry();
        $this->columnFactory = new ColumnFactory($urlGenerator, $this->rendererRegistry);
    }

    public function testSelectFieldTypeSerialization(): void
    {
        // Créer une colonne select
        $selectOptions = [
            'Électronique' => 'Électronique',
            'Informatique' => 'Informatique',
            'Audio' => 'Audio'
        ];
        
        $column = EditableColumnV2::select('category', 'category', 'Catégorie', $selectOptions)
            ->required(true);

        // Vérifier que les options contiennent le field_type
        $options = $column->getOptions();
        $this->assertArrayHasKey('field_type', $options);
        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_SELECT, $options['field_type']);
        $this->assertArrayHasKey('field_options', $options);
        $this->assertEquals($selectOptions, $options['field_options']);

        // Simuler la sérialisation via la définition de colonne
        $definition = [
            'type' => EditableColumnV2::class,
            'name' => $column->getName(),
            'property_path' => $column->getPropertyPath(),
            'label' => $column->getLabel(),
            'sortable' => $column->isSortable(),
            'searchable' => $column->isSearchable(),
            'options' => $column->getOptions()
        ];

        // Reconstruire la colonne à partir de la définition
        $reconstructedColumn = $this->columnFactory->createColumnFromDefinition($definition);

        // Vérifier que c'est bien une EditableColumnV2
        $this->assertInstanceOf(EditableColumnV2::class, $reconstructedColumn);

        // Vérifier que la configuration est correctement reconstruite
        $fieldConfig = $reconstructedColumn->getFieldConfiguration();
        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_SELECT, $fieldConfig->getFieldType());
        $this->assertEquals($selectOptions, $fieldConfig->getOptions());
        $this->assertTrue($fieldConfig->isRequired());
    }

    public function testTextFieldTypeSerialization(): void
    {
        // Créer une colonne text
        $column = EditableColumnV2::text('name', 'name', 'Nom')
            ->required(true)
            ->maxLength(100)
            ->placeholder('Saisissez le nom');

        // Vérifier la sérialisation
        $options = $column->getOptions();
        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_TEXT, $options['field_type']);

        // Reconstruction
        $definition = [
            'type' => EditableColumnV2::class,
            'name' => $column->getName(),
            'property_path' => $column->getPropertyPath(),
            'label' => $column->getLabel(),
            'sortable' => $column->isSortable(),
            'searchable' => $column->isSearchable(),
            'options' => $column->getOptions()
        ];

        $reconstructedColumn = $this->columnFactory->createColumnFromDefinition($definition);
        $fieldConfig = $reconstructedColumn->getFieldConfiguration();

        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_TEXT, $fieldConfig->getFieldType());
        $this->assertTrue($fieldConfig->isRequired());
        $this->assertEquals(100, $fieldConfig->getMaxLength());
        $this->assertEquals('Saisissez le nom', $fieldConfig->getPlaceholder());
    }

    public function testEmailFieldTypeSerialization(): void
    {
        // Créer une colonne email
        $column = EditableColumnV2::email('email', 'email', 'Email')
            ->required(true);

        // Vérifier la sérialisation
        $options = $column->getOptions();
        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_EMAIL, $options['field_type']);

        // Reconstruction
        $definition = [
            'type' => EditableColumnV2::class,
            'name' => $column->getName(),
            'property_path' => $column->getPropertyPath(),
            'label' => $column->getLabel(),
            'sortable' => $column->isSortable(),
            'searchable' => $column->isSearchable(),
            'options' => $column->getOptions()
        ];

        $reconstructedColumn = $this->columnFactory->createColumnFromDefinition($definition);
        $fieldConfig = $reconstructedColumn->getFieldConfiguration();

        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_EMAIL, $fieldConfig->getFieldType());
        $this->assertTrue($fieldConfig->isRequired());
    }

    public function testNumberFieldTypeSerialization(): void
    {
        // Créer une colonne number
        $column = EditableColumnV2::number('price', 'price', 'Prix')
            ->required(true)
            ->min('0')
            ->max('9999.99');

        // Vérifier la sérialisation
        $options = $column->getOptions();
        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_NUMBER, $options['field_type']);

        // Reconstruction
        $definition = [
            'type' => EditableColumnV2::class,
            'name' => $column->getName(),
            'property_path' => $column->getPropertyPath(),
            'label' => $column->getLabel(),
            'sortable' => $column->isSortable(),
            'searchable' => $column->isSearchable(),
            'options' => $column->getOptions()
        ];

        $reconstructedColumn = $this->columnFactory->createColumnFromDefinition($definition);
        $fieldConfig = $reconstructedColumn->getFieldConfiguration();

        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_NUMBER, $fieldConfig->getFieldType());
        $this->assertTrue($fieldConfig->isRequired());
        $this->assertEquals('0', $fieldConfig->getMin());
        $this->assertEquals('9999.99', $fieldConfig->getMax());
    }

    public function testTextareaFieldTypeSerialization(): void
    {
        // Créer une colonne textarea
        $column = EditableColumnV2::textarea('description', 'description', 'Description', 5)
            ->maxLength(500);

        // Vérifier la sérialisation
        $options = $column->getOptions();
        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_TEXTAREA, $options['field_type']);
        $this->assertArrayHasKey('data_attributes', $options);
        $this->assertEquals(['rows' => 5], $options['data_attributes']);

        // Reconstruction
        $definition = [
            'type' => EditableColumnV2::class,
            'name' => $column->getName(),
            'property_path' => $column->getPropertyPath(),
            'label' => $column->getLabel(),
            'sortable' => $column->isSortable(),
            'searchable' => $column->isSearchable(),
            'options' => $column->getOptions()
        ];

        $reconstructedColumn = $this->columnFactory->createColumnFromDefinition($definition);
        $fieldConfig = $reconstructedColumn->getFieldConfiguration();

        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_TEXTAREA, $fieldConfig->getFieldType());
        $this->assertEquals(500, $fieldConfig->getMaxLength());
        $this->assertEquals(['rows' => 5], $fieldConfig->getDataAttributes());
    }

    public function testValidationRulesSerialization(): void
    {
        // Créer une colonne avec des règles de validation
        $validationRules = [
            'NotBlank' => [],
            'Length' => ['max' => 100]
        ];

        $column = EditableColumnV2::text('name', 'name', 'Nom')
            ->validationRules($validationRules);

        // Vérifier la sérialisation
        $options = $column->getOptions();
        $this->assertArrayHasKey('validation_rules', $options);
        $this->assertEquals($validationRules, $options['validation_rules']);

        // Reconstruction
        $definition = [
            'type' => EditableColumnV2::class,
            'name' => $column->getName(),
            'property_path' => $column->getPropertyPath(),
            'label' => $column->getLabel(),
            'sortable' => $column->isSortable(),
            'searchable' => $column->isSearchable(),
            'options' => $column->getOptions()
        ];

        $reconstructedColumn = $this->columnFactory->createColumnFromDefinition($definition);
        $fieldConfig = $reconstructedColumn->getFieldConfiguration();

        $this->assertEquals($validationRules, $fieldConfig->getValidationRules());
    }

    public function testFluentApiUpdatesSerialization(): void
    {
        // Créer une colonne et modifier sa configuration via l'API fluide
        $column = EditableColumnV2::text('name', 'name', 'Nom');

        // Ajouter des configurations via l'API fluide
        $column->required(true)
               ->placeholder('Saisissez votre nom')
               ->maxLength(50);

        // Vérifier que les options sont mises à jour
        $options = $column->getOptions();
        $this->assertEquals(EditableFieldConfiguration::FIELD_TYPE_TEXT, $options['field_type']);

        // La configuration interne devrait être reflétée dans les options
        $fieldConfig = $column->getFieldConfiguration();
        $this->assertTrue($fieldConfig->isRequired());
        $this->assertEquals('Saisissez votre nom', $fieldConfig->getPlaceholder());
        $this->assertEquals(50, $fieldConfig->getMaxLength());

        // Reconstruction pour s'assurer que les changements sont persistés
        $definition = [
            'type' => EditableColumnV2::class,
            'name' => $column->getName(),
            'property_path' => $column->getPropertyPath(),
            'label' => $column->getLabel(),
            'sortable' => $column->isSortable(),
            'searchable' => $column->isSearchable(),
            'options' => $column->getOptions()
        ];

        $reconstructedColumn = $this->columnFactory->createColumnFromDefinition($definition);
        $reconstructedFieldConfig = $reconstructedColumn->getFieldConfiguration();

        $this->assertTrue($reconstructedFieldConfig->isRequired());
        $this->assertEquals('Saisissez votre nom', $reconstructedFieldConfig->getPlaceholder());
        $this->assertEquals(50, $reconstructedFieldConfig->getMaxLength());
    }
}