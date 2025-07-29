<?php

declare(strict_types=1);

namespace Sigmasoft\DataTableBundle\Column;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ActionColumn extends AbstractColumn
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        string $name = 'actions',
        string $label = 'Actions',
        array $actions = [],
        array $options = []
    ) {
        parent::__construct($name, 'id', $label, false, false, array_merge($options, ['actions' => $actions]));
    }

    protected function doRender(mixed $value, object $entity): string
    {
        $actions = $this->getOption('actions', []);
        $buttons = [];

        foreach ($actions as $actionName => $actionConfig) {
            $buttons[] = $this->renderAction($actionName, $actionConfig, $entity);
        }

        return sprintf('<div class="d-inline-flex gap-2">%s</div>', implode('', $buttons));
    }

    private function renderAction(string $name, array $config, object $entity): string
    {
        $route = $config['route'] ?? null;
        $routeParams = $config['route_params'] ?? ['id' => $entity->getId()];
        $icon = $config['icon'] ?? '';
        $class = $config['class'] ?? 'btn btn-sm btn-outline-primary';
        $title = $config['title'] ?? ucfirst($name);
        $confirm = $config['confirm'] ?? null;
        $type = $config['type'] ?? 'link';

        if ($type === 'delete') {
            return $this->renderDeleteAction($name, $config, $entity);
        }

        if ($route) {
            $url = $this->urlGenerator->generate($route, $routeParams);
            $confirmAttr = $confirm ? sprintf('onclick="return confirm(\'%s\');"', htmlspecialchars($confirm)) : '';
            
            return sprintf(
                '<a href="%s" class="%s" title="%s" %s><i class="%s"></i></a>',
                $url,
                $class,
                $title,
                $confirmAttr,
                $icon
            );
        }

        return '';
    }

    private function renderDeleteAction(string $name, array $config, object $entity): string
    {
        $icon = $config['icon'] ?? 'bi bi-trash';
        $class = $config['class'] ?? 'btn btn-sm btn-danger';
        $title = $config['title'] ?? 'Supprimer';
        $confirm = $config['confirm'] ?? 'Êtes-vous sûr de vouloir supprimer cet élément ?';

        return sprintf(
            '<button class="%s" title="%s" onclick="if(!confirm(\'%s\')) { event.preventDefault(); event.stopPropagation(); return false; }" data-action="live#action" data-live-action-param="deleteItem" data-live-id-param="%s"><i class="%s"></i></button>',
            $class,
            $title,
            htmlspecialchars($confirm),
            $entity->getId(),
            $icon
        );
    }
}
