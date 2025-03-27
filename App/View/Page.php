<?php

namespace App\View;

class Page extends \PHPFUI\Page
	{
	use \App\Tools\SchemeHost;

	public readonly \App\View\MainMenu $mainMenu;

	protected \App\Table\Setting $settingTable;

	private static bool $attemptedSignin = false;

	private bool $bannerOff = false;

	private readonly \App\Tools\Cookies $cookies;

	private ?\DebugBar\JavascriptRenderer $debugBarRenderer = null;

	private bool $displayedFlash = false;

	private bool $done = false;

	private string $forgotPassword = 'forgotPassword';

	private ?\App\Model\HeaderContent $headerContentModel = null;

	private readonly \PHPFUI\Container $mainColumn;

	private bool $noMenus = false;

	private static bool $passwordReset = false;

	private bool $publicPage = false;

	private bool $renewing = false;

	/**
	 * @var array<string>
	 */
	private array $requiredPages = [];

	private bool $shownSignIn = false;

	public function __construct(public \App\Model\Controller $controller)
		{
		parent::__construct();
//		$this->setCacheBuster(new \PHPFUI\CacheBuster(PUBLIC_ROOT, '/Cache/buster'));
		\header('Access-Control-Allow-Origin: ' . $this->getSchemeHost());
		\header('Content-Type: text/html; charset=utf-8');
		$this->cookies = new \App\Tools\Cookies();
		$this->settingTable = new \App\Table\Setting();
		$this->mainMenu = new \App\View\MainMenu($this->controller->getPermissions(), $this->getBaseURL());

		// hard redirect since it seems impossible to make apache redirect to include www.
		$_SERVER['SERVER_ADDR'] ??= '::1';
		$_SERVER['HTTP_HOST'] ??= 'www.';

		// host must have three or more segments.  domain.com does not work with PayPal.  www.domain.com does. So if not there, redirect to home page
		if ('127.0.0.1' != $_SERVER['SERVER_ADDR'] && '::1' != $_SERVER['SERVER_ADDR'] && (\count(\explode('.', (string)$_SERVER['HTTP_HOST'])) < 3 || 'https' != $_SERVER['REQUEST_SCHEME']))
			{
			\header("location: {$this->value('homePage')}{$_SERVER['REQUEST_URI']}");

			exit();
			}

		$debugBar = $controller->getDebugBar();

		if ($debugBar)
			{
			$this->debugBarRenderer = $debugBar->getJavascriptRenderer();
			$this->debugBarRenderer->getBaseUrl();
			$this->addHeadJavascript($this->getOutputBuffer([$this->debugBarRenderer, 'dumpJsAssets']));
			$this->addCss(\str_replace('../fonts/', '/fonts/', $this->getOutputBuffer([$this->debugBarRenderer, 'dumpCssAssets'])));
			}

		\PHPFUI\Base::setDebug(\App\Model\Session::getDebugging());

		// set the fav icon stuff
		$this->setFavIcon('/favicon.ico');
		$this->addHeadTag($this->value('faviconHeaders'));

		// redirect IE to Ming Dynasty page
		$ieUrl = '/' . \str_replace('www.', '', (string)$_SERVER['HTTP_HOST']) . '.old/index.html';
		$client = $_SERVER['HTTP_USER_AGENT'] ?? '';

		if (\preg_match('/MSIE 10.0|rv:11.0/i', (string)$client))
			{
			$this->redirect($ieUrl);

			exit;
			}
		$this->addIEComments('<!--[if lte IE 11]><script>window.location="' . $ieUrl . '";</script><![endif]-->');

		$this->mainColumn = new \PHPFUI\Container();
		$this->processPost();
		$this->setPageName($this->value('clubName'));

		if (! $this->controller->getPermissions()->isSuperUser())	// don't track super users, we are special
			{
			$trackingCode = $this->value('GoogleAnalyticsTrackingCode');

			if ($trackingCode)
				{
				$this->addHeadScript("https://www.googletagmanager.com/gtag/js?id={$trackingCode}", ['async' => '']);
				$js = "window.dataLayer=window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$trackingCode}');";
				$this->addHeadJavaScript($js);
				}
			}

		if ($this->value('TestMode'))
			{
			$testCallout = new \PHPFUI\Callout('alert');
			$testCallout->add('<b>This site is in <i>TEST MODE</i>. Do not use except for testing.</b>');
			$testCallout->addAttribute('data-closable');
			$testCallout->addAttribute('data-close');
			$this->mainColumn->add("{$testCallout}");
			}
		}

	public function addBanners() : void
		{
		if (! $this->bannerOff)
			{
			$bannerTable = new \App\Table\Banner();
			$banners = $bannerTable->getActiveRows();

			if (\count($banners))
				{
				\shuffle($banners);
				$slider = new \PHPFUI\SlickSlider($this);

				foreach ($banners as $banner)
					{
					$target = \str_starts_with((string)$banner['url'], 'http') ? ' target=_blank ' : '';
					$slider->addSlide("<a href='{$banner['url']}'{$target}>" . \App\Model\BannerFiles::getBanner(new \App\Record\Banner($banner)) . '</a>');
					}
				$slider->addSliderAttribute('lazyLoad', "'ondemand'");
				$slider->addSliderAttribute('mobileFirst', true);
				$slider->addSliderAttribute('swipeToSlide', true);
				$slider->addSliderAttribute('arrows', false);
				$slider->addSliderAttribute('autoplay', true);
				$slider->addSliderAttribute('autoplaySpeed', 10000);
				$this->mainColumn->add("{$slider}");
				}
			}
		$this->bannerOff = true;
		}

	public function addHeader(string $header, string $permission = '', bool $override = false, bool $banner = true) : bool
		{
		if ($banner)
			{
			$this->addBanners();
			}
		$show = true;

		if (! $this->isPublic())
			{
			if (empty($permission))
				{
				$permission = $header;
				}
			$show = $override || $this->isAuthorized($permission);
			}

		if ($show)
			{
			if (! \count($this->requiredPages))
				{
				if (\App\Model\Session::hasExpired() && ! $this->isRenewing())
					{
					$this->redirect('/Membership/renew');
					$show = false;
					}
				else
					{
					$this->mainColumn->add(new \PHPFUI\Header($header));
					}
				}
			}
		elseif ($this->isSignedIn())
			{
			$this->mainColumn->add(new \PHPFUI\Header($header));
			$this->notAuthorized($this->mainMenu->getActiveMenu() . ' - ' . $permission);
			}
		else
			{
			if (! $this->shownSignIn)
				{
				$this->mainColumn->add($this->signInPage('You must be signed in to view this page') ?? '');
				}
			}

		$this->mainColumn->add($this->getFlashMessages());

		return $show;
		}

	public function addPageContent(mixed $item) : static
		{
		$show = ! \App\Model\Session::hasExpired() || $this->isRenewing();

		if (! $this->getDone() && 0 == \count($this->requiredPages) && ($this->publicPage || ($this->isSignedIn() && $show)))
			{
			$this->mainColumn->add("{$item}");  // force convert to string so all objects execute
			}

		return $this;
		}

	public function addRequiredPage(string $page) : static
		{
		$this->requiredPages[] = $page;

		return $this;
		}

	public function addSubHeader(string $header) : static
		{
		$this->mainColumn->add(new \PHPFUI\SubHeader($header));

		return $this;
		}

	public function getBaseURL() : string
		{
		// first character could be lower case, so upper case it to match class
		$url = '/' . \ucfirst(\substr(parent::getBaseURL(), 1));

		return $url;
		}

	public function getController() : \App\Model\Controller
		{
		return $this->controller;
		}

	public function getDone() : bool
		{
		return $this->done;
		}

	public function getFlashMessages() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if ($this->displayedFlash)
			{
			return $container;
			}
		// add in flash messages
		$callouts = ['success', 'primary', 'secondary', 'warning', 'alert'];

		foreach ($callouts as $calloutClass)
			{
			$message = \App\Model\Session::getFlash($calloutClass);

			if (! $message)
				{
				continue;
				}

			$callout = new \PHPFUI\Callout($calloutClass);
			$callout->addAttribute('data-closable');
			$callout->addAttribute('data-close');

			if (\is_array($message))
				{
				$ul = new \PHPFUI\UnorderedList();

				foreach ($message as $field => $error)
					{
					if (\is_array($error))
						{
						foreach ($error as $validationError)
							{
							$ul->addItem(new \PHPFUI\ListItem("Field <b>{$field}</b> has the following error: <i>{$validationError}</i>"));
							}
						}
					else
						{
						$ul->addItem(new \PHPFUI\ListItem($error));
						}
					}
				$callout->add($ul);
				}
			else
				{
				$callout->add($message);
				}
			$container->add($callout);
			}
		$this->displayedFlash = true;

		return $container;
		}

	public function getPermissions() : \App\Model\PermissionBase
		{
		return $this->controller->getPermissions();
		}

	public function getStart() : string
		{
		$this->addStyleSheet('/css/styles.v2.css');

		if (\count($this->requiredPages))
			{
			\reset($this->requiredPages);
			$page = \current($this->requiredPages);
			$this->mainColumn->add("{$page}");  // force convert to string so all objects execute
			}
		elseif (! $this->publicPage && ! $this->isSignedIn())
			{
			$this->mainColumn->add($this->signInPage('Sign In') ?? '');
			}

		if ($this->noMenus)
			{
			$cell = new \PHPFUI\Cell();
			$titleBar = new \PHPFUI\TopBar();
			$scrollableHeader = new \App\UI\ScrollOffHeader($this, $titleBar);
			$headerContentModel = $this->getHeaderContentModel();
			$headerContent = $headerContentModel->getActiveHeaderContent();

			if ($headerContent)
				{
				$this->add($scrollableHeader->getHeader($headerContent));
				}
			$cell->add($scrollableHeader);
			$cell->add($this->mainColumn);

			$gridX = new \PHPFUI\GridX();
			$gridX->setMargin();
			$gridX->add($cell);

			$gridContainer = new \PHPFUI\GridContainer();
			$gridContainer->add($gridX);

			$this->add($gridContainer);

			return parent::getStart();
			}

		$content = new \PHPFUI\Container();

		$title = new \PHPFUI\Container();
		$abbrev = new \PHPFUI\HTML5Element('span');
		$abbrev->addClass('show-for-small-only');

		$homeURL = '/';

		if ($this->isSignedIn())
			{
			$homeURL .= 'Home';
			$abbrev->add($this->value('boardName'));
			}
		else
			{
			$abbrev->add($this->value('clubAbbrev'));
			}
		$title->add($abbrev);

		$name = new \PHPFUI\HTML5Element('span');
		$name->addClass('show-for-medium-only');

		if ($this->isSignedIn())
			{
			$name->add($this->value('boardName') . ' - ');
			}
		$name->add($this->value('clubName'));
		$title->add($name);

		$nameLocation = new \PHPFUI\HTML5Element('span');
		$nameLocation->addClass('show-for-large');

		if ($this->isSignedIn())
			{
			$nameLocation->add($this->value('boardName') . ' - ');
			}
		$nameLocation->add($this->value('clubName') . ' - ' . $this->value('clubLocation'));
		$title->add($nameLocation);

		$link = "<a href='{$homeURL}' style='color:white;font-weight:bold;'>{$title}</a>";

		$titleBar = new \PHPFUI\TopBar();
		$hamburger = new \PHPFUI\FAIcon('fas', 'bars', '#');
		$hamburger->addClass('show-for-small-only');
		$titleBar->addLeft($hamburger);
		$titleBar->addLeft($link);

		if ($this->isSignedIn())
			{
			$searchIcon = new \PHPFUI\FAIcon('fas', 'search');
//			$searchIcon->addClass('hide-for-small-only');
			$this->addSearchModal($searchIcon);
			$titleBar->addRight($searchIcon);
			}

		if ($this->isSignedIn())
			{
			$titleBar->addRight((new \PHPFUI\Button('Sign Out', '/Signout'))->addClass('small'));
			}
		else
			{
			if (! $this->shownSignIn)
				{
				$titleBar->addRight((new \PHPFUI\Button('Sign In', '/Home'))->addClass('small'));
				}
			$titleBar->addRight((new \PHPFUI\Button('Join', $this->value('joinPage')))->addClass('small')->addClass('success'));
			}

		$url = $this->isSignedIn() ? '/Rides/memberSchedule' : '/Rides/schedule';
		$titleBar->addRight((new \PHPFUI\Button('Rides', $url))->addClass('small')->addClass('info'));

		$scrollableHeader = new \App\UI\ScrollOffHeader($this, $titleBar);
		$headerContentModel = $this->getHeaderContentModel();
		$headerContent = $headerContentModel->getActiveHeaderContent();

		if ($headerContent)
			{
			$content->add($scrollableHeader->getHeader($headerContent));
			}
		$content->add($scrollableHeader);

		$body = new \PHPFUI\HTML5Element('div');
		$body->addClass('body-info');
		$grid = new \PHPFUI\GridX();
		$menuColumn = new \PHPFUI\Cell(4, 4, 3);
		$menuColumn->addClass('show-for-medium');
		$menu = $this->getMenu();
		$menuId = $menu->getId();
		$menuColumn->add($menu);
		$grid->add($menuColumn);

		$mainColumn = new \PHPFUI\Cell(12, 8, 9);
		$mainColumn->addClass('main-column');
		$mainColumn->add($this->mainColumn);
		$grid->add($mainColumn);
		$body->add($grid);

		$offCanvas = new \PHPFUI\OffCanvas($body);
		$div = new \PHPFUI\HTML5Element('div');
		$offCanvasId = $div->getId();
		// copy over the menu with JQuery at run time
		$this->addJavaScriptFirst('$("#' . $menuId . '").clone().prependTo("#' . $offCanvasId . '");');
		$offId = $offCanvas->addOff($div, $hamburger);
		$offCanvas->setPosition($offId, 'left')->setTransition($offId, 'over');

		$content->add($offCanvas);
		$content->add($this->controller->getFooter());
		$content->add($this->debugBarRenderer ? $this->debugBarRenderer->render() : '');

		$this->add($content);

		return parent::getStart();
		}

	public function isAuthorized(string $permission, ?string $menu = null) : bool
		{
		return $this->controller->getPermissions()->isAuthorized($permission, $menu ?? $this->mainMenu->getActiveMenu());
		}

	public function isPublic() : bool
		{
		return $this->publicPage;
		}

	public function isRenewing() : bool
		{
		return $this->renewing;
		}

	public function isSignedIn() : bool
		{
		return \App\Model\Session::isSignedIn();
		}

	public function landingPage(string $menu = '') : void
		{
		$menu = $menu ?: $this->mainMenu->getActiveMenu();
		$html = (string)($this->mainMenu->getLandingPage($this, $this->mainMenu->getActiveLink(), $menu));

		if (! empty($html))
			{
			$this->addHeader($menu . ' Menu', $menu);
			$this->addPageContent($html);
			}
		}

	public function notAuthorized(string $permission = '') : void
		{
		$this->mainColumn->add(new \PHPFUI\SubHeader('You do not have the correct permissions to view this page.'));

		if ($permission)
			{
			$this->mainColumn->add('You need the <b>' . $permission . '</b> permission.');
			}
		}

	public function setDone(bool $done = true) : static
		{
		$this->done = $done;

		return $this;
		}

	public function setHeaderContentModel(\App\Model\HeaderContent $model) : static
		{
		$this->headerContentModel = $model;

		return $this;
		}

	public function setPublic(bool $public = true) : static
		{
		$this->publicPage = $public;

		return $this;
		}

	public function setRenewing(bool $renewing = true) : static
		{
		$this->renewing = $renewing;

		return $this;
		}

	public function setShowMenus(bool $showMenus = true) : static
		{
		$this->noMenus = ! $showMenus;

		return $this;
		}

	public function turnOffBanner() : static
		{
		if ($this->bannerOff)
			{
			\App\Tools\Logger::get()->backTrace('Banner was already output, make turnOffBanner call sooner');
			}
		$this->bannerOff = true;

		return $this;
		}

	public function value(string $name) : string
		{
		return $this->settingTable->value($name);
		}

	private function addSearchModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this, $modalLink);
		$modal->addClass('small');
		$form = new \PHPFUI\Form($this);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Search Menus');
		$menuSections = $this->mainMenu->getMenuSections();
		$search = new \PHPFUI\Input\SelectAutoComplete($this, 'search');

		foreach ($menuSections as $menu)
			{
			$items = $menu->getMenuItems();

			foreach ($items as $item)
				{
				if ($item instanceof \PHPFUI\MenuItem) // @phpstan-ignore-line
					{
					$search->addOption($item->getName(), $item->getLink());
					}
				}
			}
		$id = $search->getHiddenField()->getId();

		$search->addAttribute('onchange', 'goToSearchSelection()');
		$fieldSet->add($search);
		$form->add($fieldSet);
		$loading = new \App\UI\Loading();
		$loading->addClass('hide');
		$loadingId = $loading->getId();
		$form->add($loading);
		$fieldSetId = $fieldSet->getId();
		$js = "function goToSearchSelection(){ $('#{$loadingId}').toggleClass('hide');$('#{$fieldSetId}').toggleClass('hide');window.location=$('#{$id}').val();}";
		$this->addJavaScript($js);
		$modal->add($form);
		}

	private function getHeaderContentModel() : \App\Model\HeaderContent
		{
		if (! $this->headerContentModel)
			{
			$this->headerContentModel = new \App\Model\HeaderContent($this->getBaseURL());
			}

		return $this->headerContentModel;
		}

	private function getMenu() : \PHPFUI\HTML5Element
		{
		$container = new \PHPFUI\HTML5Element('div');

		if ($this->isSignedIn())
			{
			$container->add($this->mainMenu);
			$container->add('<hr>');
			}
		$container->add($this->controller->getPublicMenu());

		return $container;
		}

	private function getOutputBuffer(callable $function) : string
		{
		\ob_start();
		\call_user_func($function);
		$produced = \ob_get_contents();
		\ob_end_clean();

		return $produced;
		}

	private function processPost() : static
		{
		if ($this->isSignedIn())
			{
			if ($this->isAuthorized('Waiver Exempt', 'Membership'))
				{
				\App\Model\Session::signWaiver();
				}

			// add required pages here
			if (! \App\Model\Session::signedWaiver())
				{
				$waiverView = new \App\View\Admin\Waiver($this);

				if (! \App\Model\Session::signedWaiver()) // @phpstan-ignore booleanNot.alwaysTrue
					{
					$this->addRequiredPage($waiverView);
					}
				}
			$pollModel = new \App\Model\Poll();
			$poll = $pollModel->getRequiredPoll();

			if ($poll)
				{
				// this post back could save the vote
				$pollRequired = new \App\View\PollRequired($this, $poll, $pollModel);

				// so we want to check if it has now been saved
				$poll = $pollModel->getRequiredPoll();

				if ($poll && ! \App\Model\Session::signedInMembershipRecord()->pending)
					{
					$this->addRequiredPage($pollRequired);
					}
				}
			}

		$memberModel = new \App\Model\Member();

		if (\App\Model\Session::checkCSRF())
			{
			if (! self::$attemptedSignin && isset($_POST['SignIn'], $_POST['email'], $_POST['password']))
				{
				$email = \App\Model\Member::cleanEmail($_POST['email']);
				$passwd = $_POST['password'];

				if (empty($_POST['remember']))
					{
					$this->cookies->delete('Member');
					$this->cookies->delete('Password');
					$this->cookies->delete('Remember');
					}
				else
					{
					$this->cookies->set('Member', $email, true);
					$this->cookies->set('Password', $passwd, true);
					}
				$errors = $memberModel->signInMember($email, $passwd);
				self::$attemptedSignin = true;

				if ($errors)
					{
					\App\Model\Session::setFlash('alert', $errors);
					}
				$this->redirect('', $_SERVER['QUERY_STRING']);
				}
			elseif (isset($_POST['resetPassword']) && ! self::$passwordReset)
				{
				self::$passwordReset = true;
				$text = \str_contains((string)$_POST['resetPassword'], 'Text');
				$memberModel->resetPassword(\App\Model\Member::cleanEmail($_POST['email']), $text);
				$this->cookies->delete('Password');

				if ($text)
					{
					\App\Model\Session::setFlash('primary', 'If your email address is on file with us, and we have your cell number, we texted your number with a reset password link.');
					}
				else
					{
					\App\Model\Session::setFlash('primary', 'If your email address is on file with us, we have emailed you a reset password link.  If you have not received a password, please check your SPAM folder, or try another email address.');
					}
				$this->redirect('/Home');
				$this->done = true;
				}
			elseif (isset($_POST[$this->forgotPassword]))
				{
				\App\Model\Session::setFlash($this->forgotPassword, \App\Model\Member::cleanEmail($_POST['email'] ?? ''));
				$this->redirect('/Membership/' . $this->forgotPassword);
				$this->done = true;
				}
			}

		return $this;
		}

	private function signInPage(string $header) : ?\PHPFUI\Form
		{
		if ($this->shownSignIn || \count($this->requiredPages))
			{
			return null;
			}
		$this->mainColumn->add($this->getFlashMessages());
		$this->shownSignIn = true;
		$form = new \PHPFUI\Form($this);
		$form->add(new \PHPFUI\Header($header));
		$content = new \App\View\Content($this);
		$form->add($content->getDisplayCategoryHTML('Sign In Page'));
		$fieldSet = new \PHPFUI\FieldSet('Sign In to ' . $this->value('boardName'));

		$memberCookie = $this->cookies->get('Member');
		$emailAddress = \App\Model\Member::cleanEmail($_GET['email'] ?? $memberCookie);
		$email = new \PHPFUI\Input\Email('email', 'Your email Address', $emailAddress);
		$email->setToolTip('Your email address on file with us.  Eg. yourname@gmail.com');
		$fieldSet->add($email);
		$passwordCookie = $this->cookies->get('Password');
		$pw = $_GET['pw'] ?? $passwordCookie;
		$password = new \PHPFUI\Input\PasswordEye('password', 'Password', $pw);
		$password->setToolTip("Your password. We recommend to use a password you don't use anywhere else.");
		$fieldSet->add($password);
		$cookies = $memberCookie . $passwordCookie;
		$remember = new \PHPFUI\Input\CheckBoxBoolean('remember', 'Remember me (requires cookies for this site)', ! empty($cookies));
		$fieldSet->add($remember);
		$form->add($fieldSet);
		$form->setAreYouSure(false);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton(new \PHPFUI\Submit('Sign In', 'SignIn'));

		$joinButton = new \PHPFUI\Button('Join', $this->value('joinPage'));
		$joinButton->addClass('success');
		$buttonGroup->addButton($joinButton);

		$forgot = new \PHPFUI\Submit('Forgot My Password', $this->forgotPassword);
		$forgot->addClass('alert');
		$buttonGroup->addButton($forgot);

		$form->add($buttonGroup);
		$this->done = true;

		return $form;
		}
	}
