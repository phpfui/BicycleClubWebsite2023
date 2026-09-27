<?php

namespace App\UI;

class CharacterSet extends \PHPFUI\Input\SelectAutoComplete
	{
	public function __construct(\PHPFUI\Page $page, string $charset, string $name = 'charset', string $label = 'Character Set')
		{
		parent::__construct($page, $name, $label);
		$this->addOption('Server Default', '', '' == $charset);
		$charsets = \PHPFUI\ORM::getRows('SHOW CHARACTER SET');

		foreach ($charsets as $row)
			{
			$this->addOption($row['Charset'] . ' - ' . $row['Description'], $row['Charset'], $charset == $row['Charset']);
			}
		}
	}
