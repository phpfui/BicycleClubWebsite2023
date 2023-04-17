<?php

namespace App\View;

class Maintenance extends \PHPFUI\Page implements \PHPFUI\Interfaces\NanoClass
	{
	// @phpstan-ignore-next-line
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct();
		$this->setPageName('OOPS!');
		$this->addCSS('h1, h3 {color:white;} body {background-color:black; text-align: center; font-family: Helvetica,Roboto,Arial,sans-serif;');
		$this->add('<h1>Opps, you caught us with our bibs down!</h1><h3>Come back a little later when we are more presentable.</h3>');
		$this->add('<img style="margin:0 auto;display:block;" src="/images/pissingCyclist.jpg"/>');
		}
	}
