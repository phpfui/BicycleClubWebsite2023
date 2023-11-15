<?php

namespace App\Tools;

class Scrapper
	{
	/**
	 * @var array<string>
	 */
	protected array $badLinks = [];

	protected string $currentPage = '';

	/**
	 * @var string[][]
	 *
	 * @psalm-var array{a: array{0: string}, applet: array{0: string}, area: array{0: string}, base: array{0: string}, blockquote: array{0: string}, body: array{0: string}, del: array{0: string}, form: array{0: string}, frame: array{0: string, 1: string}, head: array{0: string}, iframe: array{0: string, 1: string}, img: array{0: string, 1: string, 2: string}, input: array{0: string, 1: string}, ins: array{0: string}, link: array{0: string}, object: array{0: string, 1: string, 2: string, 3: string}, q: array{0: string}, script: array{0: string}}
	 */
	protected array $elements = [
		'a' => ['href'],
		'applet' => ['codebase'],
		'area' => ['href'],
		'base' => ['href'],
		'blockquote' => ['cite'],
		'body' => ['background'],
		'del' => ['cite'],
		'form' => ['action'],
		'frame' => ['longdesc', 'src'],
		'head' => ['profile'],
		'iframe' => ['longdesc', 'src'],
		'img' => ['longdesc', 'src', 'usemap'],
		'input' => ['src', 'usemap'],
		'ins' => ['cite'],
		'link' => ['href'],
		'object' => ['classid', 'codebase', 'data', 'usemap'],
		'q' => ['cite'],
		'script' => ['src'],
	];

	/**
	 * @var array<string>
	 */
	protected array $externalLinks = [];

	/**
	 * @var array<string>
	 */
	protected array $filterPages = [];

	/**
	 * @var array<string,\App\Tools\LinkInfo>
	 */
	protected array $links = [];

	/**
	 * @var string[]
	 *
	 * @psalm-var array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string, 7: string, 8: string, 9: string, 10: string, 11: string, 12: string}
	 */
	protected array $nonTextExtensions = ['.jpg', '.png', '.pdf', '.tif', '.gif', '.exe', '.mpg', '.swf', '.js', '.doc',
		'.docx', '.css', '.ico', ];

	protected int $pageDelay = 0;

	/**
	 * @var array<string>
	 */
	protected array $phpErrors = [];

	/**
	 * @var array<string>
	 */
	protected array $skipPages = [];

	/**
	 * @var int[]
	 *
	 * @psalm-var array{0: int, 1: int, 2: int, 3: int, 4: int}
	 */
	protected array $validResponses = [200, 301, 302, 405, 503];

	protected \WebBrowser $web;

	public function __construct(protected string $base)
		{
		$this->scrape($base);
		$this->web = new \WebBrowser(['extractforms' => true]);
		}

	/**
	 * 	 * Add a simple stripos match to only scan matched urls
	 *
	 */
	public function addPageFilter(string $match) : static
		{
		$this->filterPages[] = $match;

		return $this;
		}

	/**
	 * Skip a specific page
	 */
	public function addSkipPage(string $page) : static
		{
		if (\in_array($page, $this->skipPages))
			{
			return $this;
			}

		$this->skipPages[] = $page;

		return $this;
		}

	/**
	 * 	 * Clear page filters
	 *
	 */
	public function clearPageFilters() : static
		{
		$this->filterPages = [];

		return $this;
		}

	/**
	 * Clear skip pages
	 */
	public function clearSkipPages() : static
		{
		$this->skipPages = [];

		return $this;
		}

	public function execute() : static
		{
		\libxml_use_internal_errors(true);

		do
			{
			$unscrappedLinks = [];

			foreach ($this->links as $link => &$linkInfo)
				{
				if (! $linkInfo->beenScanned())
					{
					$unscrappedLinks[] = $link;
					$linkInfo->scanned();
					}
				}

			foreach ($unscrappedLinks as $link)
				{
				$this->currentPage = $link;

				if ($this->skippedPage($link))
					{
//					$this->report('Skipped', $link);
					}
				elseif ($this->isNotText($link))
					{
//					$this->report('Non Text link', $link);

					if (false === @\file_get_contents($link))
						{
						$references = $this->getLinkReferences($link);

						if ($references)
							{
							$referencer = \end($references);
							}
						else
							{
							$referencer = $this->base;
							}
						$this->badLinks[$link] = $referencer . ' non-text link';
						}
					}
				else
					{
					$this->report('Processing', $link);

					if ($this->pageDelay)
						{
						\usleep($this->pageDelay);
						}
					$result = $this->web->Process($link);

					if ($result['success'])
						{
						if (200 != $result['response']['code'])
							{
							$end = \strpos($link, '?');

							if (false === $end)
								{
								$end = \strlen($link);
								}
							$this->badLinks[\substr($link, 0, $end)] = "Parent {$this->currentPage} status {$result['response']['code']}";
							}
						elseif (! empty($result['body']))
							{
							$dom = new \DOMDocument();
							$html = $this->cleanXDebugClasses($result['body']);
							$dom->loadHTML($html);

							// grab all links in page
							foreach ($this->elements as $tagName => $attributes) // for each tag with links
								{
								foreach ($dom->getElementsByTagName($tagName) as $element) // for each tag found
									{
									foreach ($attributes as $attribute) // look at all the potential link types for that tag type
										{
										$linkToAdd = $element->getAttribute($attribute);
										$linkToAdd = $this->normalizeLink($linkToAdd, $link);

										if (! empty($linkToAdd))
											{
											if (! isset($this->links[$linkToAdd]))
												{
												$linkInfo = new \App\Tools\LinkInfo();
//												$this->report('Adding', $linkToAdd);
//												$parts = explode('/', $link);
//												$keepParts = [];
//												foreach ($parts as $part)
//													{
//													if (! (int)$part)
//														{
//														$keepParts[] = $part;
//														}
//													}
//												// skip pages like this in the future
//												$this->addSkipPage(implode('/', $keepParts));
												}
											else
												{
												$linkInfo = $this->links[$linkToAdd];
												}
											$linkInfo->addReference($link);
											$this->links[$linkToAdd] = $linkInfo;
											}
										}
									}
								}
							// look for PHP errors
							$domxpath = new \DOMXPath($dom);
							$filtered = $domxpath->query("//table[@class='xdebug-error']");

							if ($filtered && $filtered->length)
								{
								$i = 0;
								$newDom = new \DOMDocument();
								$newDom->formatOutput = true;

								while ($myItem = $filtered->item($i++))
									{
									$node = $newDom->importNode($myItem, true);
									$newDom->appendChild($node);
									}

								if (! isset($this->phpErrors[$link]))
									{
									$this->phpErrors[$link] = '';
									}
								$this->phpErrors[$link] .= $newDom->saveHTML();
								}
							unset($dom);
							}
						}
					}
				}
			}
		while (\count($unscrappedLinks));

		return $this;
		}

	/**
	 * @return (int|string)[]
	 *
	 * @psalm-return list<array-key>
	 */
	public function getAllExternalLinks() : array
		{
		return \array_keys($this->externalLinks);
		}

	/**
	 * @return (int|string)[]
	 *
	 * @psalm-return list<array-key>
	 */
	public function getAllInternalLinks() : array
		{
		return \array_keys($this->links);
		}

	/**
	 * @return array<string>
	 */
	public function getAllLinks() : array
		{
		return [...$this->getAllInternalLinks(), ...$this->getAllExternalLinks()];
		}

	/**
	 * @return array<string>
	 */
	public function getBadLinks() : array
		{
		return $this->badLinks;
		}

	/**
	 * @return array<string>
	 */
	public function getLinkReferences(string $link) : array
		{
		return isset($this->links[$link]) ? $this->links[$link]->getReferences() : [];
		}

	/**
	 * @return array<string>
	 */
	public function getPHPErrors() : array
		{
		return $this->phpErrors;
		}

	/**
	 * @param array<string,string> $fieldValues
	 */
	public function login(string $page, array $fieldValues) : static
		{
		$result = $this->web->Process($page);

		if (! $result['success'])
			{
			$this->badLinks[] = $page;
			}
		elseif (200 != $result['response']['code'])
			{
			$this->badLinks[] = $page;
			}
		elseif (1 != (\is_countable($result['forms']) ? \count($result['forms']) : 0))
			{
			$this->badLinks[] = $page;
			}
		else
			{
			$form = $result['forms'][0];

			foreach ($fieldValues as $name => $value)
				{
				$form->SetFormValue($name, $value);
				}
			$result2 = $form->GenerateFormRequest();
			$this->web->Process($result2['url'], 'auto', $result2['options']);
			}

		return $this;
		}

	public function normalizeLink(string $link, string $parentLink) : string
		{
		if (! \strrpos($parentLink, '/'))
			{
			return '';
			}

		if (! $this->shouldFollow($link))
			{
			return '';  // punt, no need to normalize it
			}

		if ('http' == \substr(\strtolower($link), 0, 4))
			{
			return $link;   // already good to go
			}

		if ('..' == \substr($link, 0, 2))
			{
			$link = '/' . $link;
			$returnLink = $parentLink;
			}
		else
			{
			$returnLink = $this->base;

			if ('/' != $link[0])
				{
				$returnLink = $parentLink;
				$link = '/' . $link;
				}
			}

		if (\strrpos($returnLink, '.php'))
			{
			$lastSlash = \strrpos($returnLink, '/');

			if (false !== $lastSlash)
				{
				$returnLink = \substr($returnLink, 0, $lastSlash);
				}
			}
		$returnLink = \rtrim($returnLink, '/');

		return $returnLink . \str_replace('//', '/', $link);
		}

	public function resetInternalLinks() : static
		{
		$this->links = [];

		return $this;
		}

	public function scrape(string $link) : static
		{
		$this->links[$link] = new \App\Tools\LinkInfo();

		return $this;
		}

	public function setPageDelay(int $microSeconds) : static
		{
		$this->pageDelay = (int)$microSeconds;

		return $this;
		}

	public function shouldFollow(string $link) : bool
		{
		if (empty($link))
			{
			return false;
			}
		$first = $link[0];

		if ('?' == $first || '#' == $first) // anchor or simple query string
			{
			return false;
			}

		if (false !== \stripos($link, 'tel:'))  // telephone, don't bother
			{
			return false;
			}

		if (false !== \stripos($link, 'mailto:'))  // mailto, don't bother
			{
			return false;
			}
		$extention = \strrchr($link, '.');

		if ($extention)
			{
			if (\in_array($extention, $this->nonTextExtensions))
				{
				return false;
				}
			}

		if (0 === \stripos($link, $this->base))  // if this is our server, accept
			{
			return true;
			}

		if ('//' == \substr($link, 0, 2) || 'http' == \substr(\strtolower($link), 0, 4))   // clearly external link
			{
			$this->externalLinks[$link] = $this->currentPage;

			return false;
			}

		// not sure what it could be at this point, so look at it.
		return true;
		}

	public function testExternalLinks() : static
		{
		foreach ($this->externalLinks as $link => $parentPage)
			{
			$this->report('Testing external link', $link);
			$result = $this->web->Process($link);

			if (isset($result['response']) && ! \in_array($result['response']['code'] ?? 0, $this->validResponses))
				{
				$this->badLinks[$link] = 'Parent: ' . $parentPage . ' Status: ' . ($result['response']['code'] ?? 'Unknown');
				}
			}

		return $this;
		}

	private function cleanXDebugClasses(string $html) : string
		{
		// Since the class name could have anything in it, and we can only do a literal match, remove anything other than xdebug-error in the class name
		while ($pos = \strpos($html, "'xdebug-error "))
			{
			$len = \strlen($html);
			$pos += 12;
			$end = ++$pos;

			while (++$end < $len && "'" != $html[$end]) // find the ending '
				{
				}
			$html = \substr($html, 0, $pos) . \substr($html, $end);   // rip out stuff between and try again
			}

		return $html;
		}

	private function isNotText(string $link) : bool
		{
		$lastPeriod = \strrpos($link, '.');

		if (false !== $lastPeriod)
			{
			$ext = \substr($link, $lastPeriod);

			return \in_array(\strtolower($ext), $this->nonTextExtensions);
			}

		return false;
		}

	private function report(string $message, string $link) : void
		{
		echo "{$message} <a target=_blank href='{$link}'>{$link}</a><br>";
		}

	private function skippedPage(string $link) : bool
		{
		// should we skip a specific page?
		foreach ($this->skipPages as $page)
			{
			if (\str_contains($link, (string)$page))
				{
				return true;
				}
			}

		// no filters means we can scrap page
		if (! \count($this->filterPages))
			{
			return false;
			}

		// if we match a filter, we will not skip it
		foreach ($this->filterPages as $page)
			{
			if (false !== \stripos($link, (string)$page))
				{
				return false;
				}
			}

		// no filter matches, so not interested in this page, skip it.
		return true;
		}
	}
