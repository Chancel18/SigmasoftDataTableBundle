<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Commande pour installer les assets du bundle (templates, JS, CSS)
 * 
 * @author Gédéon Makela <g.makela@sigmasoft-solution.com>
 */
#[AsCommand(
    name: 'sigmasoft:datatable:install-assets',
    description: 'Install SigmasoftDataTableBundle assets (templates, JS, CSS)',
)]
class InstallAssetsCommand extends Command
{
    public function __construct(
        private KernelInterface $kernel,
        private Filesystem $filesystem
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command installs the SigmasoftDataTableBundle assets into your project')
            ->addOption(
                'symlink',
                null,
                InputOption::VALUE_NONE,
                'Create symlinks instead of copying files'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force overwrite of existing files'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Installing SigmasoftDataTableBundle Assets');

        $bundleDir = $this->getBundleDirectory();
        $projectDir = $this->kernel->getProjectDir();
        $useSymlinks = $input->getOption('symlink');
        $force = $input->getOption('force');

        // 1. Copier les templates
        $this->installTemplates($io, $bundleDir, $projectDir, $useSymlinks, $force);

        // 2. Créer un exemple de configuration si elle n'existe pas
        $this->createExampleConfig($io, $projectDir, $force);

        // 3. Afficher les instructions post-installation
        $this->displayPostInstallInstructions($io);

        $io->success('SigmasoftDataTableBundle assets have been installed successfully!');

        return Command::SUCCESS;
    }

    private function installTemplates(
        SymfonyStyle $io,
        string $bundleDir,
        string $projectDir,
        bool $useSymlinks,
        bool $force
    ): void {
        $io->section('Installing templates...');

        $sourceDir = $bundleDir . '/templates/SigmasoftDataTable';
        $targetDir = $projectDir . '/templates/bundles/SigmasoftDataTableBundle';

        if (!is_dir($sourceDir)) {
            $io->warning('No templates found in the bundle.');
            return;
        }

        // Créer le répertoire cible si nécessaire
        if (!is_dir($targetDir)) {
            $this->filesystem->mkdir($targetDir);
        }

        // Copier ou créer des liens symboliques
        $files = scandir($sourceDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $source = $sourceDir . '/' . $file;
            $target = $targetDir . '/' . $file;

            if (file_exists($target) && !$force) {
                $io->warning("File already exists: $file (use --force to overwrite)");
                continue;
            }

            if ($useSymlinks) {
                $this->filesystem->symlink($source, $target);
                $io->writeln(" <info>✓</info> Created symlink: $file");
            } else {
                $this->filesystem->copy($source, $target, true);
                $io->writeln(" <info>✓</info> Copied: $file");
            }
        }

        $io->success('Templates installed successfully!');
    }

    private function createExampleConfig(SymfonyStyle $io, string $projectDir, bool $force): void
    {
        $io->section('Creating example configuration...');

        $configFile = $projectDir . '/config/packages/sigmasoft_data_table.yaml';

        if (file_exists($configFile) && !$force) {
            $io->note('Configuration file already exists. Skipping...');
            return;
        }

        $configContent = <<<YAML
# SigmasoftDataTableBundle Configuration
sigmasoft_data_table:
    defaults:
        items_per_page: 10
        enable_search: true
        enable_sort: true
        enable_pagination: true
        table_class: 'table table-striped table-hover'
        date_format: 'd/m/Y'
        
    # Entity-specific configurations (optional)
    entities:
        # App\Entity\User:
        #     items_per_page: 25
        #     searchable_fields: ['username', 'email', 'firstName', 'lastName']
        #     sortable_fields: ['username', 'email', 'createdAt']
YAML;

        $this->filesystem->dumpFile($configFile, $configContent);
        $io->success('Example configuration created at: config/packages/sigmasoft_data_table.yaml');
    }

    private function displayPostInstallInstructions(SymfonyStyle $io): void
    {
        $io->section('Post-Installation Instructions');

        $io->listing([
            'Clear the cache: php bin/console cache:clear',
            'Install assets: php bin/console assets:install',
            'Create your first DataTable: php bin/console make:datatable YourEntity',
        ]);

        $io->note([
            'Templates have been copied to: templates/bundles/SigmasoftDataTableBundle/',
            'You can customize these templates as needed.',
            'For more information, visit: https://github.com/Chancel18/SigmasoftDataTableBundle'
        ]);
    }

    private function getBundleDirectory(): string
    {
        $reflection = new \ReflectionClass(\Sigmasoft\DataTableBundle\SigmasoftDataTableBundle::class);
        return dirname($reflection->getFileName());
    }
}