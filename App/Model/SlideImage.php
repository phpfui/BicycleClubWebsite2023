<?php

namespace App\Model;

class SlideImage extends \App\Model\ThumbnailImageFiles
	{
	public function __construct(\App\Record\Slide $slide)
		{
		parent::__construct('images/slideShow', 'slideId', $slide->toArray());
		}

	public function getImg() : \PHPFUI\Image
		{
		if ($this->item['photoId'])
			{
			$photo = new \App\Record\Photo($this->item['photoId']);
			$photo->photo = $this->item['caption'];

			return $photo->getImage();
			}

		return $this->getPhotoImg($this->item['caption']);
		}
	}
