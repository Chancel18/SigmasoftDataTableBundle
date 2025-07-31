<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Service;

use Sigmasoft\DataTableBundle\Configuration\DataTableConfiguration;

class DataTableRegistry implements DataTableRegistryInterface
{
    private array $configurations = [];

    public function register(string $id, DataTableConfiguration $configuration): void
    {
        $this->configurations[$id] = $configuration;
    }

    public function get(string $id): DataTableConfiguration
    {
        if (!$this->has($id)) {
            throw \Sigmasoft\DataTableBundle\Exception\DataTableException::configurationNotFound($id);
        }
        
        return $this->configurations[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->configurations[$id]);
    }

    public function unregister(string $id): void
    {
        unset($this->configurations[$id]);
    }

    public function getAll(): array
    {
        return $this->configurations;
    }

    public function clear(): void
    {
        $this->configurations = [];
    }

    public function generateId(): string
    {
        return md5(uniqid('dt_', true));
    }
}
