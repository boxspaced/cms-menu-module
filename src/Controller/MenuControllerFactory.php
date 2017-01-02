<?php
namespace Menu\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Menu\Controller\MenuController;
use Standalone\Service\StandaloneService;
use Menu\Service\MenuService;
use Account\Service\AccountService;
use Zend\Log\Logger;
use Core\Controller\AbstractControllerFactory;

class MenuControllerFactory extends AbstractControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new MenuController(
            $container->get(StandaloneService::class),
            $container->get(MenuService::class),
            $container->get(AccountService::class),
            $container->get(Logger::class),
            $container->get('config')
        );

        return $this->forceHttps($controller, $container);
    }

}
