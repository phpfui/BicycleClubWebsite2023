<?php

namespace App\Model;

class BoardImages extends \App\Model\ThumbnailImageFiles
	{
	public function __construct(array $item = [])
		{
		parent::__construct('images/board', 'memberId', $item);
		}
	}
