<?php

namespace App\UI;

/**
 * Simple wrapper for Month input fields
 */
class Month extends \PHPFUI\Input\Select
	{
	/**
	 * Construct a Month input
	 *
	 * @param string $name of the field
	 * @param string $label defaults to empty
	 * @param ?string $value defaults to empty
	 */
	public function __construct(string $name, string $label = '', ?string $value = '')
		{
		parent::__construct($name, $label);

		for ($i = 1; $i < 13; ++$i)
			{
			$this->addOption(\date('F', \mktime(12, 12, 12, $i, 1, 2000)), (string)$i, (int)$value == $i);
			}
		}
	}
