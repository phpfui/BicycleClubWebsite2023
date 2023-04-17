<?php

namespace App\WWW;

class Signout extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function landingPage() : void
		{
		\App\Model\Session::destroy();
		$this->page->redirect('/');
		}
	}
