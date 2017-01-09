<?php
namespace Menu\Model;

use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Collection\Collection;
use Slug\Model\Route;

class MenuItem extends AbstractEntity
{

    /**
     * @return int
     */
    public function getId()
    {
        return $this->get('id');
    }

    /**
     * @param int $id
     * @return MenuItem
     */
    public function setId($id)
    {
        $this->set('id', $id);
        return $this;
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->get('menu');
    }

    /**
     * @param Menu $menu
     * @return MenuItem
     */
    public function setMenu(Menu $menu)
    {
        $this->set('menu', $menu);
		return $this;
    }

    /**
     * @return MenuItem
     */
    public function getParentMenuItem()
    {
        return $this->get('parent_menu_item');
    }

    /**
     * @param MenuItem $parentMenuItem
     * @return MenuItem
     */
    public function setParentMenuItem(MenuItem $parentMenuItem)
    {
        $this->set('parent_menu_item', $parentMenuItem);
		return $this;
    }

    /**
     * @return int
     */
    public function getOrderBy()
    {
        return $this->get('order_by');
    }

    /**
     * @param int $orderBy
     * @return MenuItem
     */
    public function setOrderBy($orderBy)
    {
        $this->set('order_by', $orderBy);
		return $this;
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->get('route');
    }

    /**
     * @param Route $route
     * @return MenuItem
     */
    public function setRoute(Route $route)
    {
        $this->set('route', $route);
		return $this;
    }

    /**
     * @return string
     */
    public function getNavText()
    {
        return $this->get('nav_text');
    }

    /**
     * @param string $navText
     * @return MenuItem
     */
    public function setNavText($navText)
    {
        $this->set('nav_text', $navText);
		return $this;
    }

    /**
     * @return string
     */
    public function getExternal()
    {
        return $this->get('external');
    }

    /**
     * @param string $external
     * @return MenuItem
     */
    public function setExternal($external)
    {
        $this->set('external', $external);
		return $this;
    }

    /**
     * @return Collection
     */
    public function getItems()
    {
        return $this->get('items');
    }

    /**
     * @param MenuItem $item
     * @return MenuItem
     */
    public function addItem(MenuItem $item)
    {
        $item->setMenu($this->getMenu());
        $item->setParentMenuItem($this);
        $this->getItems()->add($item);
		return $this;
    }

    /**
     * @param MenuItem $item
     * @return MenuItem
     */
    public function deleteItem(MenuItem $item)
    {
        $this->getItems()->delete($item);
		return $this;
    }

    /**
     * @return MenuItem
     */
    public function deleteAllItems()
    {
        foreach ($this->getItems() as $item) {
            $this->deleteItem($item);
        }
		return $this;
    }

}
