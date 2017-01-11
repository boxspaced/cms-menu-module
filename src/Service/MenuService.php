<?php
namespace Boxspaced\CmsMenuModule\Service;

use Zend\Cache\Storage\Adapter\AbstractAdapter as Cache;
use Zend\Log\Logger;
use Zend\Authentication\AuthenticationService;
use Boxspaced\EntityManager\EntityManager;
use Boxspaced\CmsMenuModule\Model;
use Zend\Filter\StaticFilter;
use Zend\Filter\Word\DashToCamelCase;
use Boxspaced\EntityManager\Collection\Collection;
use Boxspaced\CmsMenuModule\Exception;
use Boxspaced\CmsAccountModule\Model\UserRepository;
use Boxspaced\CmsAccountModule\Model\User;

class MenuService
{

    const MOVE_DIRECTION_UP = 'up';
    const MOVE_DIRECTION_DOWN = 'down';
    const MENU_CACHE_ID = 'menu';

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var Model\MenuRepository
     */
    protected $menuRepository;

    /**
     * @param Cache $cache
     * @param AuthenticationService $authService
     * @param EntityManager $entityManager
     * @param UserRepository $userRepository
     * @param Model\MenuRepository $menuRepository
     */
    public function __construct(
        Cache $cache,
        Logger $logger,
        AuthenticationService $authService,
        EntityManager $entityManager,
        UserRepository $userRepository,
        Model\MenuRepository $menuRepository
    )
    {
        $this->cache = $cache;
        $this->logger = $logger;
        $this->authService = $authService;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->menuRepository = $menuRepository;

        if ($this->authService->hasIdentity()) {
            $identity = $authService->getIdentity();
            $this->user = $userRepository->getById($identity->id);
        }
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        $cached = $this->cache->getItem(static::MENU_CACHE_ID);

        if (null !== $cached) {
            return $cached;
        }

        $menuEntity = $this->menuRepository->getByName('main');

        $menu = new Menu();
        $menu->name = $menuEntity->getName();
        $menu->items = $this->assembleRecursive($menuEntity->getItems());

        $this->cache->setItem(static::MENU_CACHE_ID, $menu);

        return $menu;
    }

    /**
     * @param Collection $menuItemEntities
     * @param int $level
     * @return MenuItem[]
     */
    protected function assembleRecursive(Collection $menuItemEntities, $level = 1)
    {
        $menuItems = [];

        $total = count($menuItemEntities);
        $count = 1;

        foreach ($menuItemEntities as $menuItemEntity) {

            $menuItem = new MenuItem();

            if ($menuItemEntity->getExternal()) {

                $menuItem->external = true;
                $menuItem->slug = $menuItemEntity->getExternal();
                $menuItem->navText = $menuItemEntity->getNavText();
                $menuItem->typeIcon = '/images/icons/system_page.png';
                $menuItem->typeName = 'external';
                $menuItem->menuItemId = $menuItemEntity->getId();
                $menuItem->numChildMenuItems = count($menuItemEntity->getItems());
                $menuItem->level = (int) $level;
                $menuItem->first = ($count === 1);
                $menuItem->last = ($count === $total);

                if (count($menuItemEntity->getItems())) {
                    $menuItem->items = $this->assembleRecursive($menuItemEntity->getItems(), $level+1);
                }

            } else {

                $identifier = $menuItemEntity->getRoute()->getIdentifier();

                if (is_numeric($identifier)) {

                    $contentEntity = $this->getContentByMenuItem($menuItemEntity);
                    $menuItem->navText = $contentEntity->getNavText();
                    $menuItem->typeIcon = $contentEntity->getType()->getIcon();
                    $menuItem->typeName = $contentEntity->getType()->getName();
                    $menuItem->liveFrom = $contentEntity->getLiveFrom();
                    $menuItem->expiresEnd = $contentEntity->getExpiresEnd();

                } else {

                    $menuItem->module = true;
                    $menuItem->navText = $menuItemEntity->getNavText();
                    $menuItem->typeName = $menuItemEntity->getRoute()->getModule()->getName() . ' page';
                    $menuItem->typeIcon = '/images/icons/system_page.png';
                }

                $menuItem->moduleName = $menuItemEntity->getRoute()->getModule()->getRouteController();
                $menuItem->actionName = $menuItemEntity->getRoute()->getModule()->getRouteAction();
                $menuItem->identifier = $identifier;
                $menuItem->menuItemId = $menuItemEntity->getId();
                $menuItem->slug = $menuItemEntity->getRoute()->getSlug();
                $menuItem->numChildMenuItems = count($menuItemEntity->getItems());
                $menuItem->level = (int) $level;
                $menuItem->first = ($count === 1);
                $menuItem->last = ($count === $total);

                if (count($menuItemEntity->getItems())) {
                    $menuItem->items = $this->assembleRecursive($menuItemEntity->getItems(), $level+1);
                }
            }

            $menuItems[] = $menuItem;
            $count++;
        }

        return $menuItems;
    }

    /**
     * @param Model\MenuItem $menuItem
     * @return \Boxspaced\EntityManager\Entity\AbstractEntity
     */
    protected function getContentByMenuItem(Model\MenuItem $menuItem)
    {
        $route = $menuItem->getRoute();

        if (is_numeric($route->getIdentifier())) {

            $module = $route->getModule();

            $entityName = rtrim($module->getName(), 's');
            $entityName = ucfirst(StaticFilter::execute($entityName, DashToCamelCase::class));
            $entityName = str_replace(
                '##',
                $entityName,
                'Boxspaced\\Cms##Module\\Model\\##'
            );

            return $this->entityManager->find($entityName, $route->getIdentifier());
        }

        return null;
    }

    /**
     * @param int $id
     * @param string $direction
     * @return void
     */
    public function moveItem($id, $direction)
    {
        if (!in_array($direction, array(
            static::MOVE_DIRECTION_UP,
            static::MOVE_DIRECTION_DOWN,
        ))) {
            throw new Exception\UnexpectedValueException('Invalid direction');
        }

        $menu = $this->menuRepository->getByName('main');

        $flattenedMenu = [];
        $this->flattenMenuRecursive($menu->getItems(), $flattenedMenu);

        $itemToMove = array_filter($flattenedMenu, function($flattenedMenuItem) use ($id) {
            return ($flattenedMenuItem->getId() == $id);
        });

        $itemToMove = array_shift($itemToMove);

        if (null === $itemToMove) {
            throw new Exception\UnexpectedValueException('Menu item not found with given id');
        }

        // Get all siblings
        if ($itemToMove->getParentMenuItem()) {
            $siblings = $itemToMove->getParentMenuItem()->getItems();
        } else {
            $siblings = $menu->getItems();
        }

        // Get next and prev
        $current = $siblings->rewind()->current();

        while ($current !== null) {

            if ($current === $itemToMove) {

                $prevSibling = $siblings->prev();

                if (!$prevSibling) {
                    $siblings->first();
                } else {
                    $siblings->next(); // Back to item to move
                }

                $nextSibling = $siblings->next();
                break;
            }

            $current = $siblings->next();
        }

        // Move
        if ($direction === static::MOVE_DIRECTION_UP) {

            if (!$prevSibling) {
                throw new Exception\UnexpectedValueException('Menu item can not be moved up any further');
            }

            $prevSiblingOrderBy = $prevSibling->getOrderBy();
            $itemToMoveOrderBy = $itemToMove->getOrderBy();

            $itemToMove->setOrderBy($prevSiblingOrderBy);
            $prevSibling->setOrderBy($itemToMoveOrderBy);

        } elseif ($direction === static::MOVE_DIRECTION_DOWN) {

            if (!$nextSibling) {
                throw new Exception\UnexpectedValueException('Menu item can not be moved down any further');
            }

            $nextSiblingOrderBy = $nextSibling->getOrderBy();
            $itemToMoveOrderBy = $itemToMove->getOrderBy();

            $itemToMove->setOrderBy($nextSiblingOrderBy);
            $nextSibling->setOrderBy($itemToMoveOrderBy);
        }

        $this->entityManager->flush();

        // Clear cache
        $this->cache->removeItem(static::MENU_CACHE_ID);
    }

    /**
     * @param \Boxspaced\EntityManager\Collection\Collection $items
     * @param array $flattened
     * @return void
     */
    protected function flattenMenuRecursive(\Boxspaced\EntityManager\Collection\Collection $items, array &$flattened)
    {
        foreach ($items as $item) {

            $flattened[] = $item;

            if (count($item->getItems())) {
                $this->flattenMenuRecursive($item->getItems(), $flattened);
            }
        }
    }

}
