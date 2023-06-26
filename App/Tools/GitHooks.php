<?php

namespace App\Tools;

class GitHooks
	{
	private int $exitStatus = 0;

	private array $installedPackages = ['phpunit', 'phpstan', 'php-cs-fixer'];

	private string $method;

	private readonly \Gitonomy\Git\Repository $repo;

	/** @group SyntaxTest */
	private array $validFiles = ['App' => true, 'oneOffScripts' => true, 'NoNameSpace' => true, 'www' => true, 'conversions' => true];

	public function __construct(string $hook)
		{
		$parts = \explode('/', \str_replace('\\', '/', $hook));
		$hook = \array_pop($parts);
		$parts = \explode('-', $hook);
		$this->method = \array_shift($parts);

		foreach ($parts as $part)
			{
			$this->method .= \ucfirst($part);
			}
		$this->repo = new \Gitonomy\Git\Repository(PROJECT_ROOT);
		}

	public function execute() : string
		{
		foreach ($this->installedPackages as $package)
			{
			if (! \file_exists('vendor/bin/' . $package))
				{
				$this->exitStatus = 1;

				return "Package {$package} was not found.  Try:\n\n  composer install\n";
				}
			}

		if (! \method_exists($this, $this->method))
			{
			$this->exitStatus = 0;

			return '';
			}

		$method = $this->method;
		$retVal = $this->{$method}();
		$this->exitStatus = (int)(\strlen((string)$retVal) > 0);

		return $retVal;
		}

	public function getExitStatus() : int
		{
		return $this->exitStatus;
		}

	/**
	 * Get all the php files from the project we are interested in (our own, not library code)
	 *
	 * @return array<string> of php file names with .php extension
	 */
	private function getPHPFiles() : array
		{
		$currentCommit = $this->repo->getHeadCommit();
		$files = $currentCommit->getDiff()->getFiles();

		$phpFiles = [];

		foreach ($files as $file)
			{
			$fileName = $file->getName();

			if (\str_ends_with((string)$fileName, '.php'))
				{
				$parts = \explode('/', (string)$fileName);

				if (isset($this->validFiles[$parts[0]]))
					{
					$phpFiles[] = $fileName;
					}
				}
			}

		return $phpFiles;
		}

	private function preCommit() : string
		{
		// run php-cs-fixer
		$files = \implode(' ', $this->getPHPFiles());
		$command = "vendor\\bin\\php-cs-fixer fix --config=.php-cs-fixer.dist.php {$files}";
		$this->runShellCommand($command);
		$this->runShellCommand("git add {$files}");

		return '';
		}

	private function prePush() : string
		{
		// run phpstan
		$command = 'vendor\\bin\\phpstan';

		$errors = $this->runShellCommand($command);

		if (\str_contains($errors, 'BicycleClubWebsite'))
			{
			return $errors;
			}

		// run syntax check
		$command = 'vendor\\bin\\phpunit --group SyntaxTest';

		return $this->runShellCommand($command);
		}

	private function runShellCommand(string $command) : string
		{
		$output = [];
		$exitStatus = 1;

		if (false === \exec($command, $output, $exitStatus))
			{
			return "Command {$command} failed";
			}

		return $exitStatus ? \implode("\n", $output) : '';
		}
	}
