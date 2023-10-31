<?php

namespace App\Tools;

/**
 * Various useful functions for manipulating text
 */
class TextHelper extends \PHPFUI\TextHelper
	{
	public static string $urlRegEx = "#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s!()\[\]{}<>'\";:.,!?]))#";

	/**
	 * Truncate html text at a reasonable break point and close all
	 * open html tags.
	 *
	 * @param string $html in html format
	 * @param int $maxChars to display
	 *
	 */
	public static function abbreviate(string $html, int $maxChars) : string
		{
		//find all tags
		$tagPattern = '/(<\/?)([\w]*)(\s*[^>]*)>?|&[\w#]+;/i';  //match html tags and entities
		\preg_match_all($tagPattern, $html, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
		$i = 0;
		//loop through each found tag that is within the $maxChars, add those characters to the len,
		//also track open and closed tags
		// $matches[$i][0] = the whole tag string  --the only applicable field for html enitities
		// IF its not matching an &htmlentity; the following apply
		// $matches[$i][1] = the start of the tag either '<' or '</'
		// $matches[$i][2] = the tag name
		// $matches[$i][3] = the end of the tag
		//$matces[$i][$j][0] = the string
		//$matces[$i][$j][1] = the str offest

		$warnings = $openTags = [];

		while (! empty($matches[$i]) && $matches[$i][0][1] < $maxChars)
			{

			$maxChars = $maxChars + \strlen($matches[$i][0][0]);

			if ('&' == \substr($matches[$i][0][0], 0, 1))
				{
				$maxChars = $maxChars - 1;
				}

			//if $matches[$i][2] is undefined then its an html entity, want to ignore those for tag counting
			//ignore empty/singleton tags for tag counting
			if(! empty($matches[$i][2][0]) && ! \in_array($matches[$i][2][0], ['br', 'img', 'hr', 'input', 'param', 'link']))
				{
				//double check
				if('/' != \substr($matches[$i][3][0], -1) && '/' != \substr($matches[$i][1][0], -1))
					{
					$openTags[] = $matches[$i][2][0];
					}
				elseif($openTags && \end($openTags) == $matches[$i][2][0])
					{
					\array_pop($openTags);
					}
				else
					{
					$warnings[] = "html has some tags mismatched in it:  {$html}";
					}
				}

			$i++;
			}

		$closeTags = '';

		if (! empty($openTags))
			{
			$openTags = \array_reverse($openTags);

			foreach ($openTags as $t)
				{
				$closeTags .= '</' . $t . '>';
				}
			}

		if (\strlen($html) > $maxChars)
			{
			// Finds the last space from the string new length
			$lastWord = \strpos($html, ' ', $maxChars);
			$truncated_html = $html;

			if ($lastWord)
				{
				//truncate with new len last word
				$html = \substr($html, 0, $lastWord);
				//finds last character
				$last_character = (\substr($html, -1, 1));
				//add the end text
				$truncated_html = ('.' == $last_character ? $html : (',' == $last_character ? \substr($html, 0, -1) : $html));
				}
			//restore any open tags
			$truncated_html .= $closeTags;
			}
		else
			{
			$truncated_html = $html;
			}

		return $truncated_html;
		}

	/**
	 * Convert text links into clickable links
	 */
	public static function addLinks(string $text) : string
		{
		$urlDelimiter = '^^^^';
		$textDelimiter = '~~~~';
		$var = '$1';
		$text = \preg_replace(self::$urlRegEx, '<a href="' . $urlDelimiter . $var . $urlDelimiter . '" rel="noopener noreferrer" target="_blank">' . $textDelimiter . $var . $textDelimiter . '</a>', $text);

		// <wbr> escape the displayed part of the url to make it wrap better.
		while ($startPos = \strpos($text, $textDelimiter))
			{
			$startPos += 4;
			$endPos = \strpos($text, $textDelimiter, $startPos);
			$url = \substr($text, $startPos, $endPos - $startPos);
			$text = \str_replace("{$textDelimiter}{$url}{$textDelimiter}", $url, $text);
			}

		// make sure the url has http
		while ($startPos = \strpos($text, $urlDelimiter))
			{
			$startPos += 4;
			$endPos = \strpos($text, $urlDelimiter, $startPos);
			$url = \substr($text, $startPos, $endPos - $startPos);
			$fullUrl = $url;

			if (! \str_starts_with($url, 'http'))
				{
				$fullUrl = 'http://' . $url;
				}
			$text = \str_replace("{$urlDelimiter}{$url}{$urlDelimiter}", $fullUrl, $text);
			}

		return $text;
		}

	public static function addRideLinks(string $content, bool $signedIn) : string
		{
		$removedLinks = [];
		// convert real links into something that is not a link
		$content = self::replaceLinksWithDummies($content, $removedLinks);
		// make any text links into real links
		$content = \App\Tools\TextHelper::addLinks($content);
		// convert any newly converted links
		$content = self::replaceLinksWithDummies($content, $removedLinks);
		// wrap any links via dom
		$content = \App\Tools\TextHelper::wrapLinks($content);

		// replace text links with real links to sign in
		$home = '/Rides/memberSchedule';
		$signIn = 'Sign In';
		// add back the real links with sign in links
		$newLinks = [];

		foreach ($removedLinks as $link => $text)
			{
			if (! $signedIn && \str_contains((string)$link, 'link'))
				{
				$text = $home;
				}
			$newLinks[$link] = $text;
			}

		$content = \str_replace(\array_keys($newLinks), \array_values($newLinks), $content);

		return $content;
		}

	/**
	 * Filter bad stuff out of html for email
	 */
	public static function cleanEmailHtml(string $html) : string
		{
		$html = \App\Tools\TextHelper::unhtmlentities($html);

		$options = [
			'style_pass' => 1,
		];

		$html = \Htmlawed::filter($html, $options);

		return self::fullyPathImages($html);
		}

	/**
	 * Clean up special characters from user input, but leave < and
	 * > so it is real html
	 */
	public static function cleanUserHtml(?string $html) : string
		{
		if (! $html)
			{
			return '';
			}
		$html = \str_replace("\n", '', $html);
		$html = \App\Tools\TextHelper::htmlentities($html);
		$html = \str_ireplace(['&lt;', '&gt;'], ['<', '>'], $html);
		$html = \str_ireplace(['&quot;', '&ldquo;', '&OpenCurlyDoubleQuote;', '&rdquo;', '&rdquor;', '&CloseCurlyDoubleQuote;'], '"', $html);
		$html = \str_ireplace(['&apos;', '&lsquo;', '&OpenCurlyQuote;', '&rsquo;', '&rsquor;', '&CloseCurlyQuote;'], "'", $html);
		$html = \str_ireplace('<p>&nbsp;</p>', '', $html);

		$options = [
			'style_pass' => 1,
		];

		$html = \Htmlawed::filter($html, $options);

		return self::fullyPathImages($html);
		}

	/**
	 * Format a phone number
	 *
	 *
	 */
	public static function formatPhone(?string $phone, ?string $separator = '-') : string
		{
		$len = \strlen($phone ?? '');
		$corrected = '';

		for ($i = 0; $i < $len; ++$i)
			{
			$char = $phone[$i];

			if (\ctype_digit($char))
				{
				$corrected .= $char;
				}
			}
		// remove leading 1's
		$len = \strlen($corrected);

		if ($len)
			{
			while ($len && '1' == $corrected[0])
				{
				$corrected = \substr($corrected, 1);
				$len = \strlen($corrected);
				}

			$pattern = 7 == $len ? [3] : [3, 7];

			foreach ($pattern as $len)
				{
				$corrected = \substr($corrected, 0, $len) . $separator . \substr($corrected, $len);
				}
			}

		return $corrected;
		}

	/**
	 * Convert any relative image links to fully pathed links
	 */
	public static function fullyPathImages(string $html) : string
		{
		$dom = new \voku\helper\HtmlDomParser($html);
		$images = $dom->find('img');

		// @phpstan-ignore-next-line
		if (! \count($images))
			{
			return $html;
			}

		$settingsTable = new \App\Table\Setting();
		$root = $settingsTable->value('homePage');

		foreach ($images as $image)
			{
			$path = $image->getAttribute('src');

			if (! \str_starts_with($path, 'http') && ! \str_starts_with($path, 'data:'))
				{
				$path = \trim($path, ' /.');
				$path = $root . '/' . $path;
				$image->setAttribute('src', $path);
				}
			}

		return "{$dom}";
		}

	/**
	 * return all links in the given text
	 *
	 * @return array<string> of links
	 */
	public static function getLinks(string $text) : array
		{
		$urls = [];
		\preg_match_all(self::$urlRegEx, $text, $urls);

		return $urls[0];
		}

	/**
	 * Replace links in html with a standard link and text. Useful
	 * for obsuring links that may be useful to a user and
	 * redirecting them to a sign in page.
	 *
	 * @param string $html to check
	 * @param string $url to replace
	 * @param string $text to display, defaults to $utl
	 *
	 */
	public static function hideLinks(string $html, string $url, string $text = '') : string
		{
		if (! $text)
			{
			$text = $url;
			}

		return \preg_replace(self::$urlRegEx, "<a href='{$url}'>{$text}</a>", $html);
		}

	/**
	 * Simple string replacement. Replaces thing with corresponding
	 * text in array indexed by value enclosed by '~', case insensitive.
	 *
	 * @param string $text to be searched for ~value~
	 * @param array<string,?string> $values indexed by value inside of ~value~
	 */
	public static function processText(string $text, array $values) : string
		{
		foreach ($values as $key => $value)
			{
			$field = '~' . \strtoupper($key) . '~';
			$text = \str_ireplace($field, $value ?? '', $text);
			}

		return $text;
		}

	/**
	 * Convert to proper case.
	 *
	 * Rules:
	 *  - If already mixed case, done
	 *  - If single case
	 *  	- Lowercase the whole string
	 *  	- Upper case first letter of each word
	 */
	public static function properCase(string $text) : string
		{
		$text = \trim($text);
		$lower = \strtolower($text);
		$upper = \strtoupper($text);

		if ($lower != $text && $upper != $text)
			{
			return $text;
			}

		return \ucwords($lower);
		}

	/**
	 * Replace targets wrapped in '~' with what every your callable
	 * routine would like.
	 *
	 * @param callable $callable function(string).  Should return
	 *  							 text based on parameter
	 */
	public static function replace(string $subject, callable $callable) : string
		{
		$substitute = [];
		$len = \strlen($subject);

		for ($i = 0; $i < $len; ++$i)
			{
			if ('~' == $subject[$i])
				{
				$field = '';

				while ('~' != $subject[++$i])
					{
					$field .= $subject[$i];
					}
				$substitute[] = $field;
				}
			}

		foreach ($substitute as $field)
			{
			$subject = \str_replace("~{$field}~", $callable($field), $subject);
			}

		return $subject;
		}

	/**
	 * Add wrapping into clickable links
	 */
	public static function wrapLinks(string $html) : string
		{
		$dom = new \voku\helper\HtmlDomParser($html);

		foreach ($dom->find('a') as $node)
			{
			$node->innertext = \str_replace(['.', '/'], ['.<wbr>', '/<wbr>'], $node->innertext);
			}

		return "{$dom}";
		}

	private static function replaceLinksWithDummies(string $html, array &$links) : string
		{
		$html = \str_replace('&nbsp;', ' ', $html); // html editor could insert this after a link, which could look like a valid url
		$dom = new \voku\helper\HtmlDomParser($html);
		$counter = \count($links);

		foreach ($dom->find('a') as $node)
			{
			$href = $node->getAttribute('href');

			if (empty($links[$href]))
				{
				$text = 'text' . $counter;
				$link = 'link' . $counter;
				$links[$text] = $node->innertext;
				$links[$link] = $href;
				$node->innertext = $text;
				$node->setAttribute('href', $link);
				++$counter;
				}
			}

		foreach ($dom->find('img') as $node)
			{
			if ($node->hasAttribute('src'))
				{
				$src = $node->getAttribute('src');

				if (empty($links[$src]))
					{
					$link = 'src' . $counter;
					$links[$link] = $src;
					$node->setAttribute('src', $link);
					++$counter;
					}
				}
			}

		return "{$dom}";
		}
	}
