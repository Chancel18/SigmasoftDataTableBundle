<?php

/**
 * Integration test for inline editing functionality
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package App\Tests\SigmasoftDataTableBundle\Integration
 */

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Integration;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Column\EditableColumn;
use Sigmasoft\DataTableBundle\Service\ExportService;
use Sigmasoft\DataTableBundle\Service\InlineEditService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class InlineEditingIntegrationTest extends TestCase
{
    public function testEditableColumnRendersCorrectly(): void
    {
        $column = new EditableColumn('name', 'name', 'Nom', true, true, [
            'field_type' => 'text',
            'required' => true,
            'max_length' => 100,
            'placeholder' => 'Entrez le nom...'
        ]);

        $user = new User();
        $user->setName('Test User');
        
        // Set ID using reflection since it's auto-generated
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 1);

        $rendered = $column->render('Test User', $user);

        $this->assertStringContainsString('editable-field', $rendered);
        $this->assertStringContainsString('data-entity-id="1"', $rendered);
        $this->assertStringContainsString('data-field-name="name"', $rendered);
        $this->assertStringContainsString('value="Test User"', $rendered);
        $this->assertStringContainsString('maxlength="100"', $rendered);
        $this->assertStringContainsString('placeholder="Entrez le nom..."', $rendered);
    }

    public function testEditableColumnWithSelectOptions(): void
    {
        $column = new EditableColumn('status', 'status', 'Statut', true, false, [
            'field_type' => 'select',
            'options' => [
                'active' => 'Actif',
                'inactive' => 'Inactif',
                'pending' => 'En attente'
            ]
        ]);

        $user = new User();
        $user->setStatus('active');
        
        // Set ID using reflection since it's auto-generated
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 1);

        $rendered = $column->render('active', $user);

        $this->assertStringContainsString('<select', $rendered);
        $this->assertStringContainsString('option value="active" selected', $rendered);
        $this->assertStringContainsString('Actif', $rendered);
        $this->assertStringContainsString('Inactif', $rendered);
        $this->assertStringContainsString('En attente', $rendered);
    }

    public function testEditableColumnValidation(): void
    {
        $column = new EditableColumn('email', 'email', 'Email', true, true, [
            'field_type' => 'email',
            'required' => true,
            'validation' => [
                'pattern' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}'
            ]
        ]);

        $user = new User();
        $user->setEmail('test@example.com');
        
        // Set ID using reflection since it's auto-generated
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 1);

        $rendered = $column->render('test@example.com', $user);

        $this->assertStringContainsString('type="email"', $rendered);
        $this->assertStringContainsString('required="required"', $rendered);
        $this->assertStringContainsString('pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"', $rendered);
    }

    public function testEditableColumnIndicators(): void
    {
        $column = new EditableColumn('name', 'name', 'Nom');

        $user = new User();
        $user->setName('Test User');
        
        // Set ID using reflection since it's auto-generated
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 1);

        $rendered = $column->render('Test User', $user);

        // Vérifier la présence des indicateurs de sauvegarde et d'erreur
        $this->assertStringContainsString('saving-indicator', $rendered);
        $this->assertStringContainsString('error-indicator', $rendered);
        $this->assertStringContainsString('spinner-border', $rendered);
        $this->assertStringContainsString('fa-exclamation-triangle', $rendered);
    }

    public function testInlineEditingWorkflowComponents(): void
    {
        // Test que toutes les classes nécessaires existent
        $this->assertTrue(class_exists(EditableColumn::class));
        $this->assertTrue(class_exists(ExportService::class));
        $this->assertTrue(class_exists(InlineEditService::class));

        // Test que les colonnes éditables sont bien des colonnes
        $column = new EditableColumn('test', 'test', 'Test');
        $this->assertInstanceOf(\Sigmasoft\DataTableBundle\Column\ColumnInterface::class, $column);
    }
}
