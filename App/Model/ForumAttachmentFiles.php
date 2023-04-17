<?php

namespace App\Model;

class ForumAttachmentFiles extends \App\Model\File
	{
	public function __construct()
		{
		parent::__construct('../files/forum');
		}
	}
