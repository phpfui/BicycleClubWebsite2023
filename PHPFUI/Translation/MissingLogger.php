<?php

namespace PHPFUI\Translation;

/**
 * The PHPFUI\Translation\MissingLogger allows a system to somehow record missing translations.
 *
 * The default implementation simply returns the passed translation back, override to add your specific functionality and set with \PHPFUI\Translation\Translator::setTranslationMissing
 *
 * The missing method will only get called when the translation system can not find a predefined translation.
 */
class MissingLogger
	{
	/**
	 * missing is called when a translation can not be found
	 *
	 * @param  string $missing    translation text
	 * @param  string $baseLocale of the system, should be the native app translation language
	 *
	 * @return string             the $missing variable passed into function
	 */
	public function missing(string $missing, string $baseLocale) : string
		{
		return $missing;
		}
	}
