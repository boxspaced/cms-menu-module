<?php
namespace Menu\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Log\Logger;
use Zend\Authentication\AuthenticationService;
use Boxspaced\EntityManager\EntityManager;
use Menu\Model;
use Account\Model\UserRepository;

class MenuServiceFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new MenuService(
            $container->get('Cache\Long'),
            $container->get(Logger::class),
            $container->get(AuthenticationService::class),
            $container->get(EntityManager::class),
            $container->get(UserRepository::class),
            $container->get(Model\MenuRepository::class)
        );
    }

}
