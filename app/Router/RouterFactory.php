<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;

        $router->addRoute('api/v1/<action>[/<id>]', 'ApiV1:Api:default');

        // Ukázka možnosti nastavení routeru na další verzi API
        //$router->addRoute('api/v2/<action>[/<id>]', 'ApiV2:Api:default');

		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');

		return $router;
	}
}
