<?php

namespace App\View;

class PhotoTag extends \PHPFUI\HTML5Element
	{
	public function __construct(array $photoTag)
		{
		parent::__construct('div');
		$this->addClass('photoTag');
		$this->add($photoTag['photoTag']);
		$this->setAttribute('draggable', 'true');
		$this->setAttribute('data-count', '0');
		$this->add('<button class="red" onclick="$(this).parent().remove();return false;">&cross;</button>');
		$this->add(new \PHPFUI\Input\Hidden('photoTagId[]', $photoTag['photoTagId'] ?? 0));
		$this->add(new \PHPFUI\Input\Hidden('frontToBack[]', $photoTag['frontToBack'] ?? 1));
		}
	}
