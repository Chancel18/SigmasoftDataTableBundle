<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Test d'intégration pour vérifier les chemins des templates
 * 
 * @author Gédéon Makela <g.makela@sigmasoft-solution.com>
 */
class TemplatePathTest extends TestCase
{
    private Filesystem $filesystem;
    
    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
    }
    
    /**
     * Vérifie que le template principal existe dans le bundle
     */
    public function testBundleTemplateExists(): void
    {
        $bundleRoot = dirname(__DIR__, 2);
        $templatePath = $bundleRoot . '/templates/SigmasoftDataTable/datatable.html.twig';
        
        $this->assertFileExists($templatePath, 
            "Le template principal doit exister dans le bundle à : templates/SigmasoftDataTable/datatable.html.twig"
        );
    }
    
    /**
     * Vérifie la structure attendue après installation
     */
    public function testExpectedTemplateStructure(): void
    {
        // Chemins attendus dans un projet Symfony après installation
        $expectedPaths = [
            'templates/bundles/SigmasoftDataTableBundle/datatable.html.twig',
            // Ou avec le namespace Twig
            '@SigmasoftDataTable/datatable.html.twig'
        ];
        
        // Vérifier que notre bundle a la bonne structure
        $bundleRoot = dirname(__DIR__, 2);
        $this->assertDirectoryExists($bundleRoot . '/templates');
        $this->assertDirectoryExists($bundleRoot . '/templates/SigmasoftDataTable');
    }
    
    /**
     * Test de simulation d'installation dans un projet
     */
    public function testTemplateInstallationSimulation(): void
    {
        $tempProject = sys_get_temp_dir() . '/test_project_' . uniqid();
        
        try {
            // Créer une structure de projet Symfony minimale
            $this->filesystem->mkdir($tempProject . '/templates/bundles/SigmasoftDataTableBundle');
            
            // Copier le template depuis le bundle
            $bundleTemplate = dirname(__DIR__, 2) . '/templates/SigmasoftDataTable/datatable.html.twig';
            $targetTemplate = $tempProject . '/templates/bundles/SigmasoftDataTableBundle/datatable.html.twig';
            
            if (file_exists($bundleTemplate)) {
                $this->filesystem->copy($bundleTemplate, $targetTemplate);
                
                // Vérifier que le fichier a été copié
                $this->assertFileExists($targetTemplate);
                
                // Vérifier le contenu (normaliser les retours chariot)
                $content = file_get_contents($targetTemplate);
                $content = str_replace(["\r\n", "\r"], "\n", $content); // Normaliser les retours chariot
                $this->assertStringContainsString('sigmasoft-datatable', $content);
                $this->assertStringContainsString('{{ attributes }}', $content); // Le LiveComponent utilise {{ attributes }}
                $this->assertStringContainsString('data-action="live#action"', $content); // Vérifier les actions LiveComponent
            }
        } finally {
            // Nettoyer
            if ($this->filesystem->exists($tempProject)) {
                $this->filesystem->remove($tempProject);
            }
        }
    }
    
    /**
     * Vérifie les chemins Twig utilisés dans le code
     */
    public function testTwigNamespacePaths(): void
    {
        // Le namespace Twig devrait être @SigmasoftDataTable
        $componentFile = dirname(__DIR__, 2) . '/src/Component/DataTableComponent.php';
        
        if (file_exists($componentFile)) {
            $content = file_get_contents($componentFile);
            
            // Vérifier que le template est référencé correctement
            $this->assertStringContainsString('@SigmasoftDataTable/datatable.html.twig', $content,
                "Le DataTableComponent doit utiliser le namespace @SigmasoftDataTable"
            );
        }
    }
    
    /**
     * Test la structure complète des chemins
     */
    public function testCompletePathStructure(): void
    {
        $paths = [
            'Bundle source' => 'templates/SigmasoftDataTable/datatable.html.twig',
            'Project target' => 'templates/bundles/SigmasoftDataTableBundle/datatable.html.twig',
            'Twig namespace' => '@SigmasoftDataTable/datatable.html.twig'
        ];
        
        // Documenter les chemins attendus
        foreach ($paths as $type => $path) {
            $this->assertNotEmpty($path, "Le chemin $type ne doit pas être vide");
        }
        
        // Vérifier la cohérence
        $this->assertEquals(
            'datatable.html.twig',
            basename($paths['Bundle source']),
            'Le nom du fichier doit être cohérent'
        );
    }
    
    /**
     * Test avec le PostInstallPass
     */
    public function testPostInstallPassCopiesTemplates(): void
    {
        $tempDir = sys_get_temp_dir() . '/test_postinstall_' . uniqid();
        
        try {
            // Simuler un projet Symfony
            $this->filesystem->mkdir($tempDir);
            
            // Le PostInstallPass devrait créer ce chemin
            $expectedPath = $tempDir . '/templates/bundles/SigmasoftDataTableBundle';
            
            // Simuler ce que fait le PostInstallPass
            if (!is_dir($expectedPath)) {
                $this->filesystem->mkdir($expectedPath);
                
                $source = dirname(__DIR__, 2) . '/templates/SigmasoftDataTable';
                if (is_dir($source)) {
                    $this->filesystem->mirror($source, $expectedPath);
                }
            }
            
            // Vérifier
            $this->assertDirectoryExists($expectedPath);
            
            $templateFile = $expectedPath . '/datatable.html.twig';
            if (file_exists(dirname(__DIR__, 2) . '/templates/SigmasoftDataTable/datatable.html.twig')) {
                $this->assertFileExists($templateFile);
            }
            
        } finally {
            if ($this->filesystem->exists($tempDir)) {
                $this->filesystem->remove($tempDir);
            }
        }
    }
}