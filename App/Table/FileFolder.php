<?php

namespace App\Table;

class FileFolder extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\FileFolder::class;

	public function folderCount(\App\Record\FileFolder $fileFolder) : int
		{
		$sql = 'select count(*) from file where fileFolderId=?';
		$files = (int)\PHPFUI\ORM::getValue($sql, [$fileFolder->fileFolderId]);
		$sql = 'select count(*) from fileFolder where parentFolderId=?';

		return (int)\PHPFUI\ORM::getValue($sql, [$fileFolder->fileFolderId]) + $files;
		}

	public static function getFolders(int $fileFolderId) : array
		{
		$folders = [];

		while ($fileFolderId)
			{
			$folder = new \App\Record\FileFolder($fileFolderId);

			if (! $folder->empty())
				{
				$folders[$fileFolderId] = $folder->fileFolder;
				}
			$fileFolderId = $folder->parentFolderId;
			}

		return \array_reverse($folders, true);
		}
	}
