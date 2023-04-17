<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class PublicPage extends \App\Record\Definition\PublicPage
	{
	public function clean() : static
		{
		$this->url = '/' . \preg_replace('/[^0-9a-zA-Z_]/', '', $this->url);

		return $this;
		}
	}
