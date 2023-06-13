<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class PublicPage extends \App\Record\Definition\PublicPage
	{
	public function clean() : static
		{
		if (! \str_starts_with($this->url, '/'))
			{
			$this->url = '/' . $this->url;
			}

		$this->url = \preg_replace('/[^\w\/.]/', '', $this->url);

		return $this;
		}
	}
