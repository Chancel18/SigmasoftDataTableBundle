<?php

namespace Sigmasoft\DataTableBundle\Configuration\DataTableConfig;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Column\DateColumn;
use Sigmasoft\DataTableBundle\Column\BadgeColumn;
use Sigmasoft\DataTableBundle\Configuration\AbstractDataTableConfiguration;

class UserDataTableConfig extends AbstractDataTableConfiguration
{
    public function getEntityClass(): string
    {
        return User::class;
    }
    
    public function configure(): void
    {
        $this->addColumn(new TextColumn('id', 'id', 'ID'));
        $this->addColumn(new TextColumn('firstName', 'firstName', 'Prénom'));
        $this->addColumn(new TextColumn('lastName', 'lastName', 'Nom'));
        $this->addColumn(new TextColumn('email', 'email', 'Email'));
        $this->addColumn(new DateColumn('createdAt', 'createdAt', 'Créé le'));
        $this->addColumn(new BadgeColumn('isActive', 'isActive', 'Statut', true, false, [
            'value_mapping' => [
                true => 'Actif',
                false => 'Inactif'
            ],
            'badge_class' => 'bg-success'
        ]));
        
        $this->setSearchEnabled(true);
        $this->setSearchableFields(['firstName', 'lastName', 'email']);
        $this->setPaginationEnabled(true);
        $this->setItemsPerPage(10);
        $this->setItemsPerPageOptions([10, 25, 50, 100]);
        $this->setSortField('lastName');
        $this->setSortDirection('asc');
        $this->setExportEnabled(true);
        $this->setExportFormats(['csv', 'excel']);
    }
}
