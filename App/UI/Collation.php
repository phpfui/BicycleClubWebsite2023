<?php

namespace App\UI;

class Collation extends \PHPFUI\Input\Select
	{
	public function __construct(\App\Settings\DB $settings, string $name = 'collation', string $label = 'Collation')
		{
		parent::__construct($name, $label);
		$this->addOption('Server Default', '', '' == $settings->collation);
		$collations = \PHPFUI\ORM::getRows('SHOW COLLATION');

		foreach ($collations as $row)
			{
			if ($row['Charset'] == $settings->charset)
				{
				$this->addOption($row['Collation'], $row['Collation'], $settings->collation == $row['Collation']);
				}
			}
		}
	}
