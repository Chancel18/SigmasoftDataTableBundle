<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Maker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

/**
 * Générateur de DataTable basé sur une entité Doctrine
 */
final class MakeDataTable extends AbstractMaker
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public static function getCommandName(): string
    {
        return 'make:datatable';
    }

    public static function getCommandDescription(): string
    {
        return 'Génère un DataTable complet basé sur une entité Doctrine';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('entity-class', InputArgument::OPTIONAL, 'Nom de la classe d\'entité (ex: User)')
            ->addOption('controller', 'c', InputOption::VALUE_NONE, 'Générer aussi le contrôleur CRUD')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Écraser les fichiers existants')
            ->addOption('with-actions', 'a', InputOption::VALUE_NONE, 'Inclure les actions CRUD (view, edit, delete)')
            ->addOption('with-export', 'x', InputOption::VALUE_NONE, 'Inclure les fonctionnalités d\'export')
            ->addOption('with-bulk', 'b', InputOption::VALUE_NONE, 'Inclure les actions groupées')
            ->addOption('template-path', 't', InputOption::VALUE_REQUIRED, 'Chemin du template à générer', 'admin/')
            ->setHelp(
                <<<'EOF'
La commande <info>%command.name%</info> génère automatiquement un DataTable complet 
basé sur une entité Doctrine existante.

<info>php %command.full_name% User</info>

Cela génère :
• Configuration YAML avec auto-détection des types de champs
• Template Twig avec composant DataTable intégré  
• Contrôleur CRUD optionnel
• Routes pour les actions CRUD

Options avancées :
<info>php %command.full_name% User --controller --with-actions --with-export</info>

Avec raccourcis :
<info>php %command.full_name% User -c -a -x -b</info>
EOF
            );

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        if ($input->getArgument('entity-class')) {
            return;
        }

        $argument = $command->getDefinition()->getArgument('entity-class');

        $entities = $this->getEntityChoices();

        $question = new Question($argument->getDescription());
        $question->setAutocompleterValues($entities);

        $value = $io->askQuestion($question);

        $input->setArgument('entity-class', $value);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entityClassNameDetails = $generator->createClassNameDetails(
            Validator::entityExists($input->getArgument('entity-class'), $this->getEntityChoices()),
            'Entity\\'
        );

        $entityClass = $entityClassNameDetails->getFullName();
        $entityShortName = $entityClassNameDetails->getShortName();

        $io->title(sprintf('🚀 Génération DataTable pour %s', $entityShortName));

        // 1. Analyser l'entité Doctrine
        $metadata = $this->analyzeEntity($entityClass);
        $fields = $this->extractFields($metadata, $io);

        // 2. Générer la configuration YAML
        $yamlConfig = $this->generateYamlConfiguration($entityClass, $fields, $input);
        $this->writeYamlConfiguration($yamlConfig, $entityShortName, $generator, $io);

        // 3. Générer le template Twig
        $templatePath = $this->generateTwigTemplate($entityShortName, $input, $generator, $io);

        // 4. Générer le contrôleur si demandé
        if ($input->getOption('controller')) {
            $this->generateController($entityClassNameDetails, $templatePath, $generator, $io);
        }

        // 5. Résultats
        $this->displayResults($io, $entityShortName, $templatePath, $input->getOption('controller'));
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        $dependencies->addClassDependency(
            EntityManagerInterface::class,
            'doctrine/orm'
        );
    }

    private function getEntityChoices(): array
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        return array_map(function (ClassMetadata $classMetadata) {
            return $classMetadata->getName();
        }, $metadata);
    }

    private function analyzeEntity(string $entityClass): ClassMetadata
    {
        try {
            return $this->entityManager->getClassMetadata($entityClass);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf(
                'Impossible d\'analyser l\'entité "%s": %s',
                $entityClass,
                $e->getMessage()
            ));
        }
    }

    private function extractFields(ClassMetadata $metadata, ConsoleStyle $io): array
    {
        $fields = [];
        $entityName = $metadata->getReflectionClass()->getShortName();

        $io->section('🔍 Analyse des champs de l\'entité');

        // Champs simples
        foreach ($metadata->getFieldNames() as $fieldName) {
            $fieldMapping = $metadata->getFieldMapping($fieldName);
            $type = $this->mapDoctrineTypeToDataTableType($fieldMapping['type']);

            $fields[$fieldName] = [
                'type' => $type,
                'label' => $this->generateFieldLabel($fieldName),
                'sortable' => $this->isFieldSortable($fieldMapping),
                'searchable' => $this->isFieldSearchable($fieldMapping),
            ];

            // Configuration spécifique selon le type
            $fields[$fieldName] = array_merge($fields[$fieldName], $this->getTypeSpecificConfig($fieldMapping));

            $io->text(sprintf('  ✅ <info>%s</info> → %s (%s)', 
                $fieldName, 
                $fields[$fieldName]['label'], 
                $type
            ));
        }

        // Relations (associations)
        foreach ($metadata->getAssociationNames() as $associationName) {
            $associationMapping = $metadata->getAssociationMapping($associationName);
            
            if ($metadata->isSingleValuedAssociation($associationName)) {
                // ManyToOne, OneToOne
                $targetEntity = $associationMapping['targetEntity'];
                $targetMetadata = $this->entityManager->getClassMetadata($targetEntity);
                
                // Essayer de trouver un champ "name", "title", "label" ou utiliser l'id
                $displayField = $this->findDisplayField($targetMetadata);
                
                $fieldKey = $associationName . '.' . $displayField;
                $fields[$fieldKey] = [
                    'type' => 'string',
                    'label' => $this->generateFieldLabel($associationName) . ' (' . $displayField . ')',
                    'sortable' => true,
                    'searchable' => true,
                    'relation' => [
                        'entity' => $targetEntity,
                        'field' => $displayField
                    ]
                ];

                $io->text(sprintf('  🔗 <info>%s</info> → %s (relation)', 
                    $fieldKey, 
                    $fields[$fieldKey]['label']
                ));
            }
        }

        return $fields;
    }

    private function mapDoctrineTypeToDataTableType(string $doctrineType): string
    {
        return match ($doctrineType) {
            'integer', 'bigint', 'smallint' => 'integer',
            'decimal', 'float' => 'currency',
            'boolean' => 'boolean',
            'date' => 'date',
            'datetime', 'datetime_immutable' => 'datetime',
            'time' => 'time',
            'text', 'json' => 'text',
            'guid', 'uuid' => 'string',
            default => 'string',
        };
    }

    private function generateFieldLabel(string $fieldName): string
    {
        // Convertir camelCase en "Title Case"
        $label = preg_replace('/([a-z])([A-Z])/', '$1 $2', $fieldName);
        return ucwords(strtolower($label));
    }

    private function isFieldSortable(array $fieldMapping): bool
    {
        // Les champs avec index ou unique sont généralement triables
        return !in_array($fieldMapping['type'], ['text', 'json', 'blob']);
    }

    private function isFieldSearchable(array $fieldMapping): bool
    {
        // Les champs textuels sont recherchables
        return in_array($fieldMapping['type'], ['string', 'text']);
    }

    private function getTypeSpecificConfig(array $fieldMapping): array
    {
        $config = [];

        if ($fieldMapping['type'] === 'string' && isset($fieldMapping['length'])) {
            $config['maxLength'] = min($fieldMapping['length'], 50);
        }

        if ($fieldMapping['type'] === 'datetime') {
            $config['format'] = 'd/m/Y H:i';
        }

        if ($fieldMapping['type'] === 'date') {
            $config['format'] = 'd/m/Y';
        }

        if (str_contains(strtolower($fieldMapping['fieldName']), 'email')) {
            $config['type'] = 'email';
        }

        if (str_contains(strtolower($fieldMapping['fieldName']), 'url')) {
            $config['type'] = 'url';
        }

        if (str_contains(strtolower($fieldMapping['fieldName']), 'image') || 
            str_contains(strtolower($fieldMapping['fieldName']), 'avatar') ||
            str_contains(strtolower($fieldMapping['fieldName']), 'photo')) {
            $config['type'] = 'image';
        }

        return $config;
    }

    private function findDisplayField(ClassMetadata $metadata): string
    {
        $preferredFields = ['name', 'title', 'label', 'email', 'username'];
        
        foreach ($preferredFields as $field) {
            if ($metadata->hasField($field)) {
                return $field;
            }
        }

        foreach ($metadata->getFieldNames() as $fieldName) {
            $fieldMapping = $metadata->getFieldMapping($fieldName);
            if ($fieldMapping['type'] === 'string') {
                return $fieldName;
            }
        }

        return 'id';
    }

    private function generateYamlConfiguration(string $entityClass, array $fields, InputInterface $input): array
    {
        $entityShortName = (new \ReflectionClass($entityClass))->getShortName();
        
        $config = [
            'sigmasoft_data_table' => [
                'entities' => [
                    $entityClass => [
                        'label' => 'Gestion des ' . $this->pluralize($entityShortName),
                        'items_per_page' => 25,
                        'enable_search' => true,
                        'enable_sort' => true,
                        'enable_pagination' => true,
                        'fields' => $fields
                    ]
                ]
            ]
        ];

        // Actions CRUD
        if ($input->getOption('with-actions')) {
            $routePrefix = strtolower($entityShortName);
            $config['sigmasoft_data_table']['entities'][$entityClass]['actions'] = [
                'view' => [
                    'label' => 'Voir',
                    'icon' => 'eye',
                    'variant' => 'info',
                    'route' => $routePrefix . '_show'
                ],
                'edit' => [
                    'label' => 'Modifier',
                    'icon' => 'edit',
                    'variant' => 'primary',
                    'route' => $routePrefix . '_edit'
                ],
                'delete' => [
                    'label' => 'Supprimer',
                    'icon' => 'trash',
                    'variant' => 'danger',
                    'confirm' => true
                ]
            ];
        }

        // Actions groupées
        if ($input->getOption('with-bulk')) {
            $config['sigmasoft_data_table']['entities'][$entityClass]['bulk_actions'] = [
                'delete' => [
                    'label' => 'Supprimer la sélection',
                    'icon' => 'trash',
                    'variant' => 'danger',
                    'confirm' => true,
                    'confirmMessage' => 'Êtes-vous sûr de vouloir supprimer les éléments sélectionnés ?'
                ]
            ];
        }

        // Export
        if ($input->getOption('with-export')) {
            $config['sigmasoft_data_table']['entities'][$entityClass]['enable_export'] = true;
            $config['sigmasoft_data_table']['entities'][$entityClass]['export'] = [
                'formats' => ['csv', 'xlsx']
            ];
        }

        return $config;
    }

    private function writeYamlConfiguration(array $config, string $entityName, Generator $generator, ConsoleStyle $io): void
    {
        $configPath = 'config/packages/sigmasoft_data_table.yaml';
        $yamlContent = Yaml::dump($config, 4, 2);

        if ($generator->fileExists($configPath)) {
            $io->note('Configuration existante trouvée, ajout de l\'entité...');
            
            // Fusionner avec configuration existante
            $existingConfig = Yaml::parseFile($generator->getRootDirectory() . '/' . $configPath);
            if (!isset($existingConfig['sigmasoft_data_table']['entities'])) {
                $existingConfig['sigmasoft_data_table']['entities'] = [];
            }
            
            $entityClass = array_key_first($config['sigmasoft_data_table']['entities']);
            $existingConfig['sigmasoft_data_table']['entities'][$entityClass] = 
                $config['sigmasoft_data_table']['entities'][$entityClass];
            
            $yamlContent = Yaml::dump($existingConfig, 4, 2);
        }

        $generator->dumpFile($configPath, $yamlContent);
        $io->text('  ✅ Configuration YAML générée : <info>' . $configPath . '</info>');
    }

    private function generateTwigTemplate(string $entityName, InputInterface $input, Generator $generator, ConsoleStyle $io): string
    {
        $templatePath = trim($input->getOption('template-path'), '/') . '/';
        $templateFile = $templatePath . strtolower($entityName) . '/index.html.twig';

        $generator->generateTemplate(
            $templateFile,
            'datatable/index.twig',
            [
                'entity_name' => $entityName,
                'entity_name_lower' => strtolower($entityName),
                'entity_class_name' => 'App\\Entity\\' . $entityName,
                'page_title' => 'Gestion des ' . $this->pluralize($entityName),
                'with_controller' => $input->getOption('controller'),
                'template_path' => $templatePath
            ]
        );

        $io->text('  ✅ Template Twig généré : <info>templates/' . $templateFile . '</info>');
        
        return $templateFile;
    }

    private function generateController(object $entityClassNameDetails, string $templatePath, Generator $generator, ConsoleStyle $io): void
    {
        $controllerClassNameDetails = $generator->createClassNameDetails(
            $entityClassNameDetails->getShortName() . 'Controller',
            'Controller\\',
            'Controller'
        );

        $generator->generateController(
            $controllerClassNameDetails->getFullName(),
            'datatable/Controller.tpl.php',
            [
                'entity_full_class_name' => $entityClassNameDetails->getFullName(),
                'entity_class_name' => $entityClassNameDetails->getShortName(),
                'entity_var_name' => lcfirst($entityClassNameDetails->getShortName()),
                'entity_identifier' => 'id', // TODO: détecter la vraie clé primaire
                'template_path' => $templatePath,
                'route_name' => strtolower($entityClassNameDetails->getShortName()),
                'route_path' => '/' . strtolower($entityClassNameDetails->getShortName()) . 's'
            ]
        );

        $io->text(sprintf('  ✅ Contrôleur généré : <info>%s</info>', $controllerClassNameDetails->getFullName()));
    }

    private function displayResults(ConsoleStyle $io, string $entityName, string $templatePath, bool $withController): void
    {
        $io->success('🎉 DataTable généré avec succès !');

        $io->section('📋 Fichiers générés');
        $io->listing([
            'Configuration : config/packages/sigmasoft_data_table.yaml',
            'Template : templates/' . $templatePath,
            $withController ? 'Contrôleur : src/Controller/' . $entityName . 'Controller.php' : null
        ]);

        $io->section('🚀 Prochaines étapes');
        $io->text([
            '1. Vérifiez la configuration générée dans <info>config/packages/sigmasoft_data_table.yaml</info>',
            '2. Personnalisez les champs et actions selon vos besoins',
            '3. Ajustez le template si nécessaire',
            $withController ? '4. Configurez les routes dans <info>config/routes.yaml</info> si nécessaire' : null,
            '5. Testez votre DataTable dans le navigateur'
        ]);

        $io->note([
            'Utilisation dans un template Twig :',
            '',
            '<twig:SigmasoftDataTableComponent entityClass="App\\Entity\\' . $entityName . '" />',
            '',
            'Ou accédez directement à /' . strtolower($entityName) . 's si le contrôleur a été généré'
        ]);
    }

    private function pluralize(string $word): string
    {
        // Pluralisation basique en français
        $word = strtolower($word);
        
        if (substr($word, -1) === 's' || substr($word, -1) === 'x' || substr($word, -1) === 'z') {
            return $word;
        }
        
        if (substr($word, -2) === 'al') {
            return substr($word, 0, -2) . 'aux';
        }
        
        if (substr($word, -2) === 'au' || substr($word, -2) === 'eu') {
            return $word . 'x';
        }
        
        return $word . 's';
    }
}