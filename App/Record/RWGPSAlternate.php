<?php

namespace App\Record;

class RWGPSAlternate extends \App\Record\Definition\RWGPSAlternate
	{
	protected static array $virtualFields = [
		'alternateRoutes' => [\PHPFUI\ORM\RelatedRecord::class, \App\Table\RWGPS::class],
	];

	public function delete() : bool
		{
		$this->swap();
		parent::delete();
		$this->swap();

		return parent::delete();
		}

	public function insert() : int | bool
		{
		$this->swap();
		parent::insertOrIgnore();
		$this->swap();

		return parent::insertOrIgnore();
		}

	public function insertOrIgnore() : int | bool
		{
		return $this->insert();
		}

	public function insertOrUpdate() : int | bool
		{
		return $this->insert();
		}

	public function swap() : static
		{
		$temp = (int)$this->RWGPSId;
		$this->RWGPSId = (int)$this->RWGPSAlternateId;
		$this->RWGPSAlternateId = $temp;

		return $this;
		}
	}
