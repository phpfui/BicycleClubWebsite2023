<?php

namespace App\WWW;

class Config implements \PHPFUI\Interfaces\NanoClass, \Stringable
	{
	private readonly \App\View\Setup\Page $page;

	private readonly \App\Settings\DB $settings;

	private string $title = 'Bicycle Club Website Setup';

	// @phpstan-ignore-next-line
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		$this->page = new \App\View\Setup\Page();
		$this->page->addCSS('body { margin: 1em}')->setPageName($this->title);
		\header('Access-Control-Allow-Origin: ' . $this->page->getSchemeHost());
		$this->page->addStyleSheet('/css/styles.css');
		$this->settings = new \App\Settings\DB();

		if (! $this->settings->empty() && ! $this->settings->setup)
			{
			\header('location: /Home');

			exit;
			}
		$this->page->add(new \PHPFUI\Header($this->title, 3));
		}

	public function __toString() : string
		{
		return "{$this->page}";
		}

	public function landingPage() : void
		{
		$this->page->add(new \App\View\Setup\Start($this->page, $this->getWizardBar(0)));
		}

	public function wizard(string $direction = '') : void
		{
		$stage = \max($this->settings->stage, 0);
		$redirect = true;

		if ('prev' == $direction && $stage > 0)
			{
			--$stage;
			}
		elseif ('next' == $direction)
			{
			++$stage;
			}
		else
			{
			$redirect = false;
			$state = (int)$direction;
			}
		$this->settings->stage = $stage;
		$this->settings->save();

		if ($redirect)
			{
			$this->page->redirect('/Config/wizard/' . $stage);

			return;
			}

		$wizardBar = $this->getWizardBar($stage);

		// if connection is not valid, then go to db config page
		if (! $this->settings->getPDO())
			{
			$stage = \min($stage, 1);
			}

		switch ($stage)
			{
			case 0:
				$this->page->add(new \App\View\Setup\Start($this->page, $wizardBar));

				break;

			case 1:
				$this->page->add(new \App\View\Setup\DBSettings($this->page, $this->settings, $wizardBar));

				break;

			case 2:
				$this->page->add(new \App\View\Setup\DBCharacterSet($this->page, $this->settings, $wizardBar));

				break;

			case 3:
				$this->page->add(new \App\View\Setup\DBCollation($this->page, $this->settings, $wizardBar));

				break;

			case 4:
				$this->page->add(new \App\View\Setup\DBInit($this->page, $wizardBar));

				break;

			case 5:
				$this->page->add(new \App\View\Setup\ImportMembers($this->page, $wizardBar));

				break;

			case 6:
				$this->page->add(new \App\View\Setup\TimeZone($this->page, $this->settings, $wizardBar));

				break;

			case 7:
				$view = new \App\View\Admin\Configuration($this->page);
				$this->page->add(new \App\View\Setup\Generic('General Settings', $view->site(), $wizardBar));

				break;

			case 8:
				$membershipView = new \App\View\Membership($this->page);
				$view = $membershipView->configure();
				$this->page->add(new \App\View\Setup\Generic('Membership Settings', $view, $wizardBar));

				break;

			case 9:
				$this->page->add(new \App\View\Setup\AssignMembers($this->page, $wizardBar));

				break;

			case 10:
				$view = new \App\View\System\SMTPSettings($this->page);
				$this->page->add(new \App\View\Setup\Generic('Email SMTP Settings', $view->edit(), $wizardBar));

				break;

			case 11:
				$view = new \App\View\System\IMAPSettings($this->page);
				$this->page->add(new \App\View\Setup\Generic('Email IMAP Settings', $view->edit(), $wizardBar));

				break;

			case 12:
				$this->page->add(new \App\View\Setup\TestEmail($this->page, $wizardBar));

				break;

				// load permission names
				// optional page to create and manage groups?
				// set up ride categories

			case 13:
				$superUserView = new \App\View\Setup\SuperUsers($this->page);
				$this->page->add($superUserView->addUsers($wizardBar));

				break;

			case 14:
				$superUserView = new \App\View\Setup\SuperUsers($this->page);
				$this->page->add($superUserView->emailPasswordResets($wizardBar));

				break;

			case 15:
				$view = new \App\View\System\GoogleAnalytics($this->page);
				$this->page->add(new \App\View\Setup\Generic('Google Analytics Settings', $view->edit(), $wizardBar));

				break;

			case 16:
				$view = new \App\View\System\ReCAPTCHA($this->page);
				$this->page->add(new \App\View\Setup\Generic('Google ReCAPTCHA Settings', $view->edit(), $wizardBar));

				break;

			case 17:
				$view = new \App\View\System\ErrorLogging($this->page);
				$this->page->add(new \App\View\Setup\Generic('Error Logging', $view->edit(), $wizardBar));

				break;

			case 18:
				$view = new \App\View\System\TwilioSettings($this->page);
				$this->page->add(new \App\View\Setup\Generic('Twilio SMS Settings', $view->edit(), $wizardBar));

				break;

			case 19:
				$view = new \App\View\System\TinifySettings($this->page);
				$this->page->add(new \App\View\Setup\Generic('Tinify Settings', $view->edit(), $wizardBar));

				break;

			case 20:
				$view = new \App\View\System\FavIcon($this->page);
				$this->page->add(new \App\View\Setup\Generic('FavIcon Settings', $view->edit(), $wizardBar));

				break;

			default:
				$this->page->add(new \App\View\Setup\Done($this->page, $this->settings, $wizardBar));

				break;
			}
		}

	private function getWizardBar(int $stage) : \App\View\Setup\WizardBar
		{
		return new \App\View\Setup\WizardBar($stage, 21);
		}
	}
