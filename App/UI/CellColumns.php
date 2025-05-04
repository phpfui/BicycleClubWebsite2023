<?php

namespace App\UI;

class CellColumns extends \PHPFUI\GridX
	{
	public function __construct()
		{
		parent::__construct();
		$this->setMargin();
		$this->addClass('align-middle');
		}

	/**
	 * Add an element to the container
	 */
	public function add(mixed $object, ?int $columns = null) : static
		{
		$cell = new \PHPFUI\Cell();

		if (null === $columns)
			{
			$cell->setAuto();
			}
		else
			{
			$cell->setSmall($columns);
			}

		$cell->add($object);
		parent::add($cell);

		return $this;
		}
	}
