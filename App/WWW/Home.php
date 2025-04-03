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
			if ($member->verifiedEmail < 9 && $member->membership->expires > \App\Tools\Date::todayString())
				{
				$member->verifiedEmail = 9;
				$member->update();
				}

			if ($member->verifiedEmail < 9)
				{
				$memberModel = new \App\Model\Member();

				if ($member->verifiedEmail < 2)
					{
					$memberModel->sendVerifyEmail($member);
					$this->page->redirect("/Membership/verify/{$member->memberId}");
					}
				else
					{
					$this->page->redirect("/Membership/verify/{$member->memberId}/" . $memberModel->getVerifyCode($member->password));
					}
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
