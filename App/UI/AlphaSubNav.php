<?php

namespace App\UI;

class AlphaSubNav extends \App\UI\SubNav
	{
	public function __construct(string $url, string $selected = '')
		{
		parent::__construct();

		$url = \explode('/', $url);

		if (\end($url) == $selected)
			{
			\array_pop($url);
			}
		$url = \implode('/', $url);

		for ($i = 0; $i < 26; ++$i)
			{
			$char = \chr($i + \ord('A'));
			$this->addTab($url . '/' . $char, $char, $selected == $char);
			}
		}
	}
