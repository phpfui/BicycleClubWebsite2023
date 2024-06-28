<?php

namespace App\Table;

class Folder extends \PHPFUI\ORM\Table
	{
	protected static string $className = \App\Record\Folder::class;

	/**
	 * @return array<int,string>
	 */
	public static function getParentFolders(int $folderId) : array
		{
		$folders = [];

		while ($folderId)
			{
			$folder = new \App\Record\Folder($folderId);

			if (! $folder->empty())
				{
				$folders[$folderId] = $folder->name;
				}
			$folderId = $folder->parentFolderId;
			}

		return \array_reverse($folders, true);
		}
	}
