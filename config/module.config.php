<?php
namespace Menu;

use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Mapper\Conditions\Conditions;
use Zend\Router\Http\Segment;
use Slug\Model\Route;
use Core\Model\RepositoryFactory;

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
                    'children' => [
                        'items' => [
                            'type' => Model\MenuItem::class,
                            'conditions' => function ($id) {
                                return (new Conditions())
                                        ->field('parentMenuItem')->isNull()
                                        ->field('menu.id')->eq($id)
                                        ->order('orderBy', Conditions::ORDER_ASC);
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
                            'parentMenuItem' => 'parent_menu_item_id',
                            'route' => 'route_id',
                        ],
                    ],
                ],
                'entity' => [
                    'fields' => [
                        'id' => [
                            'type' => AbstractEntity::TYPE_INT,
                        ],
                        'orderBy' => [
                            'type' => AbstractEntity::TYPE_INT,
                        ],
                        'navText' => [
                            'type' => AbstractEntity::TYPE_STRING,
                        ],
                        'external' => [
                            'type' => AbstractEntity::TYPE_STRING,
                        ],
                        'menu' => [
                            'type' => Model\Menu::class,
                        ],
                        'parentMenuItem' => [
                            'type' => Model\MenuItem::class,
                        ],
                        'route' => [
                            'type' => Route::class,
                        ],
                    ],
                    'children' => [
                        'items' => [
                            'type' => Model\MenuItem::class,
                            'conditions' => function ($id) {
                                return (new Conditions())
                                        ->field('parentMenuItem.id')->eq($id)
                                        ->order('orderBy', Conditions::ORDER_ASC);
                            },
                        ],
                    ],
                ],
            ],
        ],
    ],
];
