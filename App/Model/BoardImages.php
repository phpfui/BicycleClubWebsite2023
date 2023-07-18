<?php

namespace App\Model;

class BoardImages extends \App\Model\ThumbnailImageFiles
	{
	/**
	 * @param array<string,mixed> $item
	 */
	public function __construct(array $item = [])
		{
		parent::__construct('images/board', 'memberId', $item);
		}
	}
