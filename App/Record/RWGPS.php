<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class RWGPS extends \App\Record\Definition\RWGPS
	{
	public function insert() : int
		{
		$this->computeFeetPerMile();

		return parent::insert();
		}

	public function insertOrUpdate() : int
		{
		$this->computeFeetPerMile();

		return parent::insertOrUpdate();
		}

	public function update() : bool
		{
		$this->computeFeetPerMile();

		return parent::update();
		}

	public function computeFeetPerMile() : static
		{
		if (! empty($this->elevation) && ! empty($this->mileage))
			{
			$this->feetPerMile = $this->elevation / $this->mileage;
			}

		return $this;
		}
	}
