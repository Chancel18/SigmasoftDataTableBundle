<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Event;

/**
 * Contient toutes les constantes d'événements du DataTableBundle
 */
final class DataTableEvents
{
    /**
     * Événement déclenché avant l'exécution de la requête
     * 
     * @Event("Sigmasoft\DataTableBundle\Event\DataTableQueryEvent")
     */
    public const PRE_QUERY = 'sigmasoft.datatable.pre_query';
    
    /**
     * Événement déclenché après l'exécution de la requête
     * 
     * @Event("Sigmasoft\DataTableBundle\Event\DataTableQueryEvent")
     */
    public const POST_QUERY = 'sigmasoft.datatable.post_query';
    
    /**
     * Événement déclenché avant le rendu de la DataTable
     * 
     * @Event("Sigmasoft\DataTableBundle\Event\DataTableRenderEvent")
     */
    public const PRE_RENDER = 'sigmasoft.datatable.pre_render';
    
    /**
     * Événement déclenché après le rendu de la DataTable
     * 
     * @Event("Sigmasoft\DataTableBundle\Event\DataTableRenderEvent")
     */
    public const POST_RENDER = 'sigmasoft.datatable.post_render';
    
    /**
     * Événement déclenché lors d'une édition inline
     * 
     * @Event("Sigmasoft\DataTableBundle\Event\InlineEditEvent")
     */
    public const INLINE_EDIT = 'sigmasoft.datatable.inline_edit';
    
    /**
     * Événement déclenché avant l'édition inline
     * 
     * @Event("Sigmasoft\DataTableBundle\Event\InlineEditEvent")
     */
    public const PRE_INLINE_EDIT = 'sigmasoft.datatable.pre_inline_edit';
    
    /**
     * Événement déclenché après l'édition inline
     * 
     * @Event("Sigmasoft\DataTableBundle\Event\InlineEditEvent")
     */
    public const POST_INLINE_EDIT = 'sigmasoft.datatable.post_inline_edit';
    
    /**
     * Événement déclenché lors d'une action groupée
     * 
     * @Event("Sigmasoft\DataTableBundle\Event\BulkActionEvent")
     */
    public const BULK_ACTION = 'sigmasoft.datatable.bulk_action';
    
    /**
     * Événement déclenché lors de l'export de données
     * 
     * @Event("Sigmasoft\DataTableBundle\Event\ExportEvent")
     */
    public const EXPORT = 'sigmasoft.datatable.export';
    
    private function __construct()
    {
        // Classe non instanciable
    }
}