<?php

namespace App\UI;

class BikeShopPicker extends \PHPFUI\Input\Select
	{
	public function __construct(string $name, string $label = '', \App\Record\BikeShop $value = new \App\Record\BikeShop())
		{
		parent::__construct($name, $label);

		$bikeShopTable = new \App\Table\BikeShop();
		$bikeShopTable->addOrderBy('name');

		foreach ($bikeShopTable->getRecordCursor() as $bikeShop)
			{
			$this->addOption($bikeShop->fullName(), $bikeShop->bikeShopId, $bikeShop->bikeShopId == $value->bikeShopId);
			}
		}
	}
