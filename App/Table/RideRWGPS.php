<?php

namespace App\Table;

class RideRWGPS extends \PHPFUI\ORM\Table
	{
	protected static string $className = \App\Record\RideRWGPS::class;

	public function changeRWGPSId(\App\Record\RWGPS $old, ?\App\Record\RWGPS $new) : void
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('RWGPSId', $old->RWGPSId));

		if (null !== $new)
			{
			$this->update(['RWGPSId' => $new->RWGPSId]);
			}
		else
			{
			$this->delete();
			}
		}
	}
