<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

/**
 * Factory of the default router.
 */
class RouterFactory
{
    /** @var Nette\DI\Container */
    private $container;

    /**
     * DI Constructor.
     * @param Nette\DI\Container $container
     */
    public function __construct(Nette\DI\Container $container)
    {
        $this->container = $container;
    }

    /**
     * Create default router, all routes are automatically discovered from
     * presenters.
     * @return Nette\Application\IRouter
     */
    public function create()
    {
        $router = new RouteList;

        // Route::SECURED flag should be used on https connections
        $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default', Route::SECURED);

        return $router;
    }
}
