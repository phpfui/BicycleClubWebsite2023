<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class Banner extends \App\Record\Definition\Banner
	{
	public function clean() : static
		{
		$this->cleanProperName('description');

		return $this;
		}
	}
