<?php

namespace App\Model;

class ReleaseTag
	{
	final public const VERSION_PREFIX = 'V';

	private string $error = '';

	private bool $isTag = false;

	private string $marketingVersion = '';

	private int $migration = 0;

	private bool $valid = false;

	public function __construct(string $tag)
		{
		if (! \str_contains($tag, '_') && \str_starts_with($tag, self::VERSION_PREFIX))
			{
			$this->error = "{$tag} is not a valid release";

			return;
			}
		$this->isTag = true;

		[$this->marketingVersion, $deployVersion] = \explode('_', $tag);
		[$phpMajor, $phpMinor, $migration] = \explode('.', $deployVersion);
		$this->migration = (int)$migration;

		if (\version_compare(\PHP_VERSION, "{$phpMajor}.{$phpMinor}.0") < 0)
			{
			$this->error = "Release {$this->marketingVersion} needs PHP Version {$phpMajor}.{$phpMinor} or higher to install";

			return;
			}

		$this->valid = true;
		}

	public function getError() : string
		{
		return $this->error;
		}

	public function getMarketingVersion() : string
		{
		return $this->marketingVersion;
		}

	public function getMigration() : int
		{
		return $this->migration;
		}

	public function isTag() : bool
		{
		return $this->isTag;
		}

	public function isValid() : bool
		{
		return $this->valid;
		}
	}
