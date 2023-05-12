<?php

namespace App\Model;

class ReleaseNotes extends \App\Model\File
	{
	public function __construct()
		{
		parent::__construct('../files/releaseNotes');
		}

	public function getHighestRelease() : string
		{
		$highestVersionNumber = '1.0.0';

		foreach ($this->getAll('*.md') as $file)
			{
			$versionNumber = \str_replace(['V', '.md'], '', $file);

			if (\version_compare($versionNumber, $highestVersionNumber) > 0)
				{
				$highestVersionNumber = $versionNumber;
				}
			}

		return $highestVersionNumber;
		}
	}
