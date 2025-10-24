<?php declare(strict_types=1);

namespace UserPlay\Core;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;
        $router->addRoute('api/user/process', 'User:process');
        $router->addRoute('main', 'Main:default');
        $router->addRoute('', 'Main:default');

        return $router;
    }
}
