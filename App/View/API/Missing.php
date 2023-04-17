<?php

namespace App\View\Api;

class Missing extends \App\View\API\Base implements \PHPFUI\Interfaces\NanoClass
	{
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		$this->logError($controller->getUri() . ' is not a valid API route.', 400);
		$this->logError('Valid routes are:', 400);

		foreach (\glob(PROJECT_ROOT . '/App/API/V1/*.php') as $file)
			{
			$parts = \explode('/', (string)$file);
			$routes = \explode('.', \array_pop($parts));
			$this->logError('/V1/' . $routes[0], 400);
			}
		}
	}
