#!/usr/bin/env php
<?php

/**
 * Script de diagnostic pour vérifier l'installation du SigmasoftDataTableBundle
 * 
 * Usage: php bin/check-installation.php
 */

echo "🔍 SigmasoftDataTableBundle - Diagnostic d'installation\n";
echo "===================================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Vérifier si Composer est installé
if (!file_exists('composer.json')) {
    $errors[] = "❌ Fichier composer.json non trouvé. Vous devez être à la racine d'un projet Symfony.";
} else {
    $success[] = "✅ Fichier composer.json trouvé";
    
    // Vérifier si le bundle est installé
    $composerContent = file_get_contents('composer.json');
    $composer = json_decode($composerContent, true);
    
    if (isset($composer['require']['sigmasoft/datatable-bundle'])) {
        $version = $composer['require']['sigmasoft/datatable-bundle'];
        $success[] = "✅ Bundle SigmasoftDataTableBundle installé (version: $version)";
    } else {
        $errors[] = "❌ Bundle SigmasoftDataTableBundle non trouvé dans composer.json";
    }
}

// 2. Vérifier config/bundles.php
if (!file_exists('config/bundles.php')) {
    $errors[] = "❌ Fichier config/bundles.php non trouvé";
} else {
    $bundlesContent = file_get_contents('config/bundles.php');
    if (strpos($bundlesContent, 'SigmasoftDataTableBundle') !== false) {
        $success[] = "✅ Bundle enregistré dans config/bundles.php";
    } else {
        $errors[] = "❌ Bundle non enregistré dans config/bundles.php";
        echo "💡 Ajoutez cette ligne dans config/bundles.php :\n";
        echo "   Sigmasoft\\DataTableBundle\\SigmasoftDataTableBundle::class => ['all' => true],\n\n";
    }
}

// 3. Vérifier la configuration YAML
if (!file_exists('config/packages/sigmasoft_data_table.yaml')) {
    $warnings[] = "⚠️  Configuration YAML non trouvée dans config/packages/sigmasoft_data_table.yaml";
    echo "💡 Créez le fichier config/packages/sigmasoft_data_table.yaml avec le contenu suivant :\n";
    echo "```yaml\n";
    echo "sigmasoft_data_table:\n";
    echo "    defaults:\n";
    echo "        items_per_page: 10\n";
    echo "        enable_search: true\n";
    echo "        table_class: 'table table-striped table-hover'\n";
    echo "        date_format: 'd/m/Y'\n";
    echo "```\n\n";
} else {
    $success[] = "✅ Fichier de configuration trouvé";
}

// 4. Vérifier cache/
if (!is_dir('var/cache')) {
    $warnings[] = "⚠️  Répertoire var/cache non trouvé";
} else {
    $success[] = "✅ Répertoire cache présent";
}

// 5. Vérifier les commandes Symfony
if (file_exists('bin/console')) {
    $success[] = "✅ Console Symfony disponible";
    
    // Essayer de lister les commandes
    $output = shell_exec('php bin/console list sigmasoft 2>&1');
    if ($output && strpos($output, 'sigmasoft:datatable') !== false) {
        $success[] = "✅ Commandes SigmasoftDataTableBundle détectées";
    } else {
        $warnings[] = "⚠️  Commandes SigmasoftDataTableBundle non détectées";
        echo "💡 Exécutez: php bin/console cache:clear\n\n";
    }
} else {
    $errors[] = "❌ Console Symfony non trouvée (bin/console)";
}

// Affichage du résumé
echo "\n📊 RÉSUMÉ DU DIAGNOSTIC\n";
echo "===================\n\n";

if (!empty($success)) {
    echo "✅ SUCCÈS (" . count($success) . "):\n";
    foreach ($success as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  AVERTISSEMENTS (" . count($warnings) . "):\n";
    foreach ($warnings as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ ERREURS (" . count($errors) . "):\n";
    foreach ($errors as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

// Recommandations finales
echo "🚀 PROCHAINES ÉTAPES RECOMMANDÉES\n";
echo "===============================\n";
echo "1. php bin/console cache:clear\n";
echo "2. composer dump-autoload\n";
echo "3. php bin/console sigmasoft:datatable:install-config\n";
echo "4. php bin/console make:datatable YourEntity\n";
echo "\n";

if (empty($errors)) {
    echo "🎉 Installation semble correcte ! Vous pouvez commencer à utiliser le bundle.\n";
    exit(0);
} else {
    echo "🔧 Veuillez corriger les erreurs ci-dessus avant de continuer.\n";
    exit(1);
}