<?php

namespace App\View\System;

class DBSettings
	{
	private const string COPY = 'Copy';

	private const string SAVE = 'Save';

	private const string TEST = 'Test';

	private bool $redirect = false;

	public function __construct(private readonly \App\View\Page $page, private \App\Settings\DB $settings)
		{
		$post = $_POST;

		if (isset($post[self::TEST]) && \App\Model\Session::checkCSRF())
			{
			$this->setSettings($this->settings, $post);

			if ($post['create'] && 'sqlite' == $this->settings->driver)
				{
				$this->settings->dbname = \str_replace('\\', '/', $this->settings->dbname);
				$dbname = PROJECT_ROOT . '/data/sqlite/' . $this->settings->dbname;
				$dbname = \str_replace('//', '/', $dbname);

				if (\file_exists($dbname))
					{
					\App\Model\Session::setFlash('alert', $this->settings->dbname . ' file already exists.');
					$post['tested'] = $this->settings->driver;
					}
				else
					{
					if (false !== \strpos($this->settings->dbname, '/'))
						{
						$directory = \substr($dbname, 0, \strrpos($dbname, '/'));
						\mkdir($directory, recursive:true);
						}
					$file = \fopen($dbname, 'w');

					if ($file)
						{
						\fclose($file);
						\App\Model\Session::setFlash('success', $this->settings->dbname . ' file has been created.');
						$post['tested'] = $this->settings->driver;
						}
					else
						{
						\App\Model\Session::setFlash('alert', $this->settings->dbname . ' file could not be created.');
						$post['tested'] = false;
						}
					}
				}
			else
				{
				$pdo = $this->settings->getPDO();

				$post['tested'] = false;

				if ($pdo)
					{
					\App\Model\Session::setFlash('success', 'SQL connect is valid');
					$post['tested'] = $this->settings->driver;
					}
				}
			\App\Model\Session::setFlash('data', $post);

			$this->page->redirect();
			$this->redirect = true;
			}
		elseif (isset($post[self::COPY]) && \App\Model\Session::checkCSRF())
			{
			$oldSettings = clone $this->settings;
			$this->setSettings($this->settings, $post);
			$toPDO = $this->settings->getPDO();

			if ($toPDO)
				{
				$this->copyDB($oldSettings, $this->settings);
				}
			else
				{
				\App\Model\Session::setFlash('alert', 'SQL settings NOT saved. Please check your settings.');
				}

			\App\Model\Session::setFlash('data', $post);
			$this->page->redirect();
			$this->redirect = true;
			}
		elseif (isset($post[self::SAVE]) && \App\Model\Session::checkCSRF())
			{
			$this->setSettings($this->settings, $post);
			$pdo = $this->settings->getPDO();

			if ($pdo)
				{
				$this->settings->save();
				\App\Model\Session::setFlash('success', 'SQL settings saved. Please check that the website works as expected.');
				}
			else
				{
				\App\Model\Session::setFlash('alert', 'SQL settings NOT saved. Please check your settings.');
				}

			\App\Model\Session::setFlash('data', $post);
			$this->page->redirect();
			$this->redirect = true;
			}
		}

	public function edit() : ?\PHPFUI\Tabs
		{
		if ($this->redirect)
			{
			return null;
			}

		$data = \App\Model\Session::getFlash('data');

		if (empty($data))
			{
			$data = $_POST;
			}

		if (empty($data))
			{
			$data = $this->settings->getFields();
			}

		$failed = ! ($data['tested'] ?? false);
		$tabs = new \PHPFUI\Tabs();

		$mysqlActive = 'mysql' == $data['driver'];
		$sqliteActive = 'sqlite' == $data['driver'];
		$pgsqlActive = 'pgsql' == $data['driver'];

		$tabs->addTab($this->getUserName('mysql'), $this->getForm($this->mySql($data), $data['tested'] ?? false, 'mysql'), $mysqlActive);
		$tabs->addTab($this->getUserName('sqlite'), $this->getForm($this->sqlite($data), $data['tested'] ?? false, 'sqlite'), $sqliteActive);
		$tabs->addTab($this->getUserName('pgsql'), $this->getForm($this->postgre($data), $data['tested'] ?? false, 'pgsql'), $pgsqlActive);

		return $tabs;
		}

	private function copyDB(\App\Settings\DB $oldSettings, \App\Settings\DB $newSettings) : void
		{
		$settingTable = new \App\Table\Setting();
		$settingTable->save('maintenanceMode', 1);
		$dbCopier = new \App\Model\DBCopier($oldSettings);
		$dbCopier->createTables($newSettings);
		$dbCopier->copyTables($newSettings);
		$newSettings->save();
		$settingTable->save('maintenanceMode', 0);
		\PHPFUI\ORM::useConnection(\PHPFUI\ORM::addConnection($oldSettings->getPDO(), __CLASS__));
		$settingTable->save('maintenanceMode', 0);
		\App\Model\Session::setFlash('success', "Database migrated to {$newSettings->driver}:{$newSettings->dbname}");
		}

	private function getForm(\PHPFUI\Container $fields, bool | string $tested, string $driver) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);

		$form->add($fields);

		$loading = new \App\UI\Loading();
		$form->add($loading->addClass('hide'));
		$file = $this->settings->getLoadedFileName();
		$callout = new \PHPFUI\Callout('alert');
		$callout->add('<b>Warning:</b> Changing settings on this page can take the website offline and force you to SSH into the server to correct. ');
		$callout->add('Make sure you have server SSH access and know where the database settings live (');
		$callout->add("<i><b>{$file}</b></i>");
		$callout->add(') and are able to use basic file and editing commands in Linux.');
		$form->add($callout);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$save = new \PHPFUI\Submit(self::SAVE, self::SAVE);
		$save->setDisabled($tested !== $driver);
		$buttonGroup->add($save);

		if ($tested === $driver && $this->settings->driver !== $driver)
			{
			$COPY = new \PHPFUI\Submit(self::COPY . ' from ' . $this->getUserName($this->settings->driver), self::COPY);
			$COPY->addClass('warning');
			$COPY->addAttribute('onclick', '$(\'#' . $loading->getId() . '\').removeClass(\'hide\')');
			$buttonGroup->add($COPY);
			}
		$buttonGroup->add(new \PHPFUI\Submit(self::TEST, self::TEST)->addClass('success'));
		$form->add($buttonGroup);

		return $form;
		}

	private function getUserName(string $driver) : string
		{
		$names = ['sqlite' => 'SQLite', 'mysql' => 'MySQL / MariaDB', 'pgsql' => 'PostGre SQL'];

		return $names[$driver];
		}

	/**
	 * @param array<string,string> $data
	 */
	private function mySql(array $data) : \PHPFUI\Container
		{
		$form = new \PHPFUI\Container();

		if ('mysql' === $this->settings->driver)
			{
			$callout = new \PHPFUI\Callout('info');
			$callout->add('MySQL is the currently active database.');
			$form->add($callout);
			}

		$driver = new \PHPFUI\Input\Text('driver', 'Driver', 'mysql')->setAttribute('readonly');
		$form->add($driver);

		$host = new \PHPFUI\Input\Text('host', 'Host Name', $data['host'] ?? '')->setRequired();
		$dbname = new \PHPFUI\Input\Text('dbname', 'Database Name', $data['dbname'] ?? '')->setRequired();
		$port = new \PHPFUI\Input\Number('port', 'Port', $data['port'] ?? '')->setRequired();
		$form->add(new \PHPFUI\MultiColumn($host, $dbname, $port));

		$user = new \PHPFUI\Input\Text('user', 'User Name', $data['user'] ?? '')->setRequired();

		$password = new \PHPFUI\Input\PasswordEye('password', 'Password', $data['password'] ?? '');

		$form->add(new \PHPFUI\MultiColumn($user, $password));

		if ('mysql' == $this->settings->driver)
			{
			$charset = new \App\UI\CharacterSet($this->page, $data['charset'] ?? '');

			$collation = new \App\UI\Collation($data['collation'] ?? '', $data['charset'] ?? '');
			$form->add(new \PHPFUI\MultiColumn($charset, $collation));
			}

		return $form;
		}

	/**
	 * @param array<string,string> $data
	 */
	private function postGre(array $data) : \PHPFUI\Container
		{
		$form = new \PHPFUI\Container();

		if ('pgsql' === $this->settings->driver)
			{
			$callout = new \PHPFUI\Callout('info');
			$callout->add('PostGre is the currently active database.');
			$form->add($callout);
			}

		$driver = new \PHPFUI\Input\Text('driver', 'Driver', 'pgsql')->setAttribute('readonly');
		$form->add($driver);

		$host = new \PHPFUI\Input\Text('host', 'Host Name', $data['host'] ?? '')->setRequired();
		$dbname = new \PHPFUI\Input\Text('dbname', 'Database Name', $data['dbname'] ?? '')->setRequired();
		$port = new \PHPFUI\Input\Number('port', 'Port', $data['port'] ?? '')->setRequired();
		$form->add(new \PHPFUI\MultiColumn($host, $dbname, $port));

		$user = new \PHPFUI\Input\Text('user', 'User Name', $data['user'] ?? '')->setRequired();

		$password = new \PHPFUI\Input\PasswordEye('password', 'Password', $data['password'] ?? '');

		$form->add(new \PHPFUI\MultiColumn($user, $password));

		$charset = new \PHPFUI\Input\Hidden('charset', 'UTF8');
		$form->add($charset);

		$collation = new \PHPFUI\Input\Hidden('collation', 'default');
		$form->add($collation);

		return $form;
		}

	/**
	 * @param array<string,string> $values to save to $settings
	 */
	private function setSettings(\App\Settings\DB $settings, array $values) : void
		{
		$settings->driver = $values['driver'] ?? '';
		$settings->dbname = $values['dbname'] ?? '';
		$settings->user = $values['user'] ?? '';
		$settings->password = $values['password'] ?? '';
		$settings->port = (int)($values['port'] ?? '');
		$settings->charset = $values['charset'] ?? '';
		$settings->collation = $values['collation'] ?? '';
		}

	/**
	 * @param array<string,string> $data
	 */
	private function sqlite(array $data) : \PHPFUI\Container
		{
		$form = new \PHPFUI\Container();

		if ('sqlite' === $this->settings->driver)
			{
			$callout = new \PHPFUI\Callout('info');
			$callout->add('SQLite is the currently active database.');
			$form->add($callout);
			}

		$driver = new \PHPFUI\Input\Text('driver', 'Driver', 'sqlite')->setAttribute('readonly');
		$form->add($driver);
		$file = new \PHPFUI\Input\Text('dbname', 'Path to SQLite file relative to ' . PROJECT_ROOT, $data['dbname'] ?? '');
		$form->add($file->setRequired());
		$create = new \PHPFUI\Input\CheckBoxBoolean('create', 'Create a new DB');
		$form->add($create);

		$host = new \PHPFUI\Input\Hidden('host', $data['host'] ?? '');
		$form->add($host);

		$user = new \PHPFUI\Input\Hidden('user', $data['user'] ?? '');
		$form->add($user);

		$password = new \PHPFUI\Input\Hidden('password', $data['password'] ?? '');
		$form->add($password);

		$port = new \PHPFUI\Input\Hidden('port', $data['port'] ?? '');
		$form->add($port);

		$charset = new \PHPFUI\Input\Hidden('charset', 'UTF-8');
		$form->add($charset);

		$collation = new \PHPFUI\Input\Hidden('collation', 'NOCASE');
		$form->add($collation);

		return $form;
		}
	}
