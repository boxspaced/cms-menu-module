<?php
namespace Boxspaced\CmsMenuModule\Model;

use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Collection\Collection;

class Menu extends AbstractEntity
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
     * @return Menu
     */
    public function setId($id)
    {
        $this->set('id', $id);
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->get('name');
    }

    /**
     * @param string $name
     * @return Menu
     */
    public function setName($name)
    {
        $this->set('name', $name);
		return $this;
    }

    /**
     * @return bool
     */
    public function getPrimary()
    {
        return $this->get('primary');
    }

    /**
     * @param bool $primary
     * @return Menu
     */
    public function setPrimary($primary)
    {
        $this->set('primary', $primary);
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
     * @return Menu
     */
    public function addItem(MenuItem $item)
    {
        $item->setMenu($this);
        $this->getItems()->add($item);
		return $this;
    }

    /**
     * @param MenuItem $item
     * @return Menu
     */
    public function deleteItem(MenuItem $item)
    {
        $this->getItems()->delete($item);
		return $this;
    }

    /**
     * @return Menu
     */
    public function deleteAllItems()
    {
        foreach ($this->getItems() as $item) {
            $this->deleteItem($item);
        }
		return $this;
    }

}
