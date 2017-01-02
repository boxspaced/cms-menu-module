<?php
namespace Menu\Navigation;

class MainNavigationFactory extends AbstractNavigationFactory
{

    /**
     * @return string
     */
    protected function getName()
    {
        return 'main';
    }

}
