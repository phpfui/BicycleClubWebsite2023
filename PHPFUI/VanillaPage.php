<?php

namespace PHPFUI;

/**
 * A container to add objects to that will output a fully formed HTML page with no Foundation libraries.
 */
class VanillaPage extends \PHPFUI\Base implements \PHPFUI\Interfaces\Page
	{
	private bool $android = false;

	private \PHPFUI\Container $body;

	private bool $chrome = false;

	/** @var array<string, string> */
	private array $css = [];

	private int $edgeVersion = 0;

	private string $favIcon = '';

	private int $fireFoxVersion = 0;

	/** @var array<string, string> */
	private array $headJavascript = [];

	/** @var array<string, string> */
	private array $headScripts = [];

	/** @var array<string, string> */
	private array $headTags = [];

	/** @var array<string, string> */
	private array $ieComments = [];

	private bool $IEMobile = false;

	private bool $ios = false;

	/** @var array<string, string> */
	private array $javascript = [];

	/** @var array<string, string> */
	private array $javascriptFirst = [];

	/** @var array<string, string> */
	private array $javascriptLast = [];

	private string $language = 'en';

	private string $pageName = '';

	private string $resourcePath = '/';

	/** @var array<string, array<string, string>> */
	private array $scriptAttributes = [];

	/** @var array<string, string> */
	private array $styleSheets = [];

	/** @var array<string, string> */
	private array $tailScripts = [];

	public function __construct()
		{
		parent::__construct();
		$client = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$this->android = \preg_match('/Android|Silk/i', $client);
		$this->ios = \preg_match('/iPhone|iPad|iPod/i', $client);
		$this->IEMobile = \preg_match('/IEMobile/i', $client);

		if ($index = \strpos($client, ' Firefox/'))
			{
			$this->fireFoxVersion = (int)\substr($client, $index + 9);
			}
		elseif ($index = \strpos($client, ' Edge/'))
			{
			$this->edgeVersion = (int)\substr($client, $index + 6);
			}
		else
			{
			$this->chrome = \strpos($client, ' Chrome/') > 0;
			}
		$this->body = new \PHPFUI\Container();
		}

	/**
	 * Add to the body element directly
	 */
	public function add($item) : static
		{
		$this->body->add($item);

		return $this;
		}

	/**
	 * Add dedupped inline css
	 */
	public function addCSS(string $css) : static
		{
		$this->css[\sha1($css)] = $css;

		return $this;
		}

	/**
	 * Add dedupped JavaScript to the header
	 */
	public function addHeadJavaScript(string $js) : static
		{
		$this->headJavascript[\sha1($js)] = $js;

		return $this;
		}

	/**
	 * Add a dedupped header script
	 *
	 * @param string $module path to script
	 * @param array<string,string> $attributes key is attribute, value is option
	 */
	public function addHeadScript(string $module, array $attributes = []) : \PHPFUI\Interfaces\Page
		{
		$sha1 = \sha1($module);
		$this->headScripts[$sha1] = $module;
		$this->scriptAttributes[$sha1] = $attributes;

		return $this;
		}

	/**
	 * Add a meta tag to the head section of the page
	 */
	public function addHeadTag(string $tag) : \PHPFUI\Interfaces\Page
		{
		$this->headTags[\sha1($tag)] = $tag;

		return $this;
		}

	/**
	 * Add IE commands.  For example you should restrict IE 8 and lower clients.
	 *
	 * ```
	 * $page->addIEComments('<!--[if lt IE9]><script>window.location="/old/index.html";</script><![endif]-->');
	 * ```
	 */
	public function addIEComments(string $comment) : \PHPFUI\Interfaces\Page
		{
		$this->ieComments[\sha1($comment)] = $comment;

		return $this;
		}

	/**
	 * Add dedupped JavaScript to the page
	 */
	public function addJavaScript(string $js) : \PHPFUI\Interfaces\Page
		{
		$this->javascript[\sha1($js)] = $js;

		return $this;
		}

	/**
	 * Add dedupped JavaScript as the first JavaScript before Foundation
	 */
	public function addJavaScriptFirst(string $js) : \PHPFUI\Interfaces\Page
		{
		$this->javascriptFirst[\sha1($js)] = $js;

		return $this;
		}

	/**
	 * Add dedupped JavaScript as the last JavaScript on the page
	 */
	public function addJavaScriptLast(string $js) : \PHPFUI\Interfaces\Page
		{
		$this->javascriptLast[\sha1($js)] = $js;

		return $this;
		}

	/**
	 * Add dedupped Style Sheet to the page
	 *
	 * @param string $module filename
	 */
	public function addStyleSheet(string $module) : \PHPFUI\Interfaces\Page
		{
		$this->styleSheets[$module] = $module;

		return $this;
		}

	/**
	 * Add a dedupped script to the end of the page
	 *
	 * @param string $module path to script
	 * @param array<string,string> $attributes key is attribute, value is option
	 */
	public function addTailScript(string $module, array $attributes = []) : \PHPFUI\Interfaces\Page
		{
		$sha1 = \sha1($module);
		$this->tailScripts[$sha1] = $module;
		$this->scriptAttributes[$sha1] = $attributes;

		return $this;
		}

	/**
	 * Return just the base URI without the query string
	 */
	public function getBaseURL() : string
		{
		$url = $_SERVER['REQUEST_URI'] ?? '';
		$queryStart = \strpos($url, '?');

		if ($queryStart)
			{
			$url = \substr($url, 0, $queryStart);
			}

		return $url;
		}

	/**
	 * Direct access to the page body element.  Also accessed via add method.
	 */
	public function getBodyElement() : \PHPFUI\Container
		{
		return $this->body;
		}

	/**
	 * Return the Fav Icon
	 */
	public function getFavIcon() : string
		{
		return $this->favIcon;
		}

	/**
	 * Return the current page name
	 */
	public function getPageName() : string
		{
		return $this->pageName;
		}

	/**
	 * Returns array of the current query parameters
	 */
	public function getQueryParameters() : array
		{
		$parameters = [];
		$url = $_SERVER['REQUEST_URI'] ?? '';
		$queryStart = \strpos($url, '?');

		if ($queryStart)
			{
			\parse_str(\substr($url, $queryStart + 1), $parameters);
			}

		return $parameters;
		}

	/**
	 * Returns the query string with leading ? if set
	 */
	public function getQueryString() : string
		{
		$parameters = $this->getQueryParameters();

		if ($parameters)
			{
			return '?' . \http_build_query($parameters);
			}

		return '';
		}

	/**
	 * Get fully qualified resource path root relative with resource if passed
	 *
	 * A $resource starting with / or http is not modified
	 */
	public function getResourcePath(string $resource = '') : string
		{
		if ('' == $resource)
			{
			return $this->resourcePath;
			}

		if ('/' == $resource[0] || 0 === \stripos($resource, 'http'))
			{
			return $resource;
			}

		return $this->resourcePath . $resource;
		}

	/**
	 * return true if it has a built in date picker detectable by  HTTP_USER_AGENT
	 */
	public function hasDatePicker() : bool
		{
		return $this->android || $this->ios || $this->IEMobile || $this->fireFoxVersion >= 57 || $this->chrome || $this->edgeVersion;
		}

	/**
	 * return true if it has a built in date time picker detectable by HTTP_USER_AGENT
	 */
	public function hasDateTimePicker() : bool
		{
		return $this->android || $this->ios || $this->IEMobile;
		}

	/**
	 * return true if it has a built in time picker detectable by HTTP_USER_AGENT
	 */
	public function hasTimePicker() : bool
		{
		return $this->android || $this->IEMobile || $this->ios;
		}

	/**
	 * Return true if Android platform
	 */
	public function isAndroid() : bool
		{
		return $this->android;
		}

	/**
	 * Return true if Chrome browser
	 */
	public function isChrome() : bool
		{
		return $this->chrome;
		}

	/**
	 * Return true if Windows Mobile browser
	 */
	public function isIEMobile() : bool
		{
		return $this->IEMobile;
		}

	/**
	 * Return true if IOS platform
	 */
	public function isIOS() : bool
		{
		return $this->ios;
		}

	/**
	 * Redirect page.  Default will redirect to the current page
	 * minus query string.  Pass formatted query string as
	 * $parameter with no leading ?.
	 *
	 * @param string $url default '', current url
	 * @param string $parameters default ''
	 * @param int $timeout default 0
	 */
	public function redirect(string $url = '', string $parameters = '', int $timeout = 0) : \PHPFUI\Interfaces\Page
		{
		if (empty($url))
			{
			$url = $this->getBaseURL();
			$questionIndex = \strpos($url, '?');

			if ($questionIndex)
				{
				$url = \substr($url, 0, $questionIndex);
				}
			}

		if (! empty($parameters))
			{
			$pos = \strpos($url, '?');

			if ($pos > 0)
				{
				$url = \substr($url, 0, $pos);
				}

			if ('?' != $parameters[0])
				{
				$parameters = '?' . $parameters;
				}
			}

		$timeout = (int)$timeout;

		if (! $timeout)
			{
			\header("location: {$url}{$parameters}");
			$this->done();
			}
		else
			{
			$this->addHeadTag("<meta http-equiv='refresh' content='{$timeout};url={$url}{$parameters}'>");
			}

		return $this;
		}

	/**
	 * Remove inline css
	 */
	public function removeCSS(string $css) : static
		{
		unset($this->css[\sha1($css)]);

		return $this;
		}

	/**
	 * Remove JavaScript from the header
	 */
	public function removeHeadJavaScript(string $js) : static
		{
		unset($this->headJavascript[\sha1($js)]);

		return $this;
		}

	/**
	 * Remove header script
	 *
	 * @param string $module path to script
	 */
	public function removeHeadScript(string $module) : \PHPFUI\Interfaces\Page
		{
		unset($this->headScripts[\sha1($module)]);

		return $this;
		}

	/**
	 * Remove a meta tag from the head section of the page
	 */
	public function removeHeadTag(string $tag) : \PHPFUI\Interfaces\Page
		{
		unset($this->headTags[\sha1($tag)]);

		return $this;
		}

	/**
	 * Remove IE commands.
	 */
	public function removeIEComments(string $comment) : \PHPFUI\Interfaces\Page
		{
		unset($this->ieComments[\sha1($comment)]);

		return $this;
		}

	/**
	 * Remove JavaScript from the page
	 */
	public function removeJavaScript(string $js) : \PHPFUI\Interfaces\Page
		{
		unset($this->javascript[\sha1($js)]);

		return $this;
		}

	/**
	 * Remove JavaScript from the first JavaScript before Foundation
	 */
	public function removeJavaScriptFirst(string $js) : \PHPFUI\Interfaces\Page
		{
		unset($this->javascriptFirst[\sha1($js)]);

		return $this;
		}

	/**
	 * Remove JavaScript from the last JavaScript on the page
	 */
	public function removeJavaScriptLast(string $js) : \PHPFUI\Interfaces\Page
		{
		unset($this->javascriptLast[\sha1($js)]);

		return $this;
		}

	/**
	 * Remove  a Style Sheet from the page
	 *
	 * @param string $module filename
	 */
	public function removeStyleSheet(string $module) : \PHPFUI\Interfaces\Page
		{
		unset($this->styleSheets[$module]);

		return $this;
		}

	/**
	 * Remove script from the end of the page
	 *
	 * @param string $module path to script
	 */
	public function removeTailScript(string $module) : \PHPFUI\Interfaces\Page
		{
		unset($this->tailScripts[\sha1($module)]);

		return $this;
		}

	/**
	 * Sets the Fav Icon (shown in browser tabs and elsewhere in the
	 * browser)
	 *
	 * @param string $path to favicon
	 */
	public function setFavIcon(string $path) : \PHPFUI\Interfaces\Page
		{
		$this->favIcon = $path;

		return $this;
		}

	/**
	 * Set the page language
	 */
	public function setLanguage(string $lang) : \PHPFUI\Interfaces\Page
		{
		$this->language = $lang;

		return $this;
		}

	/**
	 * Set the page name.  Defaults to "Created with Foundation"
	 *
	 * @param string $name of page
	 */
	public function setPageName(string $name) : \PHPFUI\Interfaces\Page
		{
		$this->pageName = $name;

		return $this;
		}

	/**
	 * $resoursePath should start from the public root directory and include a trailing forward slash
	 */
	public function setResourcePath(string $resourcePath = '/') : \PHPFUI\Interfaces\Page
		{
		$this->resourcePath = $resourcePath;

		return $this;
		}

	protected function getBody() : string
		{
		return '';
		}

	protected function getEnd() : string
		{
		$nl = parent::getDebug() ? "\n" : '';

		foreach ($this->tailScripts as $sha1 => $src)
			{
			$src = $this->getResourcePath($src);
			$attributes = $this->getAttributes($this->scriptAttributes[$sha1]);
			$this->body->add("<script {$attributes} src='{$src}'></script>{$nl}");
			}

		$js = \array_merge($this->javascriptFirst, $this->javascript, $this->javascriptLast);

		if ($js)
			{
			$this->body->add('<script>' . \implode(';' . $nl, $js) . '</script>' . $nl);
			}

		return $this->body . '</body></html>';
		}

	protected function getStart() : string
		{
		$nl = parent::getDebug() ? "\n" : '';
		$output = '<!DOCTYPE html>' . $nl;

		foreach ($this->ieComments as $comment)
			{
			$output .= $comment;
			}

		$output .= "<html lang='{$this->language}'>{$nl}<head>";

		foreach ($this->headTags as $tag)
			{
			$output .= $tag;
			}

		if ($this->favIcon)
			{
			$output .= "<link rel='shortcut icon' href='{$this->favIcon}'>{$nl}";
			}

		$output .= "<title>{$this->pageName}</title>{$nl}";

		foreach ($this->styleSheets as $sheet)
			{
			$sheet = $this->getResourcePath($sheet);
			$output .= "<link rel='stylesheet' href='{$sheet}'>{$nl}";
			}

		foreach ($this->headScripts as $sha1 => $src)
			{
			$src = $this->getResourcePath($src);
			$attributes = $this->getAttributes($this->scriptAttributes[$sha1]);
			$output .= "<script {$attributes} src='{$src}'></script>{$nl}";
			}

		if ($this->headJavascript)
			{
			$output .= '<script>' . \implode(';' . $nl, $this->headJavascript) . '</script>' . $nl;
			}

		if ($this->css)
			{
			$output .= '<style>' . \implode($nl, $this->css) . '</style>' . $nl;
			}

		$output .= '</head><body>';

		return $output;
		}

	/**
	 * @param array<string, string> $attributes
	 */
	private function getAttributes(array $attributes) : string
		{
		$output = '';

		foreach ($attributes as $type => $value)
			{
			if (! \strlen(\trim($value)))
				{
				$output .= ' ' . $type;
				}
			else
				{
				$output .= " {$type}='{$value}'";
				}
			}

		return $output;
		}
	}
