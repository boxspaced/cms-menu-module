<?php
namespace Boxspaced\CmsMenuModule\Navigation;

use DateTime;
use Zend\Navigation\Service\AbstractNavigationFactory as ZendAbstractNavigationFactory;
use Interop\Container\ContainerInterface;
use Boxspaced\CmsMenuModule\Service\MenuService;
use Boxspaced\CmsMenuModule\Service\MenuItem;
use Zend\Router\Http\RouteMatch;
use Zend\Filter\StaticFilter;
use Zend\Filter\Word\DashToCamelCase;

abstract class AbstractNavigationFactory extends ZendAbstractNavigationFactory
{

    /**
     * @param ContainerInterface $container
     * @return array
     */
    protected function getPages(ContainerInterface $container)
    {
        if (null === $this->pages) {

            $menuService = $container->get(MenuService::class);
            $menu = $menuService->getMenu($this->getName());

            $application = $container->get('Application');
            $routeMatch = $application->getMvcEvent()->getRouteMatch();

            $pages = $this->getPagesFromMenuItems($menu->items, $routeMatch);

            $this->pages = $this->preparePages($container, $pages);
        }

        return $this->pages;
    }

    /**
     * @param MenuItem[] $items
     * @param RouteMatch|null $routeMatch
     * @return array
     */
    protected function getPagesFromMenuItems(array $items, RouteMatch $routeMatch = null)
    {
        $pages = [];

        $hide = array(
            'learning',
            'local-history',
            'idea-stores',
            'digital-gallery-results',
            'digital-gallery-image',
            'digital-gallery-basket',
            'course-results',
            'course-details',
            'whats-on-results',
            'whats-on-details',
	    'about-us-stock-suggestion',
            'about-us-it-contact',
	    'about-us',
	    'idea-online',
	    'whats-on',
	    'e-library',
        );

        foreach ($items as $item) {

            $page = null;

            if ($item->external) {

                $page = [
                    'label' => $item->navText,
                    'uri' => $item->slug,
                    'class' => 'external-menu-item',
                ];

            } elseif ($item->module || $this->isLive($item->liveFrom, $item->expiresEnd)) {

                $this->isActive($item, $routeMatch);

                $page = [
                    'label' => $item->navText,
                    'route' => 'content',
                    'active' => $this->isActive($item, $routeMatch),
                    'params' => [
                        'slug' => $item->slug,
                    ],
                ];
            }

            if (null === $page) {
                continue;
            }

            if (in_array($item->slug, $hide)) {
                $page['class'] = 'hidden-menu-item';
            }

            if ($item->items) {
                $page['pages'] = $this->getPagesFromMenuItems($item->items, $routeMatch);
            }

            $pages[] = $page;
        }

        return $pages;
    }

    /**
     * @param MenuItem $menuItem
     * @param RouteMatch|null $routeMatch
     * @return boolean
     */
    protected function isActive($menuItem, RouteMatch $routeMatch = null)
    {
        if (null === $routeMatch) {
            return false;
        }

        $routeMatchParams = $routeMatch->getParams();

        $menuItemParams = [
            'controller' => $this->getControllerName($menuItem->moduleName),
            'action' => ($menuItem->module) ? $menuItem->identifier : $menuItem->actionName,
        ];

        if (!$menuItem->module) {
            $menuItemParams['id'] = $menuItem->identifier;
        }

        return count(array_intersect_assoc($routeMatchParams, $menuItemParams)) === count($menuItemParams);
    }

    /**
     * @param string $moduleName
     * @return string
     */
    protected function getControllerName($moduleName)
    {
        return str_replace(
            '##',
            StaticFilter::execute($moduleName, DashToCamelCase::class),
            'Boxspaced\\Cms##Module\\Controller\\##Controller'
        );
    }

    /**
     * @param DateTime $liveFrom
     * @param DateTime $expiresEnd
     * @return bool
     */
    protected function isLive(DateTime $liveFrom, DateTime $expiresEnd)
    {
        $now = new DateTime();
        return ($liveFrom < $now && $expiresEnd > $now);
    }

}
