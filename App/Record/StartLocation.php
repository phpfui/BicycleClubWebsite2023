<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class StartLocation extends \App\Record\Definition\StartLocation
	{
	public function addressLink() : ?\PHPFUI\Link
		{
		if ($this->address && $this->town && $this->state)
			{
			$address = "{$this->address}, {$this->town}, {$this->state}";

			return new \PHPFUI\Link('https://www.google.com/maps/place/' . \str_replace([', ', ' '], '+', $address), $address);
			}

		return null;
		}

	public function clean() : static
		{
		$this->cleanProperName('address');
		$this->cleanProperName('town');
		$this->cleanProperName('nearestExit');
		$this->cleanUpperCase('state');
		$this->cleanPhone('zip', '\\-');

		return $this;
		}

	public function coordinatesLink() : ?\PHPFUI\Link
		{
		if ($this->latitude && $this->longitude)
			{
			return new \PHPFUI\Link("https://www.google.com/maps/?q={$this->latitude},{$this->longitude}", 'Google Maps');
			}

		return null;
		}

	public function userLink() : ?\PHPFUI\Link
		{
		if (\filter_var($this->link, FILTER_VALIDATE_URL))
			{
			return new \PHPFUI\Link($this->link, $this->name);
			}

		return null;
		}
	}
