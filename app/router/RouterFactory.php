<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

/**
 * Factory of the default router.
 */
class RouterFactory
{
    use Nette\StaticClass;

    /**
     * Create default router, all routes are automatically discovered from
     * presenters.
     * @return Nette\Routing\Router
     */
    public function create()
    {
        $router = new RouteList;
        $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
        return $router;
    }
}
