<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \App\Enum\Admin\PublicPageVisibility $hidden
 */
class PublicPage extends \App\Record\Definition\PublicPage
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'hidden' => [\PHPFUI\ORM\Enum::class, \App\Enum\Admin\PublicPageVisibility::class],
	];

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
