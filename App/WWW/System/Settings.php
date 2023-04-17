<?php

namespace App\WWW\System;

class Settings extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function analytics() : void
		{
		if ($this->page->addHeader('Google Analytics Settings'))
			{
			$view = new \App\View\System\GoogleAnalytics($this->page);
			$this->page->addPageContent($view->editSettings());
			}
		}

	public function captcha() : void
		{
		if ($this->page->addHeader('Google ReCAPTCHA Settings'))
			{
			$view = new \App\View\System\ReCAPTCHA($this->page);
			$this->page->addPageContent($view->editSettings());
			}
		}

	public function constantContact(string $parameter = '') : void
		{
		if ($this->page->addHeader('Constant Contact Settings'))
			{
			$view = new \App\View\System\ConstantContact($this->page);
			$this->page->addPageContent($view->editSettings($parameter));
			}
		}

	public function email() : void
		{
		if ($this->page->addHeader('Email Processor Settings'))
			{
			$view = new \App\View\Admin\SystemEmail($this->page);
			$this->page->addPageContent($view->editSettings());
			}
		}

	public function favIcon() : void
		{
		if ($this->page->addHeader('Set FavIcon'))
			{
			$view = new \App\View\System\FavIcon($this->page);
			$this->page->addPageContent($view->editSettings());
			}
		}

	public function slack() : void
		{
		if ($this->page->addHeader('Slack Settings'))
			{
			$view = new \App\View\System\SlackSettings($this->page);
			$this->page->addPageContent($view->editSettings());
			}
		}

	public function sms() : void
		{
		if ($this->page->addHeader('SMS Settings'))
			{
			$view = new \App\View\System\TwilioSettings($this->page);
			$this->page->addPageContent($view->editSettings());
			}
		}

	public function smtp() : void
		{
		if ($this->page->addHeader('SMTP Settings'))
			{
			$view = new \App\View\System\SMTPSettings($this->page);
			$this->page->addPageContent($view->editSettings());
			}
		}

	public function sparkpost() : void
		{
		if ($this->page->addHeader('SparkPost API Settings'))
			{
			$view = new \App\View\System\SparkPostSettings($this->page);
			$this->page->addPageContent($view->editSettings());
			}
		}

	public function tinify() : void
		{
		if ($this->page->addHeader('Tinify API Settings'))
			{
			$view = new \App\View\System\TinifySettings($this->page);
			$this->page->addPageContent($view->editSettings());
			}
		}
	}
