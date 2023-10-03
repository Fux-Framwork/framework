<?php

namespace Fux\Routing;

use Fux\Http\Middleware\DefaultCsrfProtectionMiddleware;
use Fux\Http\Request;

class Routing
{

    private static $router;

    public static function router()
    {
        if (!self::$router) {
            self::$router = new Router(new Request());
            if (ENABLE_DEFAULT_CSRF_MIDDLEWARE) {
                self::$router->addMiddlewares([new DefaultCsrfProtectionMiddleware()]);
            }
        }
        return self::$router;
    }

}