<?php

namespace App\Settings;

/**
 * @property string $driver
 * @property string $host
 * @property string $user
 * @property string $password
 * @property string $dbname
 * @property int $port
 * @property int $stage
 * @property bool $setup
 * @property string $charset
 * @property string $collation
 *
 * @method string getDriver()
 * @method string getHost()
 * @method string getUser()
 * @method string getPassword()
 * @method string getDbname()
 * @method int getPort()
 * @method int getStage()
 * @method bool getSetup()
 * @method string getCharset()
 * @method string getCollation()
 */
class DB extends \App\Settings\Settings
	{
	private string $error = '';

	public function getConnectionString() : string
		{
		if ('sqlite' === $this->driver)
			{
			return 'sqlite:' . $this->dbname;
			}

		$connectionString = $this->driver . ':';

		foreach ($this->getFields() as $field => $value)
			{
			if (\in_array($field, ['driver', 'stage', 'setup', 'password', 'user']))
				{
				continue;
				}

			if ($value && \ctype_lower($field))
				{
				$connectionString .= "{$field}={$value};";
				}
			}

		return \rtrim($connectionString, ';');
		}

	public function getError() : string
		{
		return $this->error;
		}

	public function getPDO() : ?\PHPFUI\ORM\Interface\PDOInstance
		{
		$this->error = '';
		$user = $this->getUser();
		$pw = $this->getPassword();

		try
			{
			$pdo = \PHPFUI\ORM\PDO\Factory::get($this->getConnectionString(), $user, $pw);

			if ($pdo->getSqlite())
				{
				$pdo->createFunction('acos', 'acos', 1);	     // @phpstan-ignore-line
				$pdo->createFunction('cos', 'cos', 1);         // @phpstan-ignore-line
				$pdo->createFunction('sin', 'sin', 1);         // @phpstan-ignore-line
				$pdo->createFunction('radians', 'deg2rad', 1); // @phpstan-ignore-line

				return $pdo;
				}

			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
			// set up session to our specifications
			$command = "SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION,ALLOW_INVALID_DATES';";

			if ($this->charset)
				{
				$command .= "SET NAMES '{$this->charset}';";
				}
			$pdo->prepare($command)->execute();
			}
		catch (\PDOException $e)
			{
			$this->error = $e->getMessage();

			return null;
			}

		return $pdo;
		}
	}
