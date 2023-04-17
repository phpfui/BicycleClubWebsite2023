<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class StoreOption extends \App\Record\Definition\StoreOption
	{
	/**
	 * @return array<int, string>
	 */
	public function getOptions() : array
		{
		return \explode(',', $this->optionValues ?? '');
		}

	/**
	 * @param array<string> $options
	 */
	public function setOptions(array $options) : static
		{
		$optionString = $comma = '';

		foreach ($options as $option)
			{
			$optionString .= $comma . \str_replace(',', '', $option);
			$comma = ',';
			}
		$this->optionValues = $optionString;

		return $this;
		}
	}
