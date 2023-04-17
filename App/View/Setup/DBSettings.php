<?php

namespace App\View\Setup;

class DBSettings extends \PHPFUI\Container
	{
	public function __construct(private readonly \PHPFUI\Page $page, \App\Settings\DB $settings, \App\View\Setup\WizardBar $wizardBar)
		{
		$submit = new \PHPFUI\Submit('Save');
		$form = new \PHPFUI\Form($this->page);
		$fields = [
			'host' => '',
			'user' => '',
			'password' => 'x',
			'dbname' => '',
			'port' => 0,
		];

		$extraFields = ['stage' => 1, 'setup' => true, ];

		$pdo = $settings->getPDO();
		$error = $settings->getError();

		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
			{
			$settings->setFields(\array_merge(\array_intersect_key($_POST, \array_merge($fields, ['driver' => ''])), $extraFields));
			$settings->save();

			if (! $error)
				{
				\PHPFUI\Session::setFlash('success', 'Database connected');
				}
			else
				{
				\PHPFUI\Session::setFlash('alert', "Can't connect to database {$settings->dbname}. Error: " . $error);
				}
			$this->page->redirect('/Config/wizard/' . $settings->stage);

			return;
			}

		$this->add(new \PHPFUI\Header('Database Settings', 4));

		$submit->addClass($error ? 'warning' : 'success');
		$wizardBar->nextAllowed(! $error);
		$wizardBar->addButton($submit);
		$form->add($wizardBar);

		$info = new \PHPFUI\Callout('info');
		$info->add('You must specify the database connection strings. Your hosting service should provide them');
		$form->add($info);

		$fieldSet = new \PHPFUI\FieldSet('Database Connection Strings');
		$dbType = new \PHPFUI\Input\Select('driver', 'Database Driver');
		$dbType->addOption('MySQL / MariaDB', 'mysql', 'mysql' == $settings->driver);
		$fieldSet->add($dbType);

		foreach ($fields as $field => $type)
			{
			if (\is_string($type))
				{
				$input = new \PHPFUI\Input\Text($field, $field, $settings->{$field});
				}
			else
				{
				$input = new \PHPFUI\Input\Number($field, $field, $settings->{$field});
				}
			$input->setRequired(! $type);
			$fieldSet->add($input);
			}
		$form->add($fieldSet);
		$this->add($form);
		}
	}
