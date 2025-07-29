<?php

/**
 * Tests for ActionColumn
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package App\Tests\SigmasoftDataTableBundle\Column
 */

declare(strict_types=1);

namespace App\Tests\SigmasoftDataTableBundle\Column;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Column\ActionColumn;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActionColumnTest extends TestCase
{
    private UrlGeneratorInterface|MockObject $urlGenerator;
    private User $entity;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->entity = new User();
        
        // Use reflection to set the ID since it's auto-generated
        $reflection = new \ReflectionClass($this->entity);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->entity, 123);
    }

    public function testBasicActionColumn(): void
    {
        $actions = [
            'show' => [
                'route' => 'user_show',
                'icon' => 'bi bi-eye',
                'class' => 'btn btn-sm btn-info',
                'title' => 'View'
            ]
        ];

        $column = new ActionColumn($this->urlGenerator, 'actions', 'Actions', $actions);

        $this->assertEquals('actions', $column->getName());
        $this->assertEquals('Actions', $column->getLabel());
        $this->assertFalse($column->isSortable());
        $this->assertFalse($column->isSearchable());
    }

    public function testRenderLinkAction(): void
    {
        $actions = [
            'show' => [
                'route' => 'user_show',
                'icon' => 'bi bi-eye',
                'class' => 'btn btn-sm btn-info',
                'title' => 'View'
            ]
        ];

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('user_show', ['id' => 123])
            ->willReturn('/users/123');

        $column = new ActionColumn($this->urlGenerator, 'actions', 'Actions', $actions);
        $result = $column->render(null, $this->entity);

        $this->assertStringContainsString('<div class="d-inline-flex gap-2">', $result);
        $this->assertStringContainsString('href="/users/123"', $result);
        $this->assertStringContainsString('class="btn btn-sm btn-info"', $result);
        $this->assertStringContainsString('title="View"', $result);
        $this->assertStringContainsString('<i class="bi bi-eye"></i>', $result);
    }

    public function testRenderDeleteAction(): void
    {
        $actions = [
            'delete' => [
                'type' => 'delete',
                'icon' => 'bi bi-trash',
                'class' => 'btn btn-sm btn-danger',
                'title' => 'Delete',
                'confirm' => 'Are you sure?'
            ]
        ];

        $column = new ActionColumn($this->urlGenerator, 'actions', 'Actions', $actions);
        $result = $column->render(null, $this->entity);

        $this->assertStringContainsString('<button', $result);
        $this->assertStringContainsString('class="btn btn-sm btn-danger"', $result);
        $this->assertStringContainsString('title="Delete"', $result);
        $this->assertStringContainsString('data-action="live#action"', $result);
        $this->assertStringContainsString('data-live-action-param="deleteItem"', $result);
        $this->assertStringContainsString('data-live-id-param="123"', $result);
        $this->assertStringContainsString('<i class="bi bi-trash"></i>', $result);
        
        // Test the secure confirmation JavaScript
        $this->assertStringContainsString('if(!confirm(', $result);
        $this->assertStringContainsString('event.preventDefault();', $result);
        $this->assertStringContainsString('event.stopPropagation();', $result);
        $this->assertStringContainsString('Are you sure?', $result);
    }

    public function testRenderDeleteActionWithDefaultConfirm(): void
    {
        $actions = [
            'delete' => [
                'type' => 'delete',
                'icon' => 'bi bi-trash'
            ]
        ];

        $column = new ActionColumn($this->urlGenerator, 'actions', 'Actions', $actions);
        $result = $column->render(null, $this->entity);

        $this->assertStringContainsString('Êtes-vous sûr de vouloir supprimer cet élément ?', $result);
        $this->assertStringContainsString('class="btn btn-sm btn-danger"', $result); // default class
        $this->assertStringContainsString('title="Supprimer"', $result); // default title
    }

    public function testRenderActionWithConfirmation(): void
    {
        $actions = [
            'archive' => [
                'route' => 'user_archive',
                'icon' => 'bi bi-archive',
                'class' => 'btn btn-sm btn-warning',
                'title' => 'Archive',
                'confirm' => 'Are you sure you want to archive this user?'
            ]
        ];

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('user_archive', ['id' => 123])
            ->willReturn('/users/123/archive');

        $column = new ActionColumn($this->urlGenerator, 'actions', 'Actions', $actions);
        $result = $column->render(null, $this->entity);

        $this->assertStringContainsString('onclick="return confirm(', $result);
        $this->assertStringContainsString('Are you sure you want to archive this user?', $result);
    }

    public function testRenderMultipleActions(): void
    {
        $actions = [
            'show' => [
                'route' => 'user_show',
                'icon' => 'bi bi-eye'
            ],
            'edit' => [
                'route' => 'user_edit',
                'icon' => 'bi bi-pencil'
            ],
            'delete' => [
                'type' => 'delete',
                'icon' => 'bi bi-trash'
            ]
        ];

        $this->urlGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturnMap([
                ['user_show', ['id' => 123], '/users/123'],
                ['user_edit', ['id' => 123], '/users/123/edit']
            ]);

        $column = new ActionColumn($this->urlGenerator, 'actions', 'Actions', $actions);
        $result = $column->render(null, $this->entity);

        // Should contain all three actions
        $this->assertStringContainsString('bi bi-eye', $result);
        $this->assertStringContainsString('bi bi-pencil', $result);
        $this->assertStringContainsString('bi bi-trash', $result);
        
        // Should be wrapped in a flex container
        $this->assertStringContainsString('<div class="d-inline-flex gap-2">', $result);
        
        // Count the number of action elements
        $this->assertEquals(2, substr_count($result, '<a href=')); // 2 link actions
        $this->assertEquals(1, substr_count($result, '<button')); // 1 delete action
    }

    public function testRenderWithCustomRouteParams(): void
    {
        $actions = [
            'custom' => [
                'route' => 'custom_route',
                'route_params' => ['id' => 123, 'action' => 'special'],
                'icon' => 'bi bi-star'
            ]
        ];

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('custom_route', ['id' => 123, 'action' => 'special'])
            ->willReturn('/custom/123/special');

        $column = new ActionColumn($this->urlGenerator, 'actions', 'Actions', $actions);
        $result = $column->render(null, $this->entity);

        $this->assertStringContainsString('/custom/123/special', $result);
    }

    public function testRenderActionWithoutRoute(): void
    {
        $actions = [
            'invalid' => [
                'icon' => 'bi bi-question'
                // No route specified
            ]
        ];

        $column = new ActionColumn($this->urlGenerator, 'actions', 'Actions', $actions);
        $result = $column->render(null, $this->entity);

        // Should render empty container since action has no route and is not delete type
        $this->assertEquals('<div class="d-inline-flex gap-2"></div>', $result);
    }

    public function testActionColumnDefaults(): void
    {
        $column = new ActionColumn($this->urlGenerator);

        $this->assertEquals('actions', $column->getName());
        $this->assertEquals('Actions', $column->getLabel());
        $this->assertEquals([], $column->getOption('actions', []));
    }
}
