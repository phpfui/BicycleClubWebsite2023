<?php

namespace App\WWW;

/**
 * MyWCC signed in home page
 */
class Home extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function landingPage() : void
		{
		$member = \App\Model\Session::signedInMemberRecord();

		if ($member->loaded())
			{
			if ($member->verifiedEmail < 9)
				{
				$joinView = new \App\View\Membership\Join($this->page);
				$this->page->addPageContent($joinView->process($member));
				}
			elseif (\App\Model\Session::hasExpired())
				{
				$this->page->redirect('/Membership/renew');
				}
			else
				{
				$content = new \App\View\Content($this->page);
				$this->page->addPageContent($content->getDisplayCategoryHTML('User Home Page Top'));

				if ($this->page->addHeader("{$member->fullName()} Home Page", 'Home Page'))
					{
					$view = new \App\View\Member\HomePage($this->page, \App\Model\Session::signedInMemberRecord());
					$this->page->addPageContent($view);
					}
				}
			}
		}

	public function loginAs(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($this->page->addHeader('Login As Other User'))
			{
			\App\Model\Session::unregisterMember();

			if ($member->loaded())
				{
				\App\Model\Session::registerMember($member);
				}
			}
		$this->page->redirect('/Home');
		}
	}
