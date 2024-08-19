<?php

namespace App\WWW\System;

class Settings extends \App\Common\WWW\System\Settings
	{
	public function sms() : void
		{
		if ($this->page->addHeader('SMS Settings'))
			{
			$view = new \App\View\System\TwilioSettings($this->page);
			$this->page->addPageContent($view->edit());
			}
		}
	}
