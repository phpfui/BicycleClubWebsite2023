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
			return new \PHPFUI\Image('/Photo/image/' . $this->item['photoId'], $this->item['caption']);
			}

		return $this->getPhotoImg($this->item['caption']);
		}
	}
