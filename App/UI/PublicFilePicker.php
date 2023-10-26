<?php

namespace App\UI;

class PublicFilePicker extends \PHPFUI\Input\Select
	{
	public function __construct(string $name, string $label = '', string $fileSpec = 'pdf/*.pdf')
		{
		parent::__construct($name, $label);
		$settingTable = new \App\Table\Setting();
		$selectedFile = $settingTable->value($name);
		$this->addOption('', '', '' == $selectedFile);
		$offset = \strlen(PUBLIC_ROOT) - 1;

		foreach (\glob(PUBLIC_ROOT . $fileSpec) as $file)
			{
			$baseName = \basename($file);
			$file = \substr($file, $offset);
			$this->addOption($baseName, $file, $file == $selectedFile);
			}
		}
	}
