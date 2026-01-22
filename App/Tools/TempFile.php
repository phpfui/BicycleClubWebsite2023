<?php

namespace App\Tools;

class TempFile implements \Stringable
	{
	// @mago-expect lint:property-type
	/** @var ?resource */
	protected $handle = null;

	protected string $name = '';

	public function __construct(string $name = 'TMP')
		{
		$this->name = \tempnam(\sys_get_temp_dir(), $name);

		if (empty($this->name))
			{
			throw new \Exception('Unable to create temp file (prefix: $name) in ' . self::class);
			}
		}

	public function __destruct()
		{
		$this->close();
		\App\Tools\File::unlink($this->name);
		}

	public function __toString() : string
		{
		return $this->name;
		}

	public function close() : static
		{
		if ($this->handle)
			{
			\fclose($this->handle);
			$this->handle = null;
			}

		return $this;
		}

	/**
	 * @return false|resource
	 */
	public function open(string $flags = 'w') // @mago-expect lint:return-type
		{
		$this->handle = \fopen($this->name, $flags);

		return $this->handle;
		}
	}
