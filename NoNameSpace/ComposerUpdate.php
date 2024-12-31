<?php

class ComposerUpdate
	{
	private string $baseDir = '';

	private array $ignored = [];

	private array $installedVersions = [];

	private string $noNameSpaceDir = 'NoNameSpace/';

	private array $skipFiles = ['changelog', 'changes', 'license', 'conduct', 'contribut', 'upgrad', 'security', 'license', 'bug',  ];

	private string $vendorDir = 'vendor/';

	public function __construct()
		{
		$composerLockFile = $this->vendorDir . '../composer.lock';

		if (\file_exists($composerLockFile))
			{
			$installed = @\json_decode(\file_get_contents($composerLockFile), true);

			foreach (($installed['packages'] ?? []) as $install)
				{
				$packageName = $install['name'];

				foreach ($this->ignored as $ignore)
					{
					if (false !== \str_starts_with($packageName, $ignore))
						{
						continue 2;
						}
					}
				$this->installedVersions[$packageName] = $install['version'];
				}
			}
		}

	public function copyDirectory(string $source, string $dest) : void
		{
		if (! \is_dir($dest))
			{
			\mkdir($dest, 0755, true);
			}
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $item)
			{
			$file = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
			$file = \str_replace('\\', '/', $file);
			$file = \str_replace('//', '/', $file);

			if ($item->isDir())
				{
				if (! \is_dir($file))
					{
					\mkdir($file, 0755, true);
					}
				}
			else
				{
				\copy($item, $file);
				}
			}
		}

	public function copyFileFiltered(string $item, string $file, bool $phpFiles) : bool
		{
		$lcFile = \strtolower($file);

		if ($phpFiles && \str_ends_with($lcFile, '.php'))
			{
			return \copy(\str_replace('\\', '/', $item), \str_replace('\\', '/', $file));
			}

		if (! \str_ends_with($lcFile, '.md'))
			{
			return false;
			}

		foreach ($this->skipFiles as $skip)
			{
			if (\str_contains($lcFile, $skip))
				{
				return false;
				}
			}

		// make sure directory exists
		$dir = \substr($file, 0, \strrpos($file, '/'));

		if (! \is_dir($dir))
			{
			return false;
			}

		if (! \strcasecmp($file, './readme.md'))
			{
			return false;
			}

		return \copy(\str_replace('\\', '/', $item), \str_replace('\\', '/', $file));
		}

	public function copyFiles(string $source, string $dest, bool $phpFiles = true) : void
		{
		if (! \is_dir($dest))
			{
			\mkdir($dest, 0755, true);
			}
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $item)
			{
			$file = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
			$file = \str_replace('\\', '/', $file);
			$file = \str_replace('//', '/', $file);

			if ($item->isDir())
				{
				if ($phpFiles && ! \is_dir($file))
					{
					\mkdir($file, 0755, true);
					}
				}
			else
				{
				$this->copyFileFiltered($item, $file, $phpFiles);
				}
			}
		}

	public function copyPath(string $name, array $sources, string $destDir) : void
		{
		foreach ($sources as $sourceDir)
			{
			//echo $name . ": sourceDir {$sourceDir} => destDir {$destDir}\n";

			if ($destDir)
				{
				$localDestDir = \str_replace('\\', '/', $this->baseDir . $destDir);
				$localDestDir = \substr($destDir, 0, \strlen($localDestDir) - 1);
				$sourceDir = $this->vendorDir . $name . '/' . $sourceDir;
				$this->copyFiles($sourceDir, $localDestDir);
				}
			}

		if ($destDir)
			{
			// copy project .md files
			$this->copyFiles('vendor/' . $name, $destDir, false);
			}
		}

	public function deleteFileInNamespace(string $nameSpace, string $file) : void
		{
		$path = \str_replace('\\', '/', $this->baseDir . $nameSpace);

		if (! \is_dir($path))
			{
			return;
			}
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

		foreach($iterator as $path)
			{
			if ($path->isFile() && $path->getFilename() == $file)
				{
				\unlink($path->getPathname());
				}
			}
		}

	public function deleteNamespace(string $nameSpace) : void
		{
		$path = \str_replace('\\', '/', $this->baseDir . $nameSpace);

		if (! \is_dir($path))
			{
			return;
			}
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

		foreach($iterator as $path)
			{
			if ($path->isFile())
				{
				\unlink($path->getPathname());
				}
			}
		}

	public function setBaseDirectory(string $baseDir) : static
		{
		$this->baseDir = $this->appendSlash($baseDir);

		return $this;
		}

	public function setIgnoredRepos(array $ignored) : static
		{
		$this->ignored = $ignored;

		return $this;
		}

	public function setNoNameSpaceDirectory(string $dir) : static
		{
		$this->noNameSpaceDir = $this->appendSlash($dir);

		return $this;
		}

	public function setVendorDirectory(string $dir) : static
		{
		$this->vendorDir = $this->appendSlash($dir);

		return $this;
		}

	public function update() : void
		{
		$installed = @\json_decode(\file_get_contents($this->vendorDir . '../composer.lock'), true);

		foreach (($installed['packages'] ?? []) as $install)
			{
			$packageName = $install['name'];

			foreach ($this->ignored as $ignore)
				{
				if (false !== \str_starts_with($packageName, $ignore))
					{
					continue 2;
					}
				}

			if (($this->installedVersions[$packageName] ?? '') == $install['version'])
				{
				continue;
				}

			if (isset($install['autoload']))
				{
				$autoload = $install['autoload'];
				$sourceDir = '';

				if (! empty($autoload['files']))
					{
					$files = \str_replace('/', '\\', "vendor\\{$packageName}\\" . \implode(',', $autoload['files']));
					echo "WARNING: Package {$packageName} contains an autoload files section ({$files})\n";
					unset($autoload['files']);
					}

				if (! empty($autoload['psr-4']))
					{
					foreach ($autoload['psr-4'] as $destDir => $sourceDir)
						{
						if (! $sourceDir)
							{
							$sourceDir = '.';
							}

						if (! \is_array($sourceDir))
							{
							$sourceDir = [$sourceDir];
							}
						$this->copyPath($packageName, $sourceDir, $destDir);
						}
					unset($autoload['psr-4']);
					}

				if (! empty($autoload['psr-0']))
					{
					foreach ($autoload['psr-0'] as $destDir => $sourceDir)
						{
						if ($sourceDir)
							{
							$destDir = '.\\';
							}

						if (! \is_array($sourceDir))
							{
							$sourceDir = [$sourceDir];
							}
						$this->copyPath($packageName, $sourceDir, $destDir);
						}
					unset($autoload['psr-0']);
					}

				if (! empty($autoload['classmap']))
					{
					foreach ($autoload['classmap'] as $file)
						{
						$fromDir = 'vendor/' . $packageName . '/' . $file;

						if (\is_file($fromDir))
							{
							$phpFile = \file_get_contents($fromDir);
							$namespacePos = \strpos($phpFile, 'namespace');

							if (false !== $namespacePos)
								{
								$namespacePos += 10;
								$semicolin = \strpos($phpFile, ';', $namespacePos);
								$namespace = \trim(\substr($phpFile, $namespacePos, $semicolin - $namespacePos));
								$targetDir = \str_replace('\\', '/', $namespace) . '/';
								}
							else
								{
								$targetDir = \str_replace('\\', '/', $this->noNameSpaceDir);
								}
							$classPos = \strpos($phpFile, 'class ');

							if (false === $classPos)
								{
								continue;
								}
							$parts = \explode(' ', \substr($phpFile, $classPos + 6));
							$className = \array_shift($parts);
							$parts = \explode("\n", $className);
							$className = \array_shift($parts) . '.php';
							$sourceFile = \str_replace('\\', '/', $fromDir);
							\copy($sourceFile, $targetDir . $className);

							continue;
							}
						$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fromDir));

						foreach ($iterator as $file)
							{
							$fileName = \strtolower($file->getFilename());

							if ($file->isFile() && \str_ends_with($fileName, '.php'))
								{
								$phpFile = \file_get_contents($file->getPathname());
								$namespacePos = \strpos($phpFile, 'namespace');

								if (false === $namespacePos)
									{
									echo "WARNING: Package {$packageName} classmap file {$fileName} has no namespace\n";

									continue;
									}
								$namespacePos += 10;
								$semicolin = \strpos($phpFile, ';', $namespacePos);
								$namespace = \trim(\substr($phpFile, $namespacePos, $semicolin - $namespacePos));
								$sourceFile = \str_replace('\\', '/', $file->getPathname());
								$targetDir = \str_replace('\\', '/', $namespace);

								if (! \is_dir($targetDir))
									{
									\mkdir($targetDir, recursive:true);
									}
								$targetFile = $targetDir . '/' . $file->getFilename();
								\copy($sourceFile, $targetFile);
								}
							}
						}
					unset($autoload['classmap']);
					}
				unset($autoload['exclude-from-classmap']);

				if (\count($autoload))
					{
					echo "Unhandled AUTOLOAD for {$packageName}: \n";
					\print_r($autoload);
					}
				}


//				echo "No AUTOLOAD for $packageName\n";

			}
		}

	private function appendSlash(string $dir) : string
		{
		$dir = \rtrim($dir, '\\');

		if (! \str_ends_with($dir, '/'))
			{
			$dir .= '/';
			}

		return $dir;
		}
	}
