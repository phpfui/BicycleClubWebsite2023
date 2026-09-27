<?php

namespace App\WWW\System;

class Settings extends \App\Common\WWW\System\Settings
	{
	public function db() : void
		{
		if ($this->page->addHeader('DB Settings'))
			{
			global $dbSettings;
			$view = new \App\View\System\DBSettings($this->page, $dbSettings);
			$this->page->addPageContent($view->edit());
			}
		}

	public function sms() : void
		{
		if ($this->page->addHeader('SMS Settings'))
			{
			$view = new \App\View\System\TwilioSettings($this->page);
			$this->page->addPageContent($view->edit());
			}
		}
	}
