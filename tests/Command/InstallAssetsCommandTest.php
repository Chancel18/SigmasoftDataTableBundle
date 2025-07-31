<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\Command\InstallAssetsCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class InstallAssetsCommandTest extends TestCase
{
    private string $tempDir;
    private Filesystem $filesystem;
    private CommandTester $commandTester;
    
    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->tempDir = sys_get_temp_dir() . '/sigmasoft_test_' . uniqid();
        $this->filesystem->mkdir($this->tempDir);
        
        // Créer une structure de test
        $this->filesystem->mkdir($this->tempDir . '/templates');
        $this->filesystem->mkdir($this->tempDir . '/config/packages');
        
        // Mock du Kernel
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->tempDir);
        
        // Créer la commande
        $command = new InstallAssetsCommand($kernel, $this->filesystem);
        
        $application = new Application();
        $application->add($command);
        
        $this->commandTester = new CommandTester($application->find('sigmasoft:datatable:install-assets'));
    }
    
    protected function tearDown(): void
    {
        $this->filesystem->remove($this->tempDir);
    }
    
    public function testInstallAssetsCreatesTemplateDirectory(): void
    {
        // Exécuter la commande
        $this->commandTester->execute([]);
        
        // Vérifier que le répertoire des templates a été créé
        $expectedDir = $this->tempDir . '/templates/bundles/SigmasoftDataTableBundle';
        $this->assertDirectoryExists($expectedDir);
    }
    
    public function testInstallAssetsCopiesTemplates(): void
    {
        // Créer un template de test dans le bundle
        $bundleTemplateDir = dirname(__DIR__, 2) . '/templates/SigmasoftDataTable';
        if (is_dir($bundleTemplateDir)) {
            $this->commandTester->execute([]);
            
            $targetDir = $this->tempDir . '/templates/bundles/SigmasoftDataTableBundle';
            $this->assertDirectoryExists($targetDir);
            
            // Vérifier qu'au moins un fichier a été copié
            $files = scandir($targetDir);
            $this->assertGreaterThan(2, count($files)); // Plus que . et ..
        }
    }
    
    public function testInstallAssetsWithForceOption(): void
    {
        // Créer un fichier existant
        $targetDir = $this->tempDir . '/templates/bundles/SigmasoftDataTableBundle';
        $this->filesystem->mkdir($targetDir);
        $this->filesystem->dumpFile($targetDir . '/test.html.twig', 'existing content');
        
        // Exécuter avec --force
        $this->commandTester->execute(['--force' => true]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Templates installed successfully!', $output);
    }
    
    public function testInstallAssetsCreatesExampleConfig(): void
    {
        $this->commandTester->execute([]);
        
        $configFile = $this->tempDir . '/config/packages/sigmasoft_data_table.yaml';
        $this->assertFileExists($configFile);
        
        $content = file_get_contents($configFile);
        $this->assertStringContainsString('sigmasoft_data_table:', $content);
        $this->assertStringContainsString('items_per_page:', $content);
    }
    
    public function testCommandSuccessOutput(): void
    {
        $this->commandTester->execute([]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Installing SigmasoftDataTableBundle Assets', $output);
        $this->assertStringContainsString('SigmasoftDataTableBundle assets have been installed successfully!', $output);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}