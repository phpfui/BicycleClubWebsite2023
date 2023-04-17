<?php

namespace App\Table;

class PhotoFolder extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PhotoFolder::class;

	public function folderCount(\App\Record\PhotoFolder $photoFolder) : int
		{
		$sql = 'select count(*) from photo where photoFolderId=?';
		$photos = (int)\PHPFUI\ORM::getValue($sql, [$photoFolder->photoFolderId]);
		$sql = 'select count(*) from photoFolder where parentId=?';

		return (int)\PHPFUI\ORM::getValue($sql, [$photoFolder->photoFolderId]) + $photos;
		}

	public static function getFolders(int $photoFolderId) : array
		{
		$folders = [];

		while ($photoFolderId)
			{
			$folder = new \App\Record\PhotoFolder($photoFolderId);

			if (! $folder->empty())
				{
				$folders[$photoFolderId] = $folder->photoFolder;
				}
			$photoFolderId = $folder->parentId;
			}

		return \array_reverse($folders, true);
		}
	}
