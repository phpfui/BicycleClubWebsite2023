<?php

namespace App\View\Setup;

class Missing extends \PHPFUI\Page implements \PHPFUI\Interfaces\NanoClass
	{
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		// @phpstan-ignore-next-line
		parent::__construct($controller);
		$message = $_SERVER['REQUEST_URI'] . ' is MISSING!';
		$this->setPageName($message);

		$this->add(new \PHPFUI\Header($message));
		$output = '';

		foreach ($controller->getErrors() as $key => $value)
			{
			if (\is_numeric($key))
				{
				$output .= "<b>{$value}</b><br>";
				}
			else
				{
				$output .= "<b>{$key}:</b> {$value}<br>";
				}
			}

		if (! empty($_SERVER['HTTP_REFERER']))
			{
			$output .= "<br>HTTP_REFERER: {$_SERVER['HTTP_REFERER']}<p>";
			}

		\http_response_code(404);
		$this->add($output);
		}
	}
