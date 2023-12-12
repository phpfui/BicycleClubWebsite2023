<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\PhotoTag> $PhotoTagChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\PhotoComment> $PhotoCommentChildren
 */
class Photo extends \App\Record\Definition\Photo
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'PhotoCommentChildren' => [\PHPFUI\ORM\Children::class, \App\Table\PhotoComment::class],
		'PhotoTagChildren' => [\PHPFUI\ORM\Children::class, \App\Table\PhotoTag::class],
	];

	public function delete() : bool
		{
		$fileModel = new \App\Model\PhotoFiles();
		$fileModel->delete((string)$this->photoId);

		return parent::delete();
		}

	public function getFullPath() : string
		{
		$fileModel = new \App\Model\PhotoFiles();

		return $fileModel->get($this->photoId . $this->extension);
		}

	public function getImage() : \PHPFUI\Image
		{
		$fileName = $this->getFullPath();

		if (! \file_exists($fileName))
			{
			$file = new \App\Model\ImageFiles();
			$settingTable = new \App\Table\Setting();

			return $file->getImg($settingTable->value('clubLogo'));
			}
		$fileTime = \filemtime($fileName);

		return new \PHPFUI\Image('/Photo/image/' . $this->photoId . '-' . $fileTime, $this->photo ?: 'photo');
		}
	}
