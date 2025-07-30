@echo off
echo =======================================================
echo Test des commandes Make dans SymfonyTestDataTableBundle
echo =======================================================

cd "C:\Users\gedeo\Documents\PROJET DEV\PHP PROJECT\SigmasoftBundleProject\Sigmasoft\SymfonyTestDataTableBundle"

echo.
echo [1] Liste de tous les services DataTable enregistres:
php bin/console debug:container sigmasoft

echo.
echo [2] Verification du bundle:
php bin/console debug:config sigmasoft_data_table

echo.
echo [3] Liste des commandes Make disponibles:
php bin/console list make

echo.
echo [4] Test direct de la commande make:datatable:
php bin/console make:datatable --help

echo.
echo [5] Test avec entite User:
php bin/console make:datatable User --help

pause