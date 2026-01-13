<?php

namespace App\Model;

class Backup
	{
	private readonly string $basePath;

	/** @var array<string> */
	private array $directories = [];

	public function __construct()
		{
		$now = new \DateTime();
		$this->directories[] = $now->format('l');
		$this->directories[] = $now->format('F');
		$this->directories[] = 'week' . ((int)(($now->format('j') - 1) / 7) + 1);
		$this->directories[] = $now->format('Y');
		$this->basePath = PROJECT_ROOT . '/backups/';
		}

	/**
	 * @return string file path of backup
	 */
	public function run(bool $schemaOnly = false, string $baseFileName = 'backup') : string
		{
		foreach ($this->directories as $directory)
			{
			$dir = $this->basePath . $directory;

			\App\Tools\File::mkdir($dir, 0o777, true);
			}
		$backupFilename = $this->basePath . $baseFileName . '.gz';
		\App\Tools\File::unlink($backupFilename);

		$dbSettings = new \App\Settings\DB();
		$settings = [];
		$settings['add-drop-table'] = true;
		$settings['default-character-set'] = 'utf8mb4';
		$settings['compress'] = \Druidfi\Mysqldump\Compress\CompressManagerFactory::GZIP;

		if ($schemaOnly)
			{
			$settings['no-data'] = true;
			$settings['reset-auto-increment'] = true;
			}
		else
			{
			$settings['disable-keys'] = true;
			$settings['extended-insert'] = true;
			$settings['no-autocommit'] = true;
			$settings['single-transaction'] = true;
			}

		$dump = new \Druidfi\Mysqldump\Mysqldump($dbSettings->getConnectionString(), $dbSettings->getUser(), $dbSettings->getPassword(), $settings);
		$dump->start($backupFilename);

		if (! $schemaOnly)
			{
			foreach ($this->directories as $directory)
				{
				$destFilename = "{$this->basePath}{$directory}/{$baseFileName}.zip";

				if (! \copy($backupFilename, $destFilename))
					{
					throw new \Exception("Can't copy {$backupFilename} to {$destFilename}");
					}
				}
			}

		return $backupFilename;
		}
	}
