---
sidebar_position: 1
---

# Exemples Avancés

Collection d'exemples pratiques et complets pour exploiter toute la puissance du **SigmasoftDataTableBundle**.

## 🛍️ E-commerce - Gestion des Produits

### Entité Product

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
class Product
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $price;

    #[ORM\Column]
    private int $stock = 0;

    #[ORM\Column(length: 50)]
    private string $status = 'draft';

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $color = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    private ?Category $category = null;

    #[ORM\ManyToMany(targetEntity: Tag::class)]
    private Collection $tags;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters and setters...
}
```

### Contrôleur avec DataTable Complet

```php
<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Column\ActionColumn;
use Sigmasoft\DataTableBundle\Column\BadgeColumn;
use Sigmasoft\DataTableBundle\Service\EditableColumnFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductController extends AbstractController
{
    public function __construct(
        private DataTableBuilder $builder,
        private EditableColumnFactory $editableFactory,
        private UrlGeneratorInterface $urlGenerator,
        private CategoryRepository $categoryRepository
    ) {}

    #[Route('/admin/products', name: 'admin_products')]
    public function index(): Response
    {
        $config = $this->builder
            ->createDataTable(Product::class)
            
            // Nom modifiable avec validation
            ->addColumn(
                $this->editableFactory->text('name', 'name', 'Nom du Produit')
                    ->required(true)
                    ->minLength(3)
                    ->maxLength(100)
                    ->placeholder('Nom du produit...')
            )
            
            // Description avec textarea
            ->addColumn(
                $this->editableFactory->textarea('description', 'description', 'Description')
                    ->maxLength(500)
                    ->rows(3)
                    ->placeholder('Décrivez le produit...')
            )
            
            // Prix avec validation numérique
            ->addColumn(
                $this->editableFactory->number('price', 'price', 'Prix')
                    ->required(true)
                    ->min(0.01)
                    ->max(9999.99)
                    ->step(0.01)
                    ->suffix(' €')
            )
            
            // Stock avec alerte visuelle
            ->addColumn(
                $this->editableFactory->number('stock', 'stock', 'Stock')
                    ->required(true)
                    ->min(0)
                    ->step(1)
                    ->cssClass(function($value) {
                        if ($value <= 0) return 'text-danger fw-bold';
                        if ($value <= 10) return 'text-warning fw-bold';
                        return 'text-success';
                    })
            )
            
            // Statut avec select
            ->addColumn(
                $this->editableFactory->select('status', 'status', 'Statut', [
                    'draft' => 'Brouillon',
                    'active' => 'Actif',
                    'inactive' => 'Inactif',
                    'out_of_stock' => 'Rupture'
                ])->required(true)
            )
            
            // Catégorie avec données dynamiques
            ->addColumn(
                $this->editableFactory->select('categoryId', 'category.id', 'Catégorie', 
                    $this->getCategoryOptions()
                )
            )
            
            // Couleur avec sélecteur
            ->addColumn(
                $this->editableFactory->color('color', 'color', 'Couleur')
                    ->showPresets(true)
                    ->defaultValue('#3498db')
            )
            
            // Badge statut (lecture seule)
            ->addColumn(new BadgeColumn('status', 'status', 'État', false, false, [
                'value_mapping' => [
                    'draft' => 'Brouillon',
                    'active' => 'Actif',
                    'inactive' => 'Inactif',
                    'out_of_stock' => 'Rupture'
                ],
                'class_mapping' => [
                    'draft' => 'bg-secondary',
                    'active' => 'bg-success',
                    'inactive' => 'bg-danger',
                    'out_of_stock' => 'bg-warning text-dark'
                ]
            ]))
            
            // Tags multiples
            ->addColumn(new BadgeColumn('tags', 'tags', 'Tags', false, true, [
                'multiple' => true,
                'property_path' => 'name',
                'badge_class' => 'bg-info text-white',
                'max_items' => 3,
                'show_count' => true,
                'empty_value' => '<span class="text-muted">Aucun tag</span>',
                'escape' => false
            ]))
            
            // Dates
            ->addDateColumn('createdAt', 'createdAt', 'Créé le', true, false, [
                'format' => 'd/m/Y H:i'
            ])
            ->addDateColumn('updatedAt', 'updatedAt', 'Modifié le', true, false, [
                'format' => 'd/m/Y H:i',
                'empty_value' => '<span class="text-muted">Jamais</span>',
                'escape' => false
            ])
            
            // Actions avec permissions
            ->addColumn(new ActionColumn($this->urlGenerator, 'actions', 'Actions', 
                $this->getProductActions()
            ))
            
            // Configuration du tableau
            ->configureSearch(true, ['name', 'description'])
            ->configurePagination(true, 25)
            ->configureSorting(true);

        return $this->render('admin/products/index.html.twig', [
            'datatableConfig' => $config,
        ]);
    }

    private function getCategoryOptions(): array
    {
        $categories = $this->categoryRepository->findBy([], ['name' => 'ASC']);
        $options = ['' => 'Aucune catégorie'];
        
        foreach ($categories as $category) {
            $options[$category->getId()] = $category->getName();
        }
        
        return $options;
    }

    private function getProductActions(): array
    {
        $actions = [
            'view' => [
                'route' => 'admin_product_show',
                'icon' => 'bi bi-eye',
                'class' => 'btn btn-sm btn-outline-primary',
                'title' => 'Voir'
            ]
        ];

        if ($this->isGranted('ROLE_EDITOR')) {
            $actions['edit'] = [
                'route' => 'admin_product_edit',
                'icon' => 'bi bi-pencil',
                'class' => 'btn btn-sm btn-primary',
                'title' => 'Modifier'
            ];
            
            $actions['duplicate'] = [
                'route' => 'admin_product_duplicate',
                'icon' => 'bi bi-files',
                'class' => 'btn btn-sm btn-secondary',
                'title' => 'Dupliquer',
                'method' => 'POST'
            ];
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $actions['delete'] = [
                'route' => 'admin_product_delete',
                'icon' => 'bi bi-trash',
                'class' => 'btn btn-sm btn-danger',
                'title' => 'Supprimer',
                'confirm' => 'Êtes-vous sûr de vouloir supprimer ce produit ?',
                'condition' => fn(Product $p) => $p->canBeDeleted()
            ];
        }

        return $actions;
    }
}
```

---

## 👥 CRM - Gestion des Utilisateurs

### Entité User Avancée

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class User implements UserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $firstName;

    #[ORM\Column(length: 50)]
    private string $lastName;

    #[ORM\Column(length: 100, unique: true)]
    private string $email;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 100)]
    private string $department;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $salary = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(length: 20)]
    private string $status = 'active';

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeInterface $lastLoginAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    // Getters and setters...
}
```

### DataTable CRM Complet

```php
<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Column\ActionColumn;
use Sigmasoft\DataTableBundle\Column\BadgeColumn;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Service\EditableColumnFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private DataTableBuilder $builder,
        private EditableColumnFactory $editableFactory
    ) {}

    #[Route('/admin/users', name: 'admin_users')]
    public function index(): Response
    {
        $config = $this->builder
            ->createDataTable(User::class)
            
            // Photo de profil (non éditable)
            ->addColumn(new TextColumn('avatar', 'id', 'Photo', false, false, [
                'callback' => function($id, User $user) {
                    $initials = strtoupper(substr($user->getFirstName(), 0, 1) . substr($user->getLastName(), 0, 1));
                    return sprintf(
                        '<div class="avatar avatar-sm"><span class="avatar-initial rounded-circle bg-primary text-white">%s</span></div>',
                        $initials
                    );
                },
                'escape' => false
            ]))
            
            // Informations personnelles éditables
            ->addColumn(
                $this->editableFactory->text('firstName', 'firstName', 'Prénom')
                    ->required(true)
                    ->minLength(2)
                    ->maxLength(50)
                    ->pattern('[A-Za-zÀ-ÿ\s\-\']+')
            )
            
            ->addColumn(
                $this->editableFactory->text('lastName', 'lastName', 'Nom')
                    ->required(true)
                    ->minLength(2)
                    ->maxLength(50)
                    ->pattern('[A-Za-zÀ-ÿ\s\-\']+')
            )
            
            ->addColumn(
                $this->editableFactory->email('email', 'email', 'Email')
                    ->required(true)
                    ->placeholder('utilisateur@exemple.com')
            )
            
            ->addColumn(
                $this->editableFactory->text('phone', 'phone', 'Téléphone')
                    ->pattern('[0-9\s\-\+\(\)]+')
                    ->placeholder('+33 1 23 45 67 89')
                    ->maxLength(20)
            )
            
            // Informations professionnelles
            ->addColumn(
                $this->editableFactory->select('department', 'department', 'Département', [
                    'IT' => 'Informatique',
                    'HR' => 'Ressources Humaines',
                    'SALES' => 'Commercial',
                    'MARKETING' => 'Marketing',
                    'FINANCE' => 'Finance',
                    'OPERATIONS' => 'Opérations'
                ])->required(true)
            )
            
            ->addColumn(
                $this->editableFactory->number('salary', 'salary', 'Salaire')
                    ->min(0)
                    ->max(500000)
                    ->step(100)
                    ->prefix('€ ')
                    ->placeholder('0')
                    ->cssClass('text-end')
            )
            
            // Rôles avec badges
            ->addColumn(new BadgeColumn('roles', 'roles', 'Rôles', false, false, [
                'multiple' => true,
                'value_mapping' => [
                    'ROLE_USER' => 'Utilisateur',
                    'ROLE_ADMIN' => 'Admin',
                    'ROLE_EDITOR' => 'Éditeur',
                    'ROLE_MANAGER' => 'Manager'
                ],
                'class_mapping' => [
                    'ROLE_USER' => 'bg-primary',
                    'ROLE_ADMIN' => 'bg-danger',
                    'ROLE_EDITOR' => 'bg-success',
                    'ROLE_MANAGER' => 'bg-warning text-dark'
                ]
            ]))
            
            // Statut avec badge
            ->addColumn(new BadgeColumn('status', 'status', 'Statut', false, false, [
                'value_mapping' => [
                    'active' => 'Actif',
                    'inactive' => 'Inactif',
                    'suspended' => 'Suspendu',
                    'pending' => 'En attente'
                ],
                'class_mapping' => [
                    'active' => 'bg-success',
                    'inactive' => 'bg-secondary',
                    'suspended' => 'bg-danger',
                    'pending' => 'bg-warning text-dark'
                ]
            ]))
            
            // Vérification email
            ->addColumn(new TextColumn('verified', 'isVerified', 'Vérifié', false, false, [
                'callback' => function($isVerified) {
                    return $isVerified 
                        ? '<i class="bi bi-check-circle-fill text-success" title="Email vérifié"></i>'
                        : '<i class="bi bi-x-circle-fill text-danger" title="Email non vérifié"></i>';
                },
                'escape' => false,
                'css_class' => 'text-center'
            ]))
            
            // Notes éditables
            ->addColumn(
                $this->editableFactory->textarea('notes', 'notes', 'Notes')
                    ->maxLength(1000)
                    ->rows(2)
                    ->placeholder('Notes sur l\'utilisateur...')
            )
            
            // Dernière connexion
            ->addDateColumn('lastLoginAt', 'lastLoginAt', 'Dernière Connexion', true, false, [
                'format' => 'd/m/Y H:i',
                'empty_value' => '<span class="text-muted">Jamais</span>',
                'escape' => false,
                'callback' => function($date) {
                    if (!$date) return '<span class="text-muted">Jamais</span>';
                    
                    $now = new \DateTime();
                    $diff = $now->diff($date);
                    
                    if ($diff->days == 0) {
                        return '<span class="text-success">Aujourd\'hui à ' . $date->format('H:i') . '</span>';
                    } elseif ($diff->days == 1) {
                        return '<span class="text-warning">Hier à ' . $date->format('H:i') . '</span>';
                    } elseif ($diff->days <= 7) {
                        return '<span class="text-info">Il y a ' . $diff->days . ' jours</span>';
                    } else {
                        return '<span class="text-muted">' . $date->format('d/m/Y') . '</span>';
                    }
                }
            ]))
            
            // Date de création
            ->addDateColumn('createdAt', 'createdAt', 'Créé le', true, false, [
                'format' => 'd/m/Y'
            ])
            
            // Actions contextuelles
            ->addColumn(new ActionColumn($this->urlGenerator, 'actions', 'Actions', [
                'profile' => [
                    'route' => 'admin_user_profile',
                    'icon' => 'bi bi-person-circle',
                    'class' => 'btn btn-sm btn-outline-info',
                    'title' => 'Profil'
                ],
                'edit' => [
                    'route' => 'admin_user_edit',
                    'icon' => 'bi bi-pencil',
                    'class' => 'btn btn-sm btn-primary',
                    'title' => 'Modifier',
                    'condition' => fn(User $user) => $this->isGranted('EDIT', $user)
                ],
                'impersonate' => [
                    'route' => 'admin_user_impersonate',
                    'icon' => 'bi bi-person-gear',
                    'class' => 'btn btn-sm btn-warning',
                    'title' => 'Se connecter en tant que',
                    'condition' => fn(User $user) => $this->isGranted('ROLE_ADMIN') && $user !== $this->getUser()
                ],
                'suspend' => [
                    'route' => 'admin_user_suspend',
                    'icon' => 'bi bi-pause-circle',
                    'class' => 'btn btn-sm btn-danger',
                    'title' => 'Suspendre',
                    'confirm' => 'Suspendre cet utilisateur ?',
                    'condition' => fn(User $user) => $user->getStatus() !== 'suspended' && $this->isGranted('ROLE_ADMIN')
                ]
            ]))
            
            // Configuration avancée
            ->configureSearch(true, ['firstName', 'lastName', 'email', 'department'])
            ->configurePagination(true, 50)
            ->configureSorting(true)
            ->configureExport(true, ['csv', 'excel'])
            ->configureFilters([
                'department' => [
                    'type' => 'select',
                    'options' => [
                        '' => 'Tous les départements',
                        'IT' => 'Informatique',
                        'HR' => 'RH',
                        'SALES' => 'Commercial'
                    ]
                ],
                'status' => [
                    'type' => 'select',
                    'options' => [
                        '' => 'Tous les statuts',
                        'active' => 'Actif',
                        'inactive' => 'Inactif'
                    ]
                ]
            ]);

        return $this->render('admin/users/index.html.twig', [
            'datatableConfig' => $config,
        ]);
    }
}
```

---

## 📊 Dashboard Analytics

### Entité Order pour Suivi des Commandes

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Order
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private string $orderNumber;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $customer;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $totalAmount;

    #[ORM\Column(length: 20)]
    private string $status = 'pending';

    #[ORM\Column(length: 50)]
    private string $paymentMethod;

    #[ORM\Column]
    private \DateTimeImmutable $orderDate;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $shippedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deliveredAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    // Getters and setters...
}
```

### DataTable Dashboard Avancé

```php
<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use Sigmasoft\DataTableBundle\Builder\DataTableBuilder;
use Sigmasoft\DataTableBundle\Column\ActionColumn;
use Sigmasoft\DataTableBundle\Column\BadgeColumn;
use Sigmasoft\DataTableBundle\Column\TextColumn;
use Sigmasoft\DataTableBundle\Service\EditableColumnFactory;

class OrderDashboardController extends AbstractController
{
    #[Route('/admin/orders', name: 'admin_orders')]
    public function index(): Response
    {
        $config = $this->builder
            ->createDataTable(Order::class)
            
            // Numéro de commande avec lien
            ->addColumn(new TextColumn('orderNumber', 'orderNumber', 'N° Commande', true, true, [
                'callback' => function($orderNumber, Order $order) {
                    $url = $this->generateUrl('admin_order_show', ['id' => $order->getId()]);
                    return sprintf('<a href="%s" class="fw-bold text-decoration-none">%s</a>', $url, $orderNumber);
                },
                'escape' => false
            ]))
            
            // Client avec informations enrichies
            ->addColumn(new TextColumn('customer', 'customer.fullName', 'Client', true, true, [
                'callback' => function($fullName, Order $order) {
                    $customer = $order->getCustomer();
                    return sprintf(
                        '<div class="d-flex align-items-center">
                            <div class="avatar avatar-xs me-2">
                                <span class="avatar-initial rounded-circle bg-info text-white">%s</span>
                            </div>
                            <div>
                                <div class="fw-semibold">%s</div>
                                <small class="text-muted">%s</small>
                            </div>
                        </div>',
                        strtoupper(substr($customer->getFirstName(), 0, 1) . substr($customer->getLastName(), 0, 1)),
                        $fullName,
                        $customer->getEmail()
                    );
                },
                'escape' => false
            ]))
            
            // Montant avec formatage et indicateurs
            ->addColumn(new TextColumn('totalAmount', 'totalAmount', 'Montant', true, false, [
                'callback' => function($amount, Order $order) {
                    $formatted = number_format($amount, 2, ',', ' ') . ' €';
                    $class = 'text-success';
                    
                    if ($amount > 1000) {
                        $class = 'text-success fw-bold';
                        $formatted .= ' <i class="bi bi-star-fill text-warning ms-1" title="Commande importante"></i>';
                    } elseif ($amount < 50) {
                        $class = 'text-muted';
                    }
                    
                    return sprintf('<span class="%s">%s</span>', $class, $formatted);
                },
                'escape' => false,
                'css_class' => 'text-end'
            ]))
            
            // Statut avec progression visuelle
            ->addColumn(new BadgeColumn('status', 'status', 'Statut', false, false, [
                'value_mapping' => [
                    'pending' => 'En attente',
                    'paid' => 'Payée',
                    'processing' => 'En traitement',
                    'shipped' => 'Expédiée',
                    'delivered' => 'Livrée',
                    'cancelled' => 'Annulée',
                    'refunded' => 'Remboursée'
                ],
                'class_mapping' => [
                    'pending' => 'bg-warning text-dark',
                    'paid' => 'bg-info text-white',
                    'processing' => 'bg-primary text-white',
                    'shipped' => 'bg-secondary text-white',
                    'delivered' => 'bg-success text-white',
                    'cancelled' => 'bg-danger text-white',
                    'refunded' => 'bg-dark text-white'
                ],
                'callback' => function($status, Order $order) {
                    $statusConfig = [
                        'pending' => ['progress' => 10, 'icon' => 'clock'],
                        'paid' => ['progress' => 25, 'icon' => 'credit-card'],
                        'processing' => ['progress' => 50, 'icon' => 'gear'],
                        'shipped' => ['progress' => 75, 'icon' => 'truck'],
                        'delivered' => ['progress' => 100, 'icon' => 'check-circle'],
                        'cancelled' => ['progress' => 0, 'icon' => 'x-circle'],
                        'refunded' => ['progress' => 0, 'icon' => 'arrow-clockwise']
                    ];
                    
                    $config = $statusConfig[$status] ?? ['progress' => 0, 'icon' => 'question'];
                    $badgeClass = $this->getOption('class_mapping')[$status] ?? 'bg-secondary';
                    $label = $this->getOption('value_mapping')[$status] ?? $status;
                    
                    return sprintf(
                        '<div class="d-flex align-items-center">
                            <span class="badge %s me-2">
                                <i class="bi bi-%s me-1"></i>%s
                            </span>
                            <div class="progress flex-grow-1" style="width: 60px; height: 4px;">
                                <div class="progress-bar bg-success" style="width: %d%%"></div>
                            </div>
                        </div>',
                        $badgeClass,
                        $config['icon'],
                        $label,
                        $config['progress']
                    );
                }
            ]))
            
            // Méthode de paiement avec icônes
            ->addColumn(new TextColumn('paymentMethod', 'paymentMethod', 'Paiement', false, false, [
                'callback' => function($method) {
                    $icons = [
                        'credit_card' => 'credit-card',
                        'paypal' => 'paypal',
                        'bank_transfer' => 'bank',
                        'cash' => 'cash-coin',
                        'check' => 'receipt'
                    ];
                    
                    $labels = [
                        'credit_card' => 'Carte Bancaire',
                        'paypal' => 'PayPal',
                        'bank_transfer' => 'Virement',
                        'cash' => 'Espèces',
                        'check' => 'Chèque'
                    ];
                    
                    $icon = $icons[$method] ?? 'question-circle';
                    $label = $labels[$method] ?? $method;
                    
                    return sprintf(
                        '<i class="bi bi-%s me-1"></i>%s',
                        $icon,
                        $label
                    );
                },
                'escape' => false
            ]))
            
            // Timeline des dates importantes
            ->addColumn(new TextColumn('timeline', 'orderDate', 'Timeline', false, false, [
                'callback' => function($orderDate, Order $order) {
                    $timeline = [];
                    
                    // Date de commande
                    $timeline[] = sprintf(
                        '<small class="text-muted d-block">
                            <i class="bi bi-cart-plus me-1"></i>Commandé: %s
                        </small>',
                        $orderDate->format('d/m/Y H:i')
                    );
                    
                    // Date d'expédition
                    if ($order->getShippedAt()) {
                        $timeline[] = sprintf(
                            '<small class="text-info d-block">
                                <i class="bi bi-truck me-1"></i>Expédié: %s
                            </small>',
                            $order->getShippedAt()->format('d/m/Y H:i')
                        );
                    }
                    
                    // Date de livraison
                    if ($order->getDeliveredAt()) {
                        $timeline[] = sprintf(
                            '<small class="text-success d-block">
                                <i class="bi bi-check-circle me-1"></i>Livré: %s
                            </small>',
                            $order->getDeliveredAt()->format('d/m/Y H:i')
                        );
                    }
                    
                    return implode('', $timeline);
                },
                'escape' => false
            ]))
            
            // Notes éditables avec preview
            ->addColumn(
                $this->editableFactory->textarea('notes', 'notes', 'Notes')
                    ->maxLength(500)
                    ->rows(2)
                    ->placeholder('Notes sur la commande...')
                    ->previewCallback(function($notes) {
                        if (!$notes) return '<em class="text-muted">Aucune note</em>';
                        
                        $preview = strlen($notes) > 50 ? substr($notes, 0, 50) . '...' : $notes;
                        return '<span title="' . htmlspecialchars($notes) . '">' . htmlspecialchars($preview) . '</span>';
                    })
            )
            
            // Actions contextuelles avancées
            ->addColumn(new ActionColumn($this->urlGenerator, 'actions', 'Actions', [
                'view' => [
                    'route' => 'admin_order_show',
                    'icon' => 'bi bi-eye',
                    'class' => 'btn btn-sm btn-outline-primary',
                    'title' => 'Détails'
                ],
                'invoice' => [
                    'route' => 'admin_order_invoice',
                    'icon' => 'bi bi-file-earmark-pdf',
                    'class' => 'btn btn-sm btn-success',
                    'title' => 'Facture PDF',
                    'target' => '_blank'
                ],
                'ship' => [
                    'route' => 'admin_order_ship',
                    'icon' => 'bi bi-truck',
                    'class' => 'btn btn-sm btn-info',
                    'title' => 'Expédier',
                    'condition' => fn(Order $o) => in_array($o->getStatus(), ['paid', 'processing']),
                    'ajax' => true,
                    'reload_datatable' => true
                ],
                'cancel' => [
                    'route' => 'admin_order_cancel',
                    'icon' => 'bi bi-x-circle',
                    'class' => 'btn btn-sm btn-danger',
                    'title' => 'Annuler',
                    'confirm' => 'Annuler cette commande ?',
                    'condition' => fn(Order $o) => !in_array($o->getStatus(), ['delivered', 'cancelled', 'refunded'])
                ]
            ]))
            
            // Configuration avancée avec filtres
            ->configureSearch(true, ['orderNumber', 'customer.firstName', 'customer.lastName', 'customer.email'])
            ->configurePagination(true, 25)
            ->configureSorting(true)
            ->configureFilters([
                'status' => [
                    'type' => 'select',
                    'options' => [
                        '' => 'Tous les statuts',
                        'pending' => 'En attente',
                        'paid' => 'Payées',
                        'shipped' => 'Expédiées',
                        'delivered' => 'Livrées'
                    ]
                ],
                'amount_range' => [
                    'type' => 'range',
                    'min' => 0,
                    'max' => 5000,
                    'step' => 50,
                    'suffix' => '€'
                ],
                'date_range' => [
                    'type' => 'daterange',
                    'field' => 'orderDate'
                ]
            ])
            ->configureExport(true, ['csv', 'excel'])
            ->configureRealTimeUpdates(true, 30); // Actualisation toutes les 30 secondes

        return $this->render('admin/orders/dashboard.html.twig', [
            'datatableConfig' => $config,
        ]);
    }
}
```

---

## 🎛️ Configuration Avancée

### Service Personnalisé pour Logique Métier

```php
<?php

namespace App\Service;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

class OrderDatatableService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function getStatusProgressConfig(): array
    {
        return [
            'pending' => ['progress' => 10, 'color' => 'warning', 'icon' => 'clock'],
            'paid' => ['progress' => 25, 'color' => 'info', 'icon' => 'credit-card'],
            'processing' => ['progress' => 50, 'color' => 'primary', 'icon' => 'gear'],
            'shipped' => ['progress' => 75, 'color' => 'secondary', 'icon' => 'truck'],
            'delivered' => ['progress' => 100, 'color' => 'success', 'icon' => 'check-circle'],
            'cancelled' => ['progress' => 0, 'color' => 'danger', 'icon' => 'x-circle'],
            'refunded' => ['progress' => 0, 'color' => 'dark', 'icon' => 'arrow-clockwise']
        ];
    }

    public function calculateOrderMetrics(Order $order): array
    {
        $metrics = [];
        
        // Temps de traitement
        if ($order->getShippedAt()) {
            $processingTime = $order->getOrderDate()->diff($order->getShippedAt());
            $metrics['processing_days'] = $processingTime->days;
        }
        
        // Temps de livraison total
        if ($order->getDeliveredAt()) {
            $totalTime = $order->getOrderDate()->diff($order->getDeliveredAt());
            $metrics['delivery_days'] = $totalTime->days;
        }
        
        return $metrics;
    }
}
```

### Template Twig Personnalisé

```twig
{# templates/admin/orders/dashboard.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}Dashboard Commandes{% endblock %}

{% block body %}
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard Commandes
                </h1>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filtersModal">
                        <i class="bi bi-funnel me-1"></i>Filtres Avancés
                    </button>
                    <button class="btn btn-success btn-sm" onclick="exportData('excel')">
                        <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                    </button>
                </div>
            </div>
            
            {# Métriques rapides #}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Commandes Aujourd'hui</h6>
                                    <h3 class="mb-0">{{ orders_today ?? 0 }}</h3>
                                </div>
                                <i class="bi bi-cart-plus fs-2 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">CA du Jour</h6>
                                    <h3 class="mb-0">{{ revenue_today|number_format(0, ',', ' ') ?? 0 }} €</h3>
                                </div>
                                <i class="bi bi-currency-euro fs-2 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">En Attente</h6>
                                    <h3 class="mb-0">{{ pending_orders ?? 0 }}</h3>
                                </div>
                                <i class="bi bi-clock fs-2 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Expéditions</h6>
                                    <h3 class="mb-0">{{ shipped_today ?? 0 }}</h3>
                                </div>
                                <i class="bi bi-truck fs-2 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {# DataTable Principal #}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        Liste des Commandes
                    </h5>
                </div>
                <div class="card-body p-0">
                    {{ component('sigmasoft_datatable', { 
                        configuration: datatableConfig 
                    }) }}
                </div>
            </div>
        </div>
    </div>
</div>

{# Modal pour filtres avancés #}
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filtres Avancés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {# Formulaire de filtres personnalisés #}
                <form id="advancedFilters">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Période</label>
                            <select class="form-select" name="period">
                                <option value="">Toutes les périodes</option>
                                <option value="today">Aujourd'hui</option>
                                <option value="week">Cette semaine</option>
                                <option value="month">Ce mois</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Montant minimum</label>
                            <input type="number" class="form-control" name="min_amount" placeholder="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="applyAdvancedFilters()">Appliquer</button>
            </div>
        </div>
    </div>
</div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script>
        function exportData(format) {
            // Logique d'export
            window.open('/admin/orders/export/' + format, '_blank');
        }
        
        function applyAdvancedFilters() {
            // Logique d'application des filtres
            const formData = new FormData(document.getElementById('advancedFilters'));
            // Recharger la datatable avec les nouveaux filtres
            location.reload();
        }
        
        // Auto-refresh toutes les 30 secondes
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                // Recharger seulement la datatable
                document.querySelector('[data-controller="live"]')?._component?.render();
            }
        }, 30000);
    </script>
{% endblock %}
```

---

*Ces exemples montrent la puissance et la flexibilité du SigmasoftDataTableBundle pour créer des interfaces de gestion complètes et professionnelles.*