<?php

namespace App\Model;

class NewsletterFiles extends \App\Model\File
	{
	public function __construct(protected \App\Record\Newsletter $newsletter)
		{
		parent::__construct('../files/newsletters');
		}

	public function getPrettyFileName() : string
		{
		$settings = new \App\Table\Setting();
		$abbrev = $settings->value('clubAbbrev');
		$name = $settings->value('newsletterName');
		$file = "{$abbrev}{$name}" . \App\Tools\Date::formatString('Ym', $this->newsletter->date) . '.pdf';
		$file = \str_replace(' ', '_', $file);
		$prettyName = '';
		$len = \strlen($file);

		for ($i = 0; $i < $len; ++$i)
			{
			$char = $file[$i];

			if (\ctype_alnum($char) || \str_contains('_.-', $char))
				{
				$prettyName .= $char;
				}
			}

		return $prettyName;
		}
	}
