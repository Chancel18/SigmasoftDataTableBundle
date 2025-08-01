<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Commande pour installer la configuration par défaut du bundle
 */
#[AsCommand(
    name: 'sigmasoft:datatable:install-config',
    description: 'Installe la configuration par défaut du SigmasoftDataTableBundle'
)]
class InstallConfigCommand extends Command
{
    public function __construct(
        private KernelInterface $kernel,
        private Filesystem $filesystem
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Installation de la configuration SigmasoftDataTableBundle');
        
        $projectDir = $this->kernel->getProjectDir();
        $bundleDir = dirname((new \ReflectionClass(self::class))->getFileName(), 2);
        
        $sourceConfig = $bundleDir . '/config/install/sigmasoft_data_table.yaml';
        $targetConfig = $projectDir . '/config/packages/sigmasoft_data_table.yaml';
        
        // Vérifier si le fichier source existe
        if (!file_exists($sourceConfig)) {
            $io->error('Le fichier de configuration source n\'existe pas : ' . $sourceConfig);
            return Command::FAILURE;
        }
        
        // Créer le répertoire de destination si nécessaire
        $targetDir = dirname($targetConfig);
        if (!is_dir($targetDir)) {
            $this->filesystem->mkdir($targetDir);
            $io->info('Répertoire créé : ' . $targetDir);
        }
        
        // Vérifier si le fichier existe déjà
        if (file_exists($targetConfig)) {
            if (!$io->confirm('Le fichier de configuration existe déjà. Souhaitez-vous le remplacer ?', false)) {
                $io->info('Installation annulée.');
                return Command::SUCCESS;
            }
            
            // Créer une sauvegarde
            $backupFile = $targetConfig . '.backup.' . date('Y-m-d-H-i-s');
            $this->filesystem->copy($targetConfig, $backupFile);
            $io->info('Sauvegarde créée : ' . $backupFile);
        }
        
        try {
            // Copier le fichier de configuration
            $this->filesystem->copy($sourceConfig, $targetConfig);
            
            $io->success([
                'Configuration installée avec succès !',
                'Fichier créé : ' . $targetConfig,
                '',
                'Vous pouvez maintenant personnaliser votre configuration dans ce fichier.'
            ]);
            
            // Afficher quelques conseils
            $io->section('Prochaines étapes');
            $io->listing([
                'Consultez le fichier ' . $targetConfig . ' pour personnaliser vos paramètres',
                'Utilisez la commande `make:datatable` pour générer vos DataTables',
                'Consultez la documentation : https://chancel18.github.io/SigmasoftDataTableBundle/'
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'installation : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}