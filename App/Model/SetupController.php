<?php

namespace App\Model;

class SetupController extends \PHPFUI\NanoController implements \PHPFUI\Interfaces\NanoController
	{
	public function __construct()
		{
		$uri = $_SERVER['REQUEST_URI'] ?? '';
		$query = \strpos((string)$uri, '?');

		if (false !== $query)
			{
			$uri = \substr((string)$uri, 0, $query);
			}
		parent::__construct($uri);
		$this->setMissingClass(\App\View\Setup\Missing::class);
		$this->setHomePageClass(\App\View\Maintenance::class);
		$this->setMissingMethod('wizard');
		$this->setRootNamespace('App\\WWW');
		}
	}
