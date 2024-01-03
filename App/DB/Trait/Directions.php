<?php

namespace App\DB\Trait;

trait Directions
	{
	public function coordinatesLink() : ?\PHPFUI\Link
		{
		if ($this->latitude && $this->longitude)
			{
			return new \PHPFUI\Link("https://www.google.com/maps/?q={$this->latitude},{$this->longitude}", 'Google Maps');
			}

		return null;
		}

	public function directionsUrl() : string
		{
		if ($this->latitude && $this->longitude)
			{
			return "https://www.google.com/maps/dir/?api=1&destination={$this->latitude},{$this->longitude}";
			}

		return '';
		}
	}
