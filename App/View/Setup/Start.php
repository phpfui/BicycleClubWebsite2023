<?php

namespace App\View\Setup;

class Start extends \PHPFUI\Container
	{
	public function __construct(\App\View\Setup\WizardBar $wizardBar)
		{
		$this->add(new \PHPFUI\Header('Welcome to the setup wizard', 4));
		$loaded = \get_loaded_extensions();
		$required = ['curl', 'fileinfo', 'gd', 'gmp', 'intl', 'mbstring', 'exif', 'openssl', 'pdo_mysql', 'xsl', ];
		$missing = \array_diff($required, $loaded);

	if (\count($missing))
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add(new \PHPFUI\Header('The following extensions are not installed', 5));
			$ul = new \PHPFUI\UnorderedList();

			foreach ($missing as $missed)
				{
				$ul->addItem(new \PHPFUI\ListItem($missed));
				}
			$callout->add($ul);
			$callout->add('Please install them before proceeding');
			$this->add($callout);
			}
		else
			{
			$callout = new \PHPFUI\Callout('info');
			$callout->add('This wizard will guide you through setting up the database, basic configuration and importing member data.<br><br>');
			$callout->add('Click the next button below to start');
			$this->add($callout);
			$this->add($wizardBar);
			}
		}
	}
