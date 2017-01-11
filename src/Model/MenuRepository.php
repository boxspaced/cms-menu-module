<?php
namespace Boxspaced\CmsMenuModule\Model;

use Boxspaced\EntityManager\EntityManager;
use Boxspaced\EntityManager\Collection\Collection;

class MenuRepository
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id
     * @return Menu
     */
    public function getById($id)
    {
        return $this->entityManager->find(Menu::class, $id);
    }

    /**
     * @return Collection
     */
    public function getAll()
    {
        return $this->entityManager->findAll(Menu::class);
    }

    /**
     * @param string $name
     * @return Menu
     */
    public function getByName($name)
    {
        $conditions = $this->entityManager->createConditions();
        $conditions->field('name')->eq($name);
        return $this->entityManager->findOne(Menu::class, $conditions);
    }

    /**
     * @param Menu $entity
     * @return MenuRepository
     */
    public function delete(Menu $entity)
    {
        $this->entityManager->delete($entity);
        return $this;
    }

}
