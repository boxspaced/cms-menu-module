<?php
namespace Menu;

use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Mapper\Conditions;
use Zend\Router\Http\Segment;
use Slug\Model\Route;
use Core\Model\RepositoryFactory;
use Zend\Permissions\Acl\Acl;

return [
    'menu' => [
        'max_menu_levels' => 3,
    ],
    'router' => [
        'routes' => [
            // LIFO
            'menu' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/menu[/:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\MenuController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            // LIFO
        ],
    ],
    'acl' => [
        'resources' => [
            [
                'id' => Controller\MenuController::class,
            ],
        ],
        'rules' => [
            [
                'type' => Acl::TYPE_ALLOW,
                'roles' => 'author',
                'resources' => Controller\MenuController::class,
                'privileges' => [
                    'index',
                    'internal-links',
                ],
            ],
            [
                'type' => Acl::TYPE_ALLOW,
                'roles' => 'publisher',
                'resources' => Controller\MenuController::class,
                'privileges' => 'shuffle',
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'Navigation\Main' => Navigation\MainNavigationFactory::class,
            Service\MenuService::class => Service\MenuServiceFactory::class,
            Model\MenuRepository::class => RepositoryFactory::class,
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\MenuController::class => Controller\MenuControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'entity_manager' => [
        'types' => [
            Model\Menu::class => [
                'mapper' => [
                    'params' => [
                        'table' => 'menu',
                    ],
                ],
                'entity' => [
                    'fields' => [
                        'id' => [
                            'type' => AbstractEntity::TYPE_INT,
                        ],
                        'name' => [
                            'type' => AbstractEntity::TYPE_STRING,
                        ],
                        'primary' => [
                            'type' => AbstractEntity::TYPE_BOOL,
                        ],
                    ],
                    'one_to_many' => [
                        'items' => [
                            'type' => Model\MenuItem::class,
                            'conditions' => function ($id) {
                                return (new Conditions())
                                        ->field('parent_menu_item')->isNull()
                                        ->field('menu.id')->eq($id)
                                        ->order('order_by', Conditions::ORDER_ASC);
                            },
                        ],
                    ],
                ],
            ],
            Model\MenuItem::class => [
                'mapper' => [
                    'params' => [
                        'table' => 'menu_item',
                        'columns' => [
                            'menu' => 'menu_id',
                            'parent_menu_item' => 'parent_menu_item_id',
                            'route' => 'route_id',
                        ],
                    ],
                ],
                'entity' => [
                    'fields' => [
                        'id' => [
                            'type' => AbstractEntity::TYPE_INT,
                        ],
                        'order_by' => [
                            'type' => AbstractEntity::TYPE_INT,
                        ],
                        'nav_text' => [
                            'type' => AbstractEntity::TYPE_STRING,
                        ],
                        'external' => [
                            'type' => AbstractEntity::TYPE_STRING,
                        ],
                        'menu' => [
                            'type' => Model\Menu::class,
                        ],
                        'parent_menu_item' => [
                            'type' => Model\MenuItem::class,
                        ],
                        'route' => [
                            'type' => Route::class,
                        ],
                    ],
                    'one_to_many' => [
                        'items' => [
                            'type' => Model\MenuItem::class,
                            'conditions' => function ($id) {
                                return (new Conditions())
                                        ->field('parent_menu_item.id')->eq($id)
                                        ->order('order_by', Conditions::ORDER_ASC);
                            },
                        ],
                    ],
                ],
            ],
        ],
    ],
];
