<?php

namespace App\Model;

class Controller extends \PHPFUI\NanoController implements \PHPFUI\Interfaces\NanoController
	{
	private \App\View\Public\Footer $footer;

	private \App\View\Public\Menu $publicMenu;

	/**
	 * @var array<string,string>
	 */
	private array $redirects = [];

	/**
	 * @var array<\stdClass|string>
	 */
	private array $routes = [];

	public function __construct(private readonly \App\Model\PermissionBase $permissions, private ?\DebugBar\StandardDebugBar $debugBar = null)
		{
		$uri = $_SERVER['REQUEST_URI'] ?? '';
		$query = \strpos((string)$uri, '?');

		if (false !== $query)
			{
			$uri = \substr((string)$uri, 0, $query);
			}
		parent::__construct($uri);
		$domainParts = \explode('.', \strtolower((string)$_SERVER['HTTP_HOST']));
		$subdomain = \array_shift($domainParts);

		if ('api' == $subdomain)
			{
			$this->setMissingClass('App\\View\API\\Missing');
			$this->setHomePageClass('');
			$this->setMissingMethod('landingPage');
			$this->setRootNamespace('App\\API');
			}
		else
			{
			$this->setMissingClass(\App\View\Missing::class);
			$this->setMissingMethod('landingPage');
			$this->setRootNamespace('App\\WWW');
			$this->setHomePageClass(\App\View\Public\HomePage::class);
			// The PublicMenu also adds routes, so make it in the controller so it is available in Page
			$this->publicMenu = new \App\View\Public\Menu($this);
			$this->footer = new \App\View\Public\Footer();
			}
		}

	public function addRedirect(string $originalUrl, string $redirectUrl) : void
		{
		$this->redirects[\strtolower($originalUrl)] = $redirectUrl;
		}

	/**
	 * @param array<\stdClass|string> $callback must be an array of object and method so object can be returned by the controller::run() method.  It can not be a closure.
	 */
	public function addRoute(string $route, array $callback) : void
		{
		$this->routes[\strtolower($route)] = $callback;
		}

	public function getDebugBar() : ?\DebugBar\StandardDebugBar
		{
		return $this->debugBar;
		}

	public function getFooter() : \App\View\Public\Footer
		{
		return $this->footer;
		}

	public function getPermissions() : \App\Model\PermissionBase
		{
		return $this->permissions;
		}

	public function getPublicMenu() : \App\View\Public\Menu
		{
		return $this->publicMenu;
		}

	public function initDebugBar(?\DebugBar\StandardDebugBar $debugBar) : static
		{
		$this->debugBar = $debugBar;

		return $this;
		}

	public function run() : \PHPFUI\Interfaces\NanoClass
		{
		$uri = $this->getUri();
		$this->checkRedirects($uri);

		if ($this->debugBar)
			{
			foreach (new \App\Model\Errors()->getErrors() as $error)
				{
				$collector = $this->debugBar->getCollector('messages');

				if ($collector instanceof \DebugBar\DataCollector\MessagesCollector)
					{
					$collector->addMessage($error, 'error');
					}
				}
			}
		$uri = \strtolower($uri);
		$callback = $this->routes[$uri] ?? null;

		if ($callback)
			{
			$page = new \App\View\Page($this);

			\call_user_func($callback, $page);

			// return the object the method was called on
			return $callback[0];
			}

		\PHPFUI\ORM::reportErrors();

		return parent::run();
		}

	private function checkRedirects(string $url) : bool
		{
		$url = \parse_url($url, PHP_URL_PATH);
		$url = \strtolower(\trim($url ?? '', '/'));

		if (empty($url))
			{
			$url = '/';
			}

		if (! isset($this->redirects[$url]))
			{
			return false;
			}
		$redirect = $this->redirects[$url];

		if ('http' != \substr((string)$redirect, 0, 4))
			{
			$redirect = '/' . $redirect;
			}
		\header('location: ' . $redirect, true, 301);

		exit();
		}
	}
