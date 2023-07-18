<?php

namespace App\Model;

class Banner
	{
	private readonly BannerFiles $fileModel;

	public function __construct()
		{
		$this->fileModel = new \App\Model\BannerFiles();
		}

	public function canDelete(int $id) : bool
		{
		return true;
		}

	public function delete(int $id) : void
		{
		if ($this->canDelete($id))
			{
			$banner = new \App\Record\Banner($id);
			$banner->delete();
			$this->fileModel->delete($id);
			}
		}
	}
