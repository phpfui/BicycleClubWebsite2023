<?php

namespace App\UI;

class GearCalculator
	{
	public function __construct(private readonly \PHPFUI\Page $page) // @phpstan-ignore-line
		{
		}

	public function show() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$container->add(new \App\UI\WheelSize('678~28-622'));
		$container->add(new \App\UI\CassettePicker('9-10-11-12-13'));

		return $container;
		}
	}