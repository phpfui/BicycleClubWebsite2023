<?php

namespace App\Model;

class StoreImages extends \App\Model\ThumbnailImageFiles
	{
	public function __construct(array $item = [])
		{
		parent::__construct('images/storePhotos', 'storePhotoId', $item);
		}

	public function getCarousel(\PHPFUI\Interfaces\Page $page, bool $full = true) : \PHPFUI\SlickSlider
		{
		$orbit = new \PHPFUI\SlickSlider($page);
		$orbit->addSliderAttribute('autoplay', true)->addSliderAttribute('autoplaySpeed', 3000)->addSliderAttribute('arrows', false);

		foreach ($this->getPhotos() as $photo)
			{
			$this->update($photo->toArray());

			if ($full)
				{
				$image = $this->getPhotoImg();
				}
			else
				{
				$image = $this->getThumbnailImg();
				}
			$image->setAttribute('alt', $photo->filename);
			$orbit->addSlide($image);
			}

		return $orbit;
		}

	public function getProductGallery(\PHPFUI\Interfaces\Page $page) : \PHPFUI\HTML5Element
		{
		$pig = new \App\UI\ProductImageGallery($page);
		$photos = $this->getPhotos();
		// no photos, no image
		if (0 == \count($photos))
			{
			return $this->getPhotoImg('Club Logo');
			}
		// one photo, no gallery
		if (1 == \count($photos))
			{
			$this->update($photos->current()->toArray());

			return $this->getPhotoImg();
			}

		foreach ($photos as $photo)
			{
			$this->update($photo->toArray());

			$image = $this->getPhotoImg();
			$image->setAttribute('alt', $photo->filename);
			$thumb = $this->getThumbnailImg();
			$thumb->setAttribute('alt', $photo->filename . ' thumbnail');
			$pig->addImages($image, $thumb);
			}

		return $pig;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\StorePhoto>
	 */
	private function getPhotos() : \PHPFUI\ORM\RecordCursor
		{
		$storePhotoTable = new \App\Table\StorePhoto();
		$storePhotoTable->setWhere(new \PHPFUI\ORM\Condition('storeItemId', $this->item['storeItemId']));
		$storePhotoTable->setOrderBy('sequence');

		return $storePhotoTable->getRecordCursor();
		}
	}
