<?php

namespace PHPFUI\Translation;

/**
 * The \PHPFUI\Translation\ServiceInterface allows for an external solution (Key Value Store for example) instead of using PHP arrays to look up translations.
 */
interface ServiceInterface
	{
	/**
	 * @param  string $text   that needs to be translated.  No substitutions or plurization needs to be done.
	 * @param  string $locale that of the translation that needs to be returned.
	 *
	 * @return string         translated text
	 */
	public function translate(string $text, string $locale) : string;
	}
