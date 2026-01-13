<?php

namespace App\Model;

class FileBackup
	{
	private string $masterDir;

	public function __construct()
		{
		$this->masterDir = PROJECT_ROOT . '/../Backup-' . $_SERVER['HTTP_HOST'];

		\App\Tools\File::mkdir($this->masterDir, 0o755, true);
		}

	public function run(string $rootDirectory, string $subDirectory) : void
		{
		$dest = $this->masterDir . '/' . $subDirectory;

		\App\Tools\File::mkdir($dest, 0o755, true);
		$source = $rootDirectory . '/' . $subDirectory;
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
				\App\Tools\File::mkdir($file, 0o755, true);
				}
			else
				{
				$fromFile = \str_replace('\\', '/', $item);
				$toFile = \str_replace('\\', '/', $file);

				if (! \file_exists($toFile) || \filemtime($toFile) < \filemtime($fromFile))
					{
					\copy($fromFile, $toFile);
					}
				}
			}
		}
	}
