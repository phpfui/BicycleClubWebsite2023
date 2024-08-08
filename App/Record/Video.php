<?php

namespace App\Record;

class Video extends \App\Record\Definition\Video
	{
	public function delete() : bool
		{
		$this->deleteFile();

		return parent::delete();
		}

	public function deleteFile() : bool
		{
		$fileName = $_SERVER['DOCUMENT_ROOT'] . '/video/' . $this->fileName;
		$this->fileName = '';

		return \App\Tools\File::unlink($fileName);
		}
	}
