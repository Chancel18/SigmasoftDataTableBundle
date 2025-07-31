<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * EventSubscriber pour copier automatiquement les templates après l'installation
 * 
 * @author Gédéon Makela <g.makela@sigmasoft-solution.com>
 */
class PostInstallSubscriber implements EventSubscriberInterface
{
    private static bool $templatesChecked = false;
    
    public function __construct(
        private KernelInterface $kernel,
        private Filesystem $filesystem
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 255]
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // On ne vérifie qu'une seule fois par requête
        if (self::$templatesChecked || !$event->isMainRequest()) {
            return;
        }
        
        self::$templatesChecked = true;
        
        // Seulement en environnement dev
        if ($this->kernel->getEnvironment() !== 'dev') {
            return;
        }
        
        $this->checkAndCopyTemplates();
    }
    
    private function checkAndCopyTemplates(): void
    {
        $projectDir = $this->kernel->getProjectDir();
        $targetDir = $projectDir . '/templates/bundles/SigmasoftDataTableBundle';
        
        // Si les templates existent déjà, on ne fait rien
        if (is_dir($targetDir) && count(scandir($targetDir)) > 2) {
            return;
        }
        
        // Trouver le répertoire source des templates
        $reflection = new \ReflectionClass(\Sigmasoft\DataTableBundle\SigmasoftDataTableBundle::class);
        $bundleDir = dirname($reflection->getFileName());
        $sourceDir = $bundleDir . '/templates/SigmasoftDataTable';
        
        if (!is_dir($sourceDir)) {
            return;
        }
        
        try {
            // Créer le répertoire cible si nécessaire
            if (!is_dir($targetDir)) {
                $this->filesystem->mkdir($targetDir);
            }
            
            // Copier les templates
            $this->filesystem->mirror($sourceDir, $targetDir, null, ['override' => false]);
            
        } catch (\Exception $e) {
            // En cas d'erreur, on ne fait rien pour ne pas bloquer l'application
        }
    }
}