<?php

namespace App\Settings;

/**
 * Get settings file.  File should be a PHP file returning an array of key / value pairs.  Key is
 * the variable to be set in the class to the value provided.
 *
 * @author BWells (8/5/2020)
 */
abstract class Settings
	{
	private string $fileName = 'NOT FOUND';

	// @var array<string, mixed>
	private array $settings = [];

	public function __construct(private string $serverName = '')
		{
		if ('' == $this->serverName)
			{
			$this->serverName = $_SERVER['SERVER_NAME'] ?? '';
			}
		}

	public function __call(string $name, array $args)
		{
		$this->load();

		if (! \str_starts_with($name, 'get'))
			{
			throw new \Exception('Method ' . $name . ' is not defined for ' . static::class);
			}
		$name = \lcfirst(\substr($name, 3));

		return $this->settings[$name] ?? null;
		}

	/**
	 * Allows for $object->field syntax
	 *
	 * Unset fields will return null
	 */
	public function __get(string $field)
		{
		$this->load();

		return $this->settings[$field] ?? null;
		}

	public function __set(string $field, mixed $value)
		{
		$this->load();

		$this->settings[$field] = $value;
		}

	/**
	 * Add fields
	 *
	 * @param array<string, mixed> $fields key / value field pairs that will be added to the existing settings
	 */
	public function addFields(array $fields) : static
		{
		$this->settings = \array_merge($this->settings, $fields);

		return $this;
		}

	public function empty() : bool
		{
		$this->load();

		return empty($this->settings);
		}

	// @return array<string, mixed>
	public function getFields() : array
		{
		$this->load();

		return $this->settings;
		}

	public function getLoadedFileName() : string
		{
		return $this->fileName;
		}

	public function save() : bool
		{
		if (! $this->settings)
			{
			return false;
			}
		$parts = \explode('\\', static::class);
		$className = \array_pop($parts);

		if (! empty($this->serverName))
			{
			$fileName = $this->getFileName($this->serverName . '/' . $className);
			}
		else
			{
			$fileName = $this->getFileName($className);
			}
		$parts = \explode('/', $fileName);
		\array_pop($parts);
		$dir = \implode('/', $parts);

		\App\Tools\File::mkdir($dir, recursive: true);
		\file_put_contents($fileName, "<?php\nreturn " . \var_export($this->settings, true) . ';');

		return true;
		}

	/**
	 * Set the fields
	 *
	 * @param array<string, mixed> $fields key / value field pairs that will now be valid fields
	 */
	public function setFields(array $fields) : static
		{
		$this->settings = $fields;

		return $this;
		}

	private function getFileName(string $fileName) : string
		{
		if (false == \strpos($fileName, '.php'))
			{
			$fileName .= '.php';
			}

		return PROJECT_ROOT . '/config/' . $fileName;
		}

	/**
	 * Load settings file from config directory in project root. The following is the file search order:
	 *
	 * - Server name directory, class base name.
	 * - If server name file is not found, then defaults to class name in config directory
	 */
	private function load() : void
		{
		if ($this->settings)
			{
			return;
			}
		$parts = \explode('\\', static::class);
		$className = \array_pop($parts);

		if (! empty($this->serverName))
			{
			$this->settings = $this->loadFile($this->serverName . '/' . $className);

			if ($this->settings)
				{
				return;
				}
			}

		$this->settings = $this->loadFile($className);
		}

	private function loadFile(string $fileName) : array
		{
		$fileName = $this->getFileName($fileName);

		if (\file_exists($fileName))
			{
			if (\function_exists('opcache_invalidate'))
				{
				\opcache_invalidate($fileName, true);
				}

			$this->fileName = $fileName;

			return include $fileName;
			}

		return [];
		}
	}
