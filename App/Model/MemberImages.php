<?php

namespace App\Model;

class MemberImages extends \App\Model\ThumbnailImageFiles
	{
	public function __construct(array $item = [])
		{
		parent::__construct('../filesimages/board', 'memberId', $item);
		}
	}
