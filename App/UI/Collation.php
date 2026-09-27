<?php

namespace App\UI;

class Collation extends \PHPFUI\Input\Select
	{
	public function __construct(string $collation, string $charset, string $name = 'collation', string $label = 'Collation')
		{
		parent::__construct($name, $label);
		$this->addOption('Server Default', '', '' == $collation);
		$collations = \PHPFUI\ORM::getRows('SHOW COLLATION');

		foreach ($collations as $row)
			{
			if ($row['Charset'] == $charset)
				{
				$this->addOption($row['Collation'], $row['Collation'], $collation == $row['Collation']);
				}
			}
		}
	}
