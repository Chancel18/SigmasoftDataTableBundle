<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

class ConfigurationManager
{
    public function __construct(
        private readonly array $globalConfig,
        private readonly array $entitiesConfig,
        private readonly array $templates
    ) {}

    public function getEntityConfig(string $entityClass): EntityConfiguration
    {
        $entityConfig = $this->entitiesConfig[$entityClass] ?? [];

        return new EntityConfiguration(
            entityClass: $entityClass,
            config: array_merge($this->globalConfig, $entityConfig),
            templates: $this->templates
        );
    }

    public function getGlobalConfig(): array
    {
        return $this->globalConfig;
    }

    public function getAvailableEntities(): array
    {
        return array_keys($this->entitiesConfig);
    }

    public function hasEntityConfig(string $entityClass): bool
    {
        return isset($this->entitiesConfig[$entityClass]);
    }
}
