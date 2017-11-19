<?php
namespace Boxspaced\CmsMenuModule\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Boxspaced\CmsMenuModule\Controller\MenuController;
use Boxspaced\CmsStandaloneModule\Service\StandaloneService;
use Boxspaced\CmsMenuModule\Service\MenuService;
use Boxspaced\CmsAccountModule\Service\AccountService;
use Zend\Log\Logger;

class MenuControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new MenuController(
            $container->get(StandaloneService::class),
            $container->get(MenuService::class),
            $container->get(AccountService::class),
            $container->get(Logger::class),
            $container->get('config')
        );
    }

}
