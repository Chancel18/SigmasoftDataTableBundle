<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Template;

use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\Component\DataTableComponent;
use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Column\ActionColumn;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Knp\Component\Pager\Pagination\SlidingPagination;

/**
 * Test du template DataTable et de ses composants
 */
class DataTableTemplateTest extends TestCase
{
    private Environment $twig;
    private DataTableConfiguration $config;
    
    protected function setUp(): void
    {
        // Configuration de Twig
        $loader = new FilesystemLoader([
            __DIR__ . '/../../templates'
        ]);
        
        $this->twig = new Environment($loader, [
            'debug' => true,
            'cache' => false,
            'strict_variables' => true,
        ]);
        
        // Ajout de l'extension debug
        $this->twig->addExtension(new DebugExtension());
        
        // Configuration du traducteur
        $translator = new Translator('fr');
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', __DIR__ . '/../../translations/SigmasoftDataTable.fr.yaml', 'fr', 'SigmasoftDataTable');
        $translator->addResource('yaml', __DIR__ . '/../../translations/SigmasoftDataTable.en.yaml', 'en', 'SigmasoftDataTable');
        
        $this->twig->addExtension(new TranslationExtension($translator));
        
        // Configuration de test
        $this->config = $this->createMock(DataTableConfiguration::class);
        $this->config->method('getEntityClass')->willReturn('App\\Entity\\TestEntity');
        $this->config->method('getTheme')->willReturn('bootstrap5');
        $this->config->method('isSearchEnabled')->willReturn(true);
        $this->config->method('isPaginationEnabled')->willReturn(true);
        $this->config->method('isSortingEnabled')->willReturn(true);
        $this->config->method('getTableClass')->willReturn('table table-striped table-hover');
        $this->config->method('getPaginationSizes')->willReturn([5, 10, 25, 50, 100]);
    }
    
    public function testBaseTemplateRendering(): void
    {
        // Configuration des colonnes
        $columns = [
            new TextColumn('id', 'id', 'ID'),
            new TextColumn('name', 'name', 'Nom'),
            new ActionColumn(['edit', 'delete'])
        ];
        
        $this->config->method('getColumns')->willReturn($columns);
        
        // Mock du composant
        $component = $this->createMockComponent();
        
        // Mock des données
        $data = $this->createMockPagination();
        
        // Contexte du template
        $context = [
            'this' => $component,
            'config' => $this->config,
            'data' => $data,
            'attributes' => new \ArrayObject(['class' => 'my-custom-class'])
        ];
        
        // Rendu du template principal
        $html = $this->twig->render('SigmasoftDataTable/datatable.html.twig', $context);
        
        // Assertions
        $this->assertStringContainsString('sigmasoft-datatable', $html);
        $this->assertStringContainsString('sigmasoft-datatable--bootstrap5', $html);
        $this->assertStringContainsString('my-custom-class', $html);
        $this->assertStringContainsString('card', $html);
        $this->assertStringContainsString('table table-striped table-hover', $html);
    }
    
    public function testSearchComponentRendering(): void
    {
        $component = $this->createMockComponent();
        $component->searchInput = 'test search';
        
        $context = [
            'config' => $this->config,
            'this' => $component
        ];
        
        $html = $this->twig->render('SigmasoftDataTable/components/_search.html.twig', $context);
        
        $this->assertStringContainsString('datatable-search', $html);
        $this->assertStringContainsString('bi-search', $html);
        $this->assertStringContainsString('test search', $html);
        $this->assertStringContainsString('data-model="searchInput"', $html);
        $this->assertStringContainsString('bi-x-lg', $html); // Bouton clear
    }
    
    public function testPaginationComponentRendering(): void
    {
        $data = $this->createMockPagination(100, 2, 10); // 100 items, page 2, 10 par page
        
        $context = [
            'data' => $data,
            'this' => $this->createMockComponent()
        ];
        
        $html = $this->twig->render('SigmasoftDataTable/components/_pagination.html.twig', $context);
        
        // Vérifications
        $this->assertStringContainsString('datatable-pagination', $html);
        $this->assertStringContainsString('Affichage de 11 à 20 sur 100 résultats', $html);
        $this->assertStringContainsString('bi-chevron-left', $html);
        $this->assertStringContainsString('bi-chevron-right', $html);
        $this->assertStringContainsString('page-item active', $html);
        $this->assertStringContainsString('data-live-page-param="1"', $html);
        $this->assertStringContainsString('data-live-page-param="3"', $html);
    }
    
    public function testHeaderCellRendering(): void
    {
        $column = new TextColumn('name', 'name', 'Nom');
        $column->setSortable(true);
        
        $context = [
            'column' => $column,
            'config' => $this->config,
            'sortField' => 'name',
            'sortDirection' => 'asc'
        ];
        
        $html = $this->twig->render('SigmasoftDataTable/components/_header_cell.html.twig', $context);
        
        $this->assertStringContainsString('datatable-header-cell', $html);
        $this->assertStringContainsString('sortable', $html);
        $this->assertStringContainsString('Nom', $html);
        $this->assertStringContainsString('bi-sort-up', $html);
        $this->assertStringContainsString('data-live-field-param="name"', $html);
    }
    
    public function testEmptyDataRendering(): void
    {
        $this->config->method('getColumns')->willReturn([
            new TextColumn('id', 'id', 'ID'),
            new TextColumn('name', 'name', 'Nom')
        ]);
        
        $component = $this->createMockComponent();
        $component->config = new \stdClass();
        $component->config->searchQuery = 'test';
        
        $data = $this->createMockPagination(0); // Aucune donnée
        
        $context = [
            'this' => $component,
            'config' => $this->config,
            'data' => $data,
            'attributes' => new \ArrayObject()
        ];
        
        $html = $this->twig->render('SigmasoftDataTable/datatable.html.twig', $context);
        
        $this->assertStringContainsString('bi-inbox', $html);
        $this->assertStringContainsString('Aucune donnée disponible', $html);
        $this->assertStringContainsString('Aucun résultat pour "test"', $html);
    }
    
    public function testThemeSupport(): void
    {
        // Test avec thème par défaut (bootstrap5)
        $context = [
            'this' => $this->createMockComponent(),
            'config' => $this->config,
            'data' => $this->createMockPagination(),
            'attributes' => new \ArrayObject()
        ];
        
        $html = $this->twig->render('SigmasoftDataTable/datatable.html.twig', $context);
        
        $this->assertStringContainsString('sigmasoft-datatable--bootstrap5', $html);
        $this->assertStringContainsString('card', $html);
        $this->assertStringContainsString('--bs-gray-600', $html);
        $this->assertStringContainsString('--bs-primary', $html);
    }
    
    public function testBlockOverride(): void
    {
        // Créer un template qui étend et surcharge un block
        $customTemplate = <<<TWIG
{% extends '@SigmasoftDataTable/datatable.html.twig' %}

{% block datatable_empty_content %}
    <div class="custom-empty">
        <p>Message personnalisé : aucune donnée</p>
    </div>
{% endblock %}
TWIG;
        
        // Ajouter le template personnalisé
        $loader = $this->twig->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $loader->prependPath(__DIR__ . '/../fixtures/templates', 'Test');
        }
        
        // Créer le fichier temporaire
        $tempFile = __DIR__ . '/../fixtures/templates/custom_datatable.html.twig';
        @mkdir(dirname($tempFile), 0777, true);
        file_put_contents($tempFile, $customTemplate);
        
        try {
            $this->config->method('getColumns')->willReturn([new TextColumn('id', 'id', 'ID')]);
            
            $context = [
                'this' => $this->createMockComponent(),
                'config' => $this->config,
                'data' => $this->createMockPagination(0),
                'attributes' => new \ArrayObject()
            ];
            
            $html = $this->twig->render('@Test/custom_datatable.html.twig', $context);
            
            $this->assertStringContainsString('custom-empty', $html);
            $this->assertStringContainsString('Message personnalisé : aucune donnée', $html);
        } finally {
            @unlink($tempFile);
            @rmdir(dirname($tempFile));
        }
    }
    
    private function createMockComponent(): object
    {
        $component = new class {
            public $searchInput = '';
            public $itemsPerPageValue = 10;
            public $showAlert = false;
            public $alertType = '';
            public $alertMessage = '';
            public $config;
            
            public function __construct() {
                $this->config = new class {
                    public $sortField = 'id';
                    public $sortDirection = 'asc';
                    public $searchQuery = '';
                    public $page = 1;
                };
            }
            
            public function getConfiguration() {
                return null;
            }
            
            public function getData() {
                return [];
            }
            
            public function renderColumn($name, $value, $item) {
                return '<td>' . htmlspecialchars((string)$value) . '</td>';
            }
        };
        
        return $component;
    }
    
    private function createMockPagination(int $total = 50, int $currentPage = 1, int $itemsPerPage = 10): SlidingPagination
    {
        $pagination = $this->createMock(SlidingPagination::class);
        
        $pagination->method('getTotalItemCount')->willReturn($total);
        $pagination->method('getCurrentPageNumber')->willReturn($currentPage);
        $pagination->method('getPageCount')->willReturn((int)ceil($total / $itemsPerPage));
        $pagination->method('getItemNumberPerPage')->willReturn($itemsPerPage);
        
        $firstItem = ($currentPage - 1) * $itemsPerPage + 1;
        $lastItem = min($currentPage * $itemsPerPage, $total);
        
        $pagination->method('getPaginationData')->willReturn([
            'firstItemNumber' => $firstItem,
            'lastItemNumber' => $lastItem,
            'current' => $currentPage,
            'pageCount' => (int)ceil($total / $itemsPerPage),
            'totalCount' => $total,
            'numItemsPerPage' => $itemsPerPage,
            'first' => 1,
            'last' => (int)ceil($total / $itemsPerPage),
            'next' => $currentPage < ceil($total / $itemsPerPage) ? $currentPage + 1 : null,
            'previous' => $currentPage > 1 ? $currentPage - 1 : null,
            'pagesInRange' => range(max(1, $currentPage - 2), min(ceil($total / $itemsPerPage), $currentPage + 2)),
            'startPage' => max(1, $currentPage - 2),
            'endPage' => min(ceil($total / $itemsPerPage), $currentPage + 2)
        ]);
        
        // Simuler des données
        $items = [];
        if ($total > 0) {
            for ($i = $firstItem; $i <= $lastItem; $i++) {
                $items[] = (object)['id' => $i, 'name' => 'Item ' . $i];
            }
        }
        
        $pagination->method('getItems')->willReturn($items);
        $pagination->method('count')->willReturn(count($items));
        $pagination->method('getIterator')->willReturn(new \ArrayIterator($items));
        
        return $pagination;
    }
}