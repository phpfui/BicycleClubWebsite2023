<?php

namespace App\UI;

class Loading extends \PHPFUI\HTML5Element
	{
	public function __construct(string $class = '')
		{
		parent::__construct('div');
		$this->addClass($class . ' grid-x grid-padding-x align-center-middle text-center');
		$cell = new \PHPFUI\Cell();
		$cell->add("<i class='fa-solid fa-circle-notch fa-spin-pulse fa-5x fa-fw'></i>");
		$span = new \PHPFUI\HTML5Element('span');
		$span->add('Loading...');
		$span->addClass('show-for-sr');
		$cell->add($span);
		$this->add($cell);
		}
	}
