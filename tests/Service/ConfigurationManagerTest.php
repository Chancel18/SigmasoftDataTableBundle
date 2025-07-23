<?php
declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Sigmasoft\DataTableBundle\Service\ConfigurationManager;
use Sigmasoft\DataTableBundle\Service\EntityConfiguration;

class ConfigurationManagerTest extends TestCase
{
    private ConfigurationManager $configManager;
    private array $globalConfig;
    private array $entitiesConfig;
    private array $templates;

    protected function setUp(): void
    {
        $this->globalConfig = [
            'items_per_page' => 25,
            'enable_search' => true,
            'enable_sorting' => true,
            'enable_filtering' => true,
            'enable_export' => false,
            'cache_ttl' => 300,
        ];

        $this->entitiesConfig = [
            'App\\Entity\\User' => [
                'fields' => [
                    'id' => ['type' => 'integer', 'sortable' => true, 'searchable' => false],
                    'name' => ['type' => 'string', 'searchable' => true, 'sortable' => true],
                    'email' => ['type' => 'email', 'searchable' => true],
                    'createdAt' => ['type' => 'datetime', 'sortable' => true, 'searchable' => false],
                ],
                'items_per_page' => 20,
                'enable_export' => true,
                'export' => [
                    'formats' => ['csv', 'xlsx']
                ],
            ],
            'App\\Entity\\Product' => [
                'fields' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string', 'searchable' => true],
                    'price' => ['type' => 'currency', 'sortable' => true],
                ],
                'items_per_page' => 50,
            ],
        ];

        $this->templates = [
            'table' => '@SigmasoftDataTable/table.html.twig',
            'pagination' => '@SigmasoftDataTable/pagination.html.twig',
        ];

        $this->configManager = new ConfigurationManager(
            $this->globalConfig,
            $this->entitiesConfig,
            $this->templates
        );
    }

    public function testGetEntityConfigForExistingEntity(): void
    {
        $config = $this->configManager->getEntityConfig('App\\Entity\\User');

        $this->assertInstanceOf(EntityConfiguration::class, $config);
        $this->assertSame('App\\Entity\\User', $config->getEntityClass());
        $this->assertSame(20, $config->getItemsPerPage());
        $this->assertTrue($config->isExportEnabled());
        $exportConfig = $config->getExportConfig();
        $this->assertSame(['csv', 'xlsx'], $exportConfig['formats'] ?? []);
    }

    public function testGetEntityConfigForNonExistingEntity(): void
    {
        $config = $this->configManager->getEntityConfig('App\\Entity\\NonExistent');

        $this->assertInstanceOf(EntityConfiguration::class, $config);
        $this->assertSame('App\\Entity\\NonExistent', $config->getEntityClass());
        // Doit utiliser les valeurs par défaut du globalConfig
        $this->assertSame(25, $config->getItemsPerPage());
        $this->assertFalse($config->isExportEnabled());
    }

    public function testGetEntityConfigWithFieldConfiguration(): void
    {
        $config = $this->configManager->getEntityConfig('App\\Entity\\User');
        $fields = $config->getFields();

        $this->assertArrayHasKey('id', $fields);
        $this->assertArrayHasKey('name', $fields);
        $this->assertArrayHasKey('email', $fields);
        $this->assertArrayHasKey('createdAt', $fields);

        // Test configuration du champ name
        $nameField = $fields['name'];
        $this->assertSame('string', $nameField['type']);
        $this->assertTrue($nameField['searchable']);
        $this->assertTrue($nameField['sortable']);

        // Test configuration du champ email
        $emailField = $fields['email'];
        $this->assertSame('email', $emailField['type']);
        $this->assertTrue($emailField['searchable']);
        $this->assertArrayNotHasKey('sortable', $emailField);
    }

    public function testGetEntityConfigMergesWithGlobalConfig(): void
    {
        $config = $this->configManager->getEntityConfig('App\\Entity\\Product');

        // La configuration d'entité spécifique doit surcharger la globale
        $this->assertSame(50, $config->getItemsPerPage()); // Spécifique à Product

        // Les valeurs non définies dans l'entité doivent utiliser la configuration globale
        $this->assertTrue($config->isSearchEnabled()); // Depuis globalConfig
        $this->assertTrue($config->isSortEnabled()); // Depuis globalConfig
        $this->assertFalse($config->isExportEnabled()); // Depuis globalConfig
    }

    public function testGetGlobalConfig(): void
    {
        $config = $this->configManager->getGlobalConfig();

        $this->assertSame($this->globalConfig, $config);
        $this->assertSame(25, $config['items_per_page']);
        $this->assertTrue($config['enable_search']);
        $this->assertSame(300, $config['cache_ttl']);
    }

    public function testGetEntityConfigTemplates(): void
    {
        $config = $this->configManager->getEntityConfig('App\\Entity\\User');
        $templates = $config->getTemplates();

        $this->assertSame($this->templates, $templates);
        $this->assertSame('@SigmasoftDataTable/table.html.twig', $templates['table']);
        $this->assertSame('@SigmasoftDataTable/pagination.html.twig', $templates['pagination']);
    }

    public function testGetTemplate(): void
    {
        $config = $this->configManager->getEntityConfig('App\\Entity\\User');
        $tableTemplate = $config->getTemplate('table');
        $paginationTemplate = $config->getTemplate('pagination');
        $nonExistentTemplate = $config->getTemplate('nonexistent');

        $this->assertSame('@SigmasoftDataTable/table.html.twig', $tableTemplate);
        $this->assertSame('@SigmasoftDataTable/pagination.html.twig', $paginationTemplate);
        $this->assertStringContainsString('nonexistent', $nonExistentTemplate);
    }

    public function testHasEntityConfig(): void
    {
        $this->assertTrue($this->configManager->hasEntityConfig('App\\Entity\\User'));
        $this->assertTrue($this->configManager->hasEntityConfig('App\\Entity\\Product'));
        $this->assertFalse($this->configManager->hasEntityConfig('App\\Entity\\NonExistent'));
    }

    public function testGetAvailableEntities(): void
    {
        $availableEntities = $this->configManager->getAvailableEntities();

        $this->assertSame(['App\\Entity\\User', 'App\\Entity\\Product'], $availableEntities);
        $this->assertContains('App\\Entity\\User', $availableEntities);
        $this->assertContains('App\\Entity\\Product', $availableEntities);
    }

    public function testEmptyConfigurationManager(): void
    {
        $emptyConfigManager = new ConfigurationManager([], [], []);

        $config = $emptyConfigManager->getEntityConfig('App\\Entity\\Test');
        $this->assertInstanceOf(EntityConfiguration::class, $config);

        $globalConfig = $emptyConfigManager->getGlobalConfig();
        $this->assertSame([], $globalConfig);

        $config = $emptyConfigManager->getEntityConfig('App\\Entity\\Test');
        $templates = $config->getTemplates();
        $this->assertSame([], $templates);
    }

    public function testEntityConfigurationInheritance(): void
    {
        // Test qu'une entité hérite correctement de la configuration globale
        $configWithPartialEntity = new ConfigurationManager(
            [
                'items_per_page' => 30,
                'enable_search' => true,
                'enable_export' => false,
            ],
            [
                'App\\Entity\\PartialConfig' => [
                    'items_per_page' => 15, // Override seulement cette valeur
                ],
            ],
            []
        );

        $config = $configWithPartialEntity->getEntityConfig('App\\Entity\\PartialConfig');

        $this->assertSame(15, $config->getItemsPerPage()); // Overridé
        $this->assertTrue($config->isSearchEnabled()); // Hérité de global
        $this->assertFalse($config->isExportEnabled()); // Hérité de global
    }

    public function testComplexFieldConfiguration(): void
    {
        $complexConfig = new ConfigurationManager(
            ['items_per_page' => 10],
            [
                'App\\Entity\\Complex' => [
                    'fields' => [
                        'status' => [
                            'type' => 'choice',
                            'choices' => ['active', 'inactive', 'pending'],
                            'searchable' => true,
                            'filterable' => true,
                        ],
                        'metadata' => [
                            'type' => 'json',
                            'searchable' => false,
                            'format' => ['pretty_print' => true],
                        ],
                        'tags' => [
                            'type' => 'array',
                            'separator' => ', ',
                            'searchable' => true,
                        ],
                    ],
                ],
            ],
            []
        );

        $config = $complexConfig->getEntityConfig('App\\Entity\\Complex');
        $fields = $config->getFields();

        // Test champ status
        $statusField = $fields['status'];
        $this->assertSame('choice', $statusField['type']);
        $this->assertSame(['active', 'inactive', 'pending'], $statusField['choices']);
        $this->assertTrue($statusField['searchable']);
        $this->assertTrue($statusField['filterable']);

        // Test champ metadata
        $metadataField = $fields['metadata'];
        $this->assertSame('json', $metadataField['type']);
        $this->assertFalse($metadataField['searchable']);
        $this->assertTrue($metadataField['format']['pretty_print']);

        // Test champ tags
        $tagsField = $fields['tags'];
        $this->assertSame('array', $tagsField['type']);
        $this->assertSame(', ', $tagsField['separator']);
        $this->assertTrue($tagsField['searchable']);
    }

    public function testGetSearchableFields(): void
    {
        $config = $this->configManager->getEntityConfig('App\\Entity\\User');
        $searchableFields = $config->getSearchFields();

        $this->assertContains('name', $searchableFields);
        $this->assertContains('email', $searchableFields);
        $this->assertNotContains('id', $searchableFields);
        $this->assertNotContains('createdAt', $searchableFields);
    }

    public function testGetSortableFields(): void
    {
        $config = $this->configManager->getEntityConfig('App\\Entity\\User');
        $sortableFields = $config->getSortableFields();

        $this->assertContains('id', $sortableFields);
        $this->assertContains('name', $sortableFields);
        $this->assertContains('createdAt', $sortableFields);
        $this->assertNotContains('email', $sortableFields);
    }

    public function testConfigurationValidation(): void
    {
        // Test avec une configuration invalide pour s'assurer que ça ne plante pas
        $invalidConfig = new ConfigurationManager(
            ['items_per_page' => 'invalid'], // Type invalide
            [
                'App\\Entity\\Invalid' => [
                    'fields' => 'not_an_array', // Configuration invalide
                ],
            ],
            []
        );

        // Devrait créer l'EntityConfiguration sans erreur
        $config = $invalidConfig->getEntityConfig('App\\Entity\\Invalid');
        $this->assertInstanceOf(EntityConfiguration::class, $config);
    }
}