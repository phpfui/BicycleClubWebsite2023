<?php

namespace App\View;

abstract class Folder
	{
	/**
	 * Get standard folder breadcrumbs
	 *
	 * @param string $url / and $folder->folderId will be appended
	 */
	public function getBreadCrumbs(string $url, \App\Record\Folder $folder, \App\Record\File | \App\Record\Photo|null $item = null) : \PHPFUI\BreadCrumbs
		{
		$breadCrumbs = new \PHPFUI\BreadCrumbs();

		$folders = \App\Table\Folder::getParentFolders($folder->folderId);

		$breadCrumbs->addCrumb('All', $url);

		foreach ($folders as $folderId => $name)
			{
			$link = '';

			if ($folder->folderId != $folderId || $item)
				{
				$link = $url . '/' . $folderId;
				}
			$breadCrumbs->addCrumb($name, $link);
			}

		if ($item)
			{
			$breadCrumbs->addCrumb($item->description);
			}

		return $breadCrumbs;
		}
	}
