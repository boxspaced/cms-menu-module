<?php
namespace Menu\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Log\Logger;
use Menu\Service;
use Account\Service\AccountService;
use Standalone\Service\StandaloneService;

class MenuController extends AbstractActionController
{

    /**
     * @var StandaloneService
     */
    protected $standaloneService;

    /**
     * @var Service\MenuService
     */
    protected $menuService;

    /**
     * @var AccountService
     */
    protected $accountService;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ViewModel
     */
    protected $view;

    /**
     * @param StandaloneService $standaloneService
     * @param Service\MenuService $menuService
     * @param AccountService $accountService
     * @param Logger $logger
     * @param array $config
     */
    public function __construct(
        StandaloneService $standaloneService,
        Service\MenuService $menuService,
        AccountService $accountService,
        Logger $logger,
        array $config
    )
    {
        $this->standaloneService = $standaloneService;
        $this->menuService = $menuService;
        $this->accountService = $accountService;
        $this->logger = $logger;
        $this->config = $config;

        $this->view = new ViewModel();
        $this->view->setTerminal(true);
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $menu = $this->menuService->getMenu();

        $adminNavigation = $this->adminNavigationWidget();
        if (null !== $adminNavigation) {
            $this->view->addChild($adminNavigation, 'adminNavigation');
        }

        $menuItems = [];
        $items = [];

        $this->flattenMenuRecursive($menu->items, $items);

        foreach ($items as $item) {

            if ($item->module) {
                // @todo fix (hack to force view to display correct text)
                $item->moduleName = 'item';
            }

            $menuItem = array(
                'external' => $item->external,
                'module' => $item->module,
                'level' => $item->level,
                'first' => $item->first,
                'last' => $item->last,
                'typeIcon' => $item->typeIcon,
                'typeName' => $item->typeName,
                'moduleName' => $item->moduleName,
                'name' => $item->slug,
                'id' => $item->identifier,
                'menuItemId' => $item->menuItemId,
                'numChildMenuItems' => $item->numChildMenuItems,
                'maxMenuLevels' => $this->config['menu']['max_menu_levels'],
                'allowShuffleMenu' => $this->accountService->isAllowed(get_class(), 'shuffle'),
            );

            if (!$item->external && !$item->module) {

                // @todo remove, should be view helpers
                $lifespanState = $this->itemAdminWidget()->calcLifeSpanState($item->liveFrom, $item->expiresEnd);
                $lifespanTitle = $this->itemAdminWidget()->calcLifeSpanTitle($item->liveFrom, $item->expiresEnd);

                $menuItem['lifespanState'] = $lifespanState;
                $menuItem['lifespanTitle'] = $lifespanTitle;
                $menuItem['allowEdit'] = $this->accountService->isAllowed($item->moduleName, 'edit');
                $menuItem['allowPublish'] = $this->accountService->isAllowed($item->moduleName, 'publish');
                $menuItem['allowDelete'] = $this->accountService->isAllowed($item->moduleName, 'delete');
            }

            if (!$item->external) {
                $menuItem['allowCreate'] = $this->accountService->isAllowed('item', 'create');
            }

            $menuItems[] = $menuItem;
        }

        $this->view->menuItems = $menuItems;

        $this->view->allowCreateItem = $this->accountService->isAllowed('item', 'create');

        return $this->view;
    }

    /**
     * @param Service\MenuItem[] $items
     * @param Service\MenuItem[] $return
     * @return void
     */
    protected function flattenMenuRecursive($items, &$return)
    {
        foreach ($items as $item) {

            $return[] = $item;

            if (count($item->items)) {
                $this->flattenMenuRecursive($item->items, $return);
            }
        }
    }

    /**
     * @return void
     */
    public function shuffleAction()
    {
        $id = $this->params()->fromRoute('id');
        $direction = $this->params()->fromQuery('direction');

        $this->menuService->moveItem($id, $direction);

        $this->flashMessenger()->addSuccessMessage('Item moved successfully.');

        return $this->redirect()->toRoute('menu');
    }

    /**
     * @return void
     */
    public function internalLinksAction()
    {
        $this->view->standaloneItems = $this->standaloneService->getPublishedStandalone();
        return $this->view;
    }

}