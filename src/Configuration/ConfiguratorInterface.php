<?php

namespace Sigmasoft\DataTableBundle\Configuration;

use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;

interface ConfiguratorInterface
{
    public function configure(DataTableBuilder $builder): void;
}
