<?php

namespace App\View\Setup;

class Page extends \PHPFUI\Page
	{
	use \App\Tools\SchemeHost;

	public function isAuthorized(string $permission, ?string $menu = null) : bool
		{
		return true;
		}
	}
