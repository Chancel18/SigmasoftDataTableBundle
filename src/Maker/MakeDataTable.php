<?php

/**
 * MakeDataTable - Commande Maker pour génération automatique de DataTables
 * 
 * @author Gédeon Makela <g.makela@sigmasoft-solution.com>
 * @copyright 2024 Sigmasoft Solution
 * @package Sigmasoft\DataTableBundle\Maker
 */

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Maker;

use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

final class MakeDataTable extends AbstractMaker
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private array $bundleConfig = []
    ) {
    }

    public static function getCommandName(): string
    {
        return 'make:datatable';
    }

    public static function getCommandDescription(): string
    {
        return 'Generate a SigmasoftDataTable for an entity with automatic column detection';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity class name (e.g. User, Product)')
            ->addOption('controller', 'c', InputOption::VALUE_OPTIONAL, 'The controller class name (auto-detected if not provided)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Override existing controller method')
            ->setHelp('This command generates a DataTable configuration in an existing controller index method.');
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        $dependencies->addClassDependency(DataTableBuilder::class, 'sigmasoft-datatable');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entityClass = $input->getArgument('entity');
        $controllerClass = $input->getOption('controller');
        $force = $input->getOption('force');

        // Validate entity
        $fullEntityClass = $this->getFullEntityClass($entityClass);
        if (!$this->entityExists($fullEntityClass)) {
            $io->error(sprintf('Entity "%s" not found.', $fullEntityClass));
            return;
        }

        // Auto-detect controller if not provided
        if (!$controllerClass) {
            $controllerClass = $this->detectControllerClass($entityClass);
        }

        if (!$controllerClass) {
            $io->error(sprintf('No controller found for entity "%s". Use --controller option to specify one.', $entityClass));
            return;
        }

        $fullControllerClass = $this->getFullControllerClass($controllerClass);
        
        // Validate controller and method
        if (!$this->validateController($fullControllerClass, $io)) {
            return;
        }

        // Validate template
        if (!$this->validateTemplate($entityClass, $io)) {
            return;
        }

        // Generate DataTable configuration
        $this->generateDataTableConfiguration($fullEntityClass, $fullControllerClass, $entityClass, $force, $io, $generator);

        $io->success(sprintf(
            'DataTable configuration generated successfully for %s in %s::index()',
            $entityClass,
            $controllerClass
        ));
    }

    private function getFullEntityClass(string $entityClass): string
    {
        if (str_contains($entityClass, '\\')) {
            return $entityClass;
        }
        return 'App\\Entity\\' . $entityClass;
    }

    private function getFullControllerClass(string $controllerClass): string
    {
        if (str_contains($controllerClass, '\\')) {
            return $controllerClass;
        }
        return 'App\\Controller\\' . $controllerClass;
    }

    private function entityExists(string $entityClass): bool
    {
        try {
            $this->entityManager->getClassMetadata($entityClass);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    private function detectControllerClass(string $entityClass): ?string
    {
        $possibleControllers = [
            $entityClass . 'Controller',
            $entityClass . 'sController', // pluriel
            'Admin' . $entityClass . 'Controller',
        ];

        $filesystem = new Filesystem();
        $controllerDir = $this->parameterBag->get('kernel.project_dir') . '/src/Controller';

        foreach ($possibleControllers as $controllerName) {
            $controllerPath = $controllerDir . '/' . $controllerName . '.php';
            if ($filesystem->exists($controllerPath)) {
                return $controllerName;
            }
        }

        return null;
    }

    private function validateController(string $controllerClass, ConsoleStyle $io): bool
    {
        if (!class_exists($controllerClass)) {
            $io->error(sprintf('Controller "%s" not found.', $controllerClass));
            return false;
        }

        if (!method_exists($controllerClass, 'index')) {
            $io->error(sprintf('Method "index" not found in controller "%s".', $controllerClass));
            return false;
        }

        return true;
    }

    private function validateTemplate(string $entityClass, ConsoleStyle $io): bool
    {
        $templatePath = $this->getTemplatePath($entityClass);
        $filesystem = new Filesystem();

        if (!$filesystem->exists($templatePath)) {
            $io->error(sprintf('Template "%s" not found.', $templatePath));
            return false;
        }

        return true;
    }

    private function getTemplatePath(string $entityClass): string
    {
        $entityName = strtolower($entityClass);
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        return $projectDir . '/templates/' . $entityName . '/index.html.twig';
    }

    private function generateDataTableConfiguration(
        string $fullEntityClass,
        string $fullControllerClass,
        string $entityClass,
        bool $force,
        ConsoleStyle $io,
        Generator $generator
    ): void {
        $metadata = $this->entityManager->getClassMetadata($fullEntityClass);
        $columns = $this->generateColumnsFromMetadata($metadata);
        $actions = $this->generateDefaultActions($entityClass);

        $dataTableCode = $this->generateDataTableCode($entityClass, $columns, $actions);

        // Read current controller file
        $controllerPath = $this->getControllerPath($fullControllerClass);
        $currentContent = file_get_contents($controllerPath);

        // Check if DataTable code already exists
        if (str_contains($currentContent, 'DataTableBuilder') && !$force) {
            $io->warning('DataTable configuration already exists. Use --force to override.');
            return;
        }

        // Inject DataTable code into index method
        $newContent = $this->injectDataTableCode($currentContent, $dataTableCode, $force);
        
        file_put_contents($controllerPath, $newContent);
    }

    private function generateColumnsFromMetadata(ClassMetadata $metadata): array
    {
        $columns = [];
        $excludedProperties = $this->bundleConfig['maker']['excluded_properties'] ?? [
            'password', 'plainPassword', 'salt', 'token', 'resetToken'
        ];

        foreach ($metadata->getFieldNames() as $fieldName) {
            if (in_array($fieldName, $excludedProperties)) {
                continue;
            }

            $fieldType = $metadata->getTypeOfField($fieldName);
            $columnType = $this->getColumnTypeFromDoctrine($fieldType);
            
            $columns[] = [
                'name' => $fieldName,
                'type' => $columnType,
                'label' => ucfirst(str_replace('_', ' ', $fieldName)),
                'options' => $this->getColumnOptions($fieldType, $fieldName)
            ];
        }

        return $columns;
    }

    private function getColumnTypeFromDoctrine(string $doctrineType): string
    {
        $typeMapping = $this->bundleConfig['maker']['default_column_types'] ?? [
            'string' => 'text',
            'text' => 'text',
            'integer' => 'number',
            'float' => 'number',
            'decimal' => 'number',
            'boolean' => 'badge',
            'datetime' => 'date',
            'datetime_immutable' => 'date',
            'date' => 'date',
            'date_immutable' => 'date',
            'time' => 'date',
            'time_immutable' => 'date',
        ];

        return $typeMapping[$doctrineType] ?? 'text';
    }

    private function getColumnOptions(string $fieldType, string $fieldName): array
    {
        $options = [];

        // Ajouter sortable par défaut sauf pour les textes longs
        if (!in_array($fieldType, ['text'])) {
            $options['sortable'] = true;
        }

        // Ajouter searchable pour les champs texte
        if (in_array($fieldType, ['string', 'text'])) {
            $options['searchable'] = true;
        }

        // Options spécifiques par type
        switch ($fieldType) {
            case 'integer':
                $options['format'] = 'integer';
                $options['thousands_separator'] = ' ';
                break;
            case 'float':
            case 'decimal':
                $options['format'] = 'decimal';
                $options['decimals'] = 2;
                $options['thousands_separator'] = ' ';
                $options['decimal_separator'] = ',';
                break;
            case 'boolean':
                $options['value_mapping'] = [
                    '1' => 'Oui',
                    '0' => 'Non'
                ];
                break;
            case 'datetime':
            case 'datetime_immutable':
                $options['format'] = 'd/m/Y H:i';
                break;
            case 'date':
            case 'date_immutable':
                $options['format'] = 'd/m/Y';
                break;
        }

        // ID non searchable
        if ($fieldName === 'id') {
            $options['searchable'] = false;
        }

        return $options;
    }

    private function generateDefaultActions(string $entityClass): array
    {
        if (!($this->bundleConfig['maker']['auto_add_actions'] ?? true)) {
            return [];
        }

        $entityLower = strtolower($entityClass);
        $defaultActions = $this->bundleConfig['maker']['default_actions'] ?? [];

        $actions = [];
        
        if (isset($defaultActions['show'])) {
            $actions['show'] = array_merge($defaultActions['show'], [
                'route' => 'app_' . $entityLower . '_show'
            ]);
        }

        if (isset($defaultActions['edit'])) {
            $actions['edit'] = array_merge($defaultActions['edit'], [
                'route' => 'app_' . $entityLower . '_edit'
            ]);
        }

        if (isset($defaultActions['delete'])) {
            $actions['delete'] = $defaultActions['delete'];
        }

        return $actions;
    }

    private function generateDataTableCode(string $entityClass, array $columns, array $actions): string
    {
        $code = "\n        // Génération automatique DataTable pour {$entityClass}\n";
        $code .= "        \$config = \$builder->createDataTable({$entityClass}::class);\n\n";

        // Générer les colonnes
        foreach ($columns as $column) {
            $options = var_export($column['options'], true);
            $code .= "        \$config = \$builder->add{$this->getColumnMethodName($column['type'])}(\$config, '{$column['name']}', '{$column['name']}', '{$column['label']}'";
            
            if (!empty($column['options'])) {
                $code .= ", {$options}";
            }
            
            $code .= ");\n";
        }

        // Générer les actions si configurées
        if (!empty($actions)) {
            $code .= "\n        // Actions CRUD\n";
            $actionsCode = var_export($actions, true);
            $code .= "        \$config = \$builder->addActionColumn(\$config, {$actionsCode});\n";
        }

        // Configuration finale
        $code .= "\n        // Configuration finale\n";
        $code .= "        \$config = \$builder->configureSearch(\$config, true, " . $this->getSearchableFields($columns) . ");\n";
        $code .= "        \$config = \$builder->configurePagination(\$config, true, 10);\n";
        $code .= "        \$config = \$builder->configureSorting(\$config, true, 'id', 'desc');\n\n";

        $code .= "        return \$this->render('" . strtolower($entityClass) . "/index.html.twig', [\n";
        $code .= "            'datatableConfig' => \$config,\n";
        $code .= "        ]);\n";

        return $code;
    }

    private function getColumnMethodName(string $type): string
    {
        return match ($type) {
            'text' => 'TextColumn',
            'date' => 'DateColumn',
            'badge' => 'BadgeColumn',
            'action' => 'ActionColumn',
            'number' => 'NumberColumn',
            default => 'TextColumn'
        };
    }

    private function getSearchableFields(array $columns): string
    {
        $searchableFields = [];
        foreach ($columns as $column) {
            if ($column['options']['searchable'] ?? false) {
                $searchableFields[] = "'{$column['name']}'";
            }
        }

        return '[' . implode(', ', $searchableFields) . ']';
    }

    private function getControllerPath(string $controllerClass): string
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $relativePath = str_replace('App\\', 'src/', $controllerClass);
        $relativePath = str_replace('\\', '/', $relativePath);
        return $projectDir . '/' . $relativePath . '.php';
    }

    private function injectDataTableCode(string $currentContent, string $dataTableCode, bool $force): string
    {
        // Ajouter l'import DataTableBuilder si nécessaire
        if (!str_contains($currentContent, 'use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;')) {
            $currentContent = preg_replace(
                '/(namespace App\\\\Controller;)/',
                "$1\n\nuse Sigmasoft\\DataTableBundle\\Builder\\DataTableBuilder;",
                $currentContent
            );
        }

        // Ajouter DataTableBuilder dans les paramètres de la méthode index si nécessaire
        if (!str_contains($currentContent, 'DataTableBuilder $builder')) {
            $currentContent = preg_replace(
                '/public function index\(\s*\)\s*:\s*Response/',
                'public function index(DataTableBuilder $builder): Response',
                $currentContent
            );
        }

        // Remplacer ou injecter le contenu de la méthode index
        $pattern = '/public function index\([^)]*\)\s*:\s*Response\s*\{(.*?)\}/s';
        
        if (preg_match($pattern, $currentContent)) {
            if ($force || !str_contains($currentContent, 'DataTableBuilder')) {
                $replacement = "public function index(DataTableBuilder \$builder): Response\n    {\n{$dataTableCode}    }";
                $currentContent = preg_replace($pattern, $replacement, $currentContent);
            }
        }

        return $currentContent;
    }
}
