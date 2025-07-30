<?php
/**
 * Script de diagnostic pour SigmasoftDataTableBundle
 * Exécuter depuis le projet Symfony : php vendor/sigmasoft/datatable-bundle/diagnose-bundle.php
 */

echo "=== DIAGNOSTIC SIGMASOFT DATATABLE BUNDLE ===\n\n";

// 1. Vérification de l'autoload
echo "1. VÉRIFICATION AUTOLOAD\n";
echo "------------------------\n";

$bundleClass = 'Sigmasoft\DataTableBundle\SigmasoftDataTableBundle';
if (class_exists($bundleClass)) {
    echo "✅ Classe bundle trouvée: $bundleClass\n";
    
    $bundle = new $bundleClass();
    echo "✅ Bundle instancié avec succès\n";
    echo "   Path: " . $bundle->getPath() . "\n";
} else {
    echo "❌ ERREUR: Classe bundle non trouvée: $bundleClass\n";
}

// 2. Vérification de l'Extension
echo "\n2. VÉRIFICATION EXTENSION\n";
echo "-------------------------\n";

$extensionClass = 'Sigmasoft\DataTableBundle\DependencyInjection\SigmasoftDataTableExtension';
if (class_exists($extensionClass)) {
    echo "✅ Classe Extension trouvée: $extensionClass\n";
    
    $extension = new $extensionClass();
    echo "✅ Extension instanciée avec succès\n";
    echo "   Alias: " . $extension->getAlias() . "\n";
} else {
    echo "❌ ERREUR: Classe Extension non trouvée: $extensionClass\n";
}

// 3. Vérification du Maker
echo "\n3. VÉRIFICATION MAKER\n";
echo "---------------------\n";

$makerClass = 'Sigmasoft\DataTableBundle\Maker\MakeDataTable';
if (class_exists($makerClass)) {
    echo "✅ Classe Maker trouvée: $makerClass\n";
    
    // Vérifier les méthodes statiques
    if (method_exists($makerClass, 'getCommandName')) {
        echo "✅ Méthode getCommandName existe\n";
        echo "   Nom de commande: " . $makerClass::getCommandName() . "\n";
    } else {
        echo "❌ ERREUR: Méthode getCommandName manquante\n";
    }
} else {
    echo "❌ ERREUR: Classe Maker non trouvée: $makerClass\n";
}

// 4. Vérification de la structure des fichiers
echo "\n4. STRUCTURE DES FICHIERS\n";
echo "-------------------------\n";

$bundleDir = __DIR__;
echo "Bundle directory: $bundleDir\n";

$requiredFiles = [
    'src/SigmasoftDataTableBundle.php',
    'src/SigmasoftDataTableBundle/DependencyInjection/SigmasoftDataTableExtension.php',
    'src/SigmasoftDataTableBundle/DependencyInjection/Configuration.php',
    'src/SigmasoftDataTableBundle/Maker/MakeDataTable.php',
    'templates/SigmasoftDataTableBundle/datatable.html.twig'
];

foreach ($requiredFiles as $file) {
    $fullPath = $bundleDir . '/' . $file;
    if (file_exists($fullPath)) {
        echo "✅ $file\n";
    } else {
        echo "❌ MANQUANT: $file\n";
    }
}

// 5. Vérification du namespace
echo "\n5. VÉRIFICATION NAMESPACE\n";
echo "-------------------------\n";

$composerJson = $bundleDir . '/composer.json';
if (file_exists($composerJson)) {
    $composer = json_decode(file_get_contents($composerJson), true);
    if (isset($composer['autoload']['psr-4'])) {
        foreach ($composer['autoload']['psr-4'] as $namespace => $path) {
            echo "Namespace: $namespace => $path\n";
        }
    }
} else {
    echo "❌ ERREUR: composer.json non trouvé\n";
}

// 6. Test de chargement complet
echo "\n6. TEST CHARGEMENT BUNDLE\n";
echo "--------------------------\n";

try {
    if (class_exists('Symfony\Component\DependencyInjection\ContainerBuilder')) {
        $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $bundle = new $bundleClass();
        
        echo "✅ Container créé\n";
        
        // Test build
        $bundle->build($container);
        echo "✅ Bundle::build() exécuté sans erreur\n";
        
        // Test Extension
        if (class_exists($extensionClass)) {
            $extension = new $extensionClass();
            try {
                $extension->load([], $container);
                echo "✅ Extension::load() exécuté sans erreur\n";
            } catch (\Exception $e) {
                echo "❌ ERREUR Extension::load(): " . $e->getMessage() . "\n";
                echo "   Trace: " . $e->getTraceAsString() . "\n";
            }
        }
    } else {
        echo "⚠️  Symfony DependencyInjection non disponible pour test complet\n";
    }
} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DU DIAGNOSTIC ===\n";