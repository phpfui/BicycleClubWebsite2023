<?php

namespace PHPFUI\Translation;

/**
 * To translate a string, call the trans function passing either the app native language, or tokens.
 * String starting with a period are assumed to be a token string ie '.welcome', '.welcome.footer', '.welcome.body'
 * Other strings are assumed the base language
 *
 * Tokened translations can be more effectively chunked, cached and managed directly in PHP
 * Base language translations are stored in one big array, meaning everything is loaded into memory
 *
 * Translator supports pluralization similar with Laravel, but with the count being passed in the variable array with an index of 'count'
 */
class Translator
	{
	protected static string $baseLocale = '';

	protected static string $directory = '.';

	protected static ?\PHPFUI\Translation\Tree $loadedTranslations = null;

	protected static string $locale = '';

	protected static ?\PHPFUI\Translation\MissingLogger $missing = null;

	/**
	 * @var ?array<string,array<mixed>> $nativeTranslations
	 */
	protected static ?array $nativeTranslations = null;

	protected static ?\PHPFUI\Translation\ServiceInterface $serviceInterface = null;

	/**
	 * Resets the cache, used by unit testing, as normally the app will not change languages for a single script, where as unit testing will.
	 */
	public static function clearCache() : void
		{
		self::$loadedTranslations = null;
		self::$nativeTranslations = null;
		}

	/**
	 * The base local is the native language of the app where some of the translation is stored as actual language fragments.
	 */
	public static function getBaseLocale() : string
		{
		return self::$baseLocale;
		}

	/**
	 * Returns an plain array of languages that have been installed by the user.
	 *
	 * @return array<string>
	 */
	public static function getInstalledLanguages() : array
		{
		$languages = [];

		$iterator = new \DirectoryIterator(self::$directory);

		foreach ($iterator as $item)
			{
			if ($item->isDir() && ! $item->isDot())
				{
				$languages[] = $item->getFilename();
				}
			}

		return $languages;
		}

	/**
	 * Returns the current translation directory that should contain generated files with user strings
	 */
	public static function getTranslationDirectory() : string
		{
		return self::$directory;
		}

	/**
	 * Loads a translation chuck from the file system for the specified directory and file.
	 *
	 * @return array<string,array<string>>
	 */
	public static function load(string $directory, string $file) : array
		{
		$file = $directory . '/' . $file . '.php';

		if (! \file_exists($file))
			{
			return [];
			}

		return include $file;
		}

	/**
	 * Set the base locale. Normally called once in the primary include file for a script.
	 */
	public static function setBaseLocale(string $baseLocale = '') : void
		{
		self::$baseLocale = $baseLocale;
		}

	/**
	 * Set the locale for the translations to return
	 *
	 * @param string $locale can be any string, but the following are reserved:
	 * - invisible returns empty string for everything, useful for debugging to see untranslated text
	 * - TRANS returns 'TRANS' string, useful for debugging
	 * - RAW returns the actual string passed with no pluralization
	 * - a blank locale will not preform a translation and returns the actual string passed to trans with pluralization enabled
	 *
	 * Other valid locales would be returned by getInstalledLanguages
	 */
	public static function setLocale(string $locale = '') : void
		{
		self::$locale = $locale;
		}

	/**
	 * Set the translation directory. Normally called once in the primary include file for a script.
	 */
	public static function setTranslationDirectory(string $directory = '.') : void
		{
		self::$directory = $directory;
		}

	/**
	 * If a translation string is not found, the "missing" method will be called on this object. Great for saving missing translations for later translation.
	 */
	public static function setTranslationMissing(?\PHPFUI\Translation\MissingLogger $missing = null) : void
		{
		self::$missing = $missing;
		}

	/**
	 * Sets a translation service that is passsed the string to translate and a local. The service should not provide any pluralization.
	 */
	public static function setTranslationService(?\PHPFUI\Translation\ServiceInterface $serviceInterface = null) : void
		{
		self::$serviceInterface = $serviceInterface;
		}

	/**
	 * Translates a string based on the current locale
	 *
	 * @param  string               $text      to be translated, can be chunked (.modules.general) or native (General Modules)
	 * @param  array<string,mixed>  $variables associative array of variables to substitute in returned translation string. Pass an index of 'count' for pluralization support.
	 *
	 * @return string               of translated text
	 */
	public static function trans(string $text, array $variables = []) : string
		{
		switch (self::$locale)
			{
			case 'invisible':
				return '';

			case 'TRANS':
				return 'TRANS';

			case 'RAW':
				return $text;

			case '':
				$translation = self::getTranslation($text);

				return self::processVariables($translation, $variables);
			}

		// avoid empty translations
		if ('' === $text)
			{
			return '';
			}

		$translation = self::getTranslation($text);

		return self::processVariables($translation, $variables);
		}

	/**
	 * @param array<string, string> $variables
	 *
	 * @return array<string>
	 */
	protected static function getKeys(array $variables) : array
		{
		$retVal = [];

		foreach ($variables as $key => $value)
			{
			$retVal[] = ':' . $key;
			}

		return $retVal;
		}

	/**
	 * Get the correct translation, but don't pluralize or substitute
	 */
	protected static function getTranslation(string $text) : string
		{
		$directory = self::$directory . '/' . self::$locale;

		if ('.' == $text[0])
			{
			if (self::$serviceInterface)
				{
				return self::$serviceInterface->translate($text, self::$locale);
				}

			$parts = \explode('.', $text);
			// remove the empty first part
			\array_shift($parts);

			if (! self::$missing)
				{
				self::$missing = new \PHPFUI\Translation\MissingLogger();
				}

			if (! self::$loadedTranslations)
				{
				self::$loadedTranslations = new \PHPFUI\Translation\Tree('baseChunks', $directory);
				}
			$directory .= '/chunked';

			return self::$loadedTranslations->lookup($parts, $directory) ?? self::$missing->missing($text, self::$baseLocale);
			}

		// we are in the base language, just return it, no need to translate
		if (self::$baseLocale == self::$locale)
			{
			return $text;
			}

		if (self::$serviceInterface)
			{
			return self::$serviceInterface->translate($text, self::$locale);
			}

		if (! \is_array(self::$nativeTranslations))
			{
			self::$nativeTranslations = self::load($directory, 'native');
			}

		if (! self::$missing)
			{
			self::$missing = new \PHPFUI\Translation\MissingLogger();
			}

		return self::$nativeTranslations[$text] ?? self::$missing->missing($text, self::$baseLocale);
		}

	/**
	 * Pluralize the text.
	 *
	 * Different sections are divided by the vertical bar (|) character.  If you need '|' in your text use the HTML entity &verbar;
	 * $count of 0 or less will select the first section.
	 * $count higher than the last section will return the last section.
	 * Other counts will select the number section, so 1 would return the 'one' section in the string 'zero|one|two'
	 * You can use the [first,last] notation at the start of a section to specify a matching range for the section.
	 * * is a wild card for matching any count.  For example '[0]There are no brands|[1,9]There are under ten brands|[10,99]There are under 100 brands|[*]There are hundreds of brands'
	 */
	protected static function pluralize(string $text, int $count) : string
		{
		$parts = \explode('|', $text);

		if (1 == \count($parts))
			{
			return $text;
			}

		// do any start with ranges? [
		$counts = [];

		foreach ($parts as $index => $part)
			{
			if ('[' == $part[0])
				{
				$end = \strpos($part, ']');

				if (false === $end)
					{
					throw new \PHPFUI\Translation\Exception(__METHOD__ . ': Translation part "' . $part . '" does not contain end ]');
					}
				// get inner range
				$range = \substr($part, 1, $end - 1);
				// remove range for user consumption
				$parts[$index] = \substr($part, $end + 1);
				// get the parts out of it
				$counts[$index] = \explode(',', $range);
				}
			else
				{
				$counts[$index] = [$index];
				}
			}

		foreach ($counts as $index => $countArray)
			{
			foreach ($countArray as $countValue)
				{
				if ($countValue < 0)
					{
					$countValue = 0;
					}

				if ('*' === $countValue || $countValue >= $count)
					{
					// exact match on count, use this one
					return $parts[$index];
					}
				}
			}

		// no range matches, so lets just return a reasonable guess
		if ($count < \count($parts))
			{
			if ($count < 0)
				{
				$count = 0;
				}

			return $parts[$count];
			}

		// return the highest part
		return \array_pop($parts);
		}

	/**
	 * Replace variables and perform pluralization if the 'count' index is defined in $variables array, then pluralization will be invoked.
	 *
	 * @param array<string,mixed> $variables
	 */
	protected static function processVariables(string $text, array $variables) : string
		{
		if (! \count($variables))
			{
			return $text;
			}

		if (isset($variables['count']) && \is_int($variables['count']))
			{
			$text = self::pluralize($text, $variables['count']);
			}

		return \str_replace(self::getKeys($variables), $variables, $text);
		}
	}
