<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Sigmasoft\DataTableBundle\DependencyInjection\SigmasoftDataTableExtension;

class SigmasoftDataTableBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function getContainerExtension(): SigmasoftDataTableExtension
    {
        if (null === $this->extension) {
            $this->extension = new SigmasoftDataTableExtension();
        }

        return $this->extension;
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
