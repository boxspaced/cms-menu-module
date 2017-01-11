<?php
namespace Boxspaced\CmsMenuModule\Service;

use DateTime;

class MenuItem
{

    /**
     *
     * @var bool
     */
    public $external;

    /**
     *
     * @var bool
     */
    public $module;

    /**
     *
     * @var string
     */
    public $navText;

    /**
     *
     * @var string
     */
    public $slug;

    /**
     *
     * @var string
     */
    public $typeIcon;

    /**
     *
     * @var string
     */
    public $typeName;

    /**
     *
     * @var string
     */
    public $moduleName;

    /**
     *
     * @var string
     */
    public $actionName;

    /**
     *
     * @var string
     */
    public $identifier;

    /**
     *
     * @var DateTime
     */
    public $liveFrom;

    /**
     *
     * @var DateTime
     */
    public $expiresEnd;

    /**
     *
     * @var int
     */
    public $menuItemId;

    /**
     *
     * @var int
     */
    public $numChildMenuItems;

    /**
     *
     * @var int
     */
    public $level;

    /**
     *
     * @var bool
     */
    public $first;

    /**
     *
     * @var bool
     */
    public $last;

    /**
     *
     * @var MenuItem[]
     */
    public $items = [];

}
