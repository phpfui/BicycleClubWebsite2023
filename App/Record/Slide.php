<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class Slide extends \App\Record\Definition\Slide
	{
	public function delete() : bool
		{
		$model = new \App\Model\SlideImage($this);
		$model->delete($this->slideId);

		return parent::delete();
		}
	}
