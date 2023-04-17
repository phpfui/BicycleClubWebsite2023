<?php

namespace App\UI;

/**
 * Default all Accordions to use multi-expand and close all
 */
class Accordion extends \PHPFUI\Accordion
	{
	public function __construct()
		{
		parent::__construct();
		$this->addAttribute('data-multi-expand', 'true');
		$this->addAttribute('data-allow-all-closed', 'true');
		}
	}
