<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class File extends \App\Record\Definition\File
	{
	public function delete() : bool
		{
		$fileModel = new \App\Model\FileFiles();
		$fileModel->delete($this->fileId);

		return parent::delete();
		}
	}
