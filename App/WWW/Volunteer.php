<?php

namespace App\WWW;

class Volunteer extends \App\Common\WWW\Volunteer
	{
	public function myDetails() : void
		{
		$member = \App\Model\Session::signedInMemberRecord();
		$detailReport = new \App\Report\Volunteer();
		$detailReport->details($member);
		$this->page->done();
		}

	public function myPoints(\App\Record\Member $member = new \App\Record\Member(), int $year = 0) : void
		{
		if ($member->empty() || ! $this->page->isAuthorized('Outstanding Volunteer Points'))
			{
			$member = new \App\Record\Member(\App\Model\Session::getSignedInMemberId());
			}

		if ($this->page->addHeader('My Points'))
			{
			$view = new \App\View\Volunteer\Points($this->page);
			$this->page->addPageContent($view->display($member, $year));
			}
		}

	public function pointHistory() : void
		{
		if ($this->page->addHeader('Point History'))
			{
			$view = new \App\View\Volunteer\Points($this->page);
			$this->page->addPageContent($view->searchHistory());
			}
		}

	public function points() : void
		{
		if ($this->page->addHeader('Outstanding Volunteer Points'))
			{
			if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\Finance();
				$report->downloadPoints($_POST);
				$this->page->done();
				}
			else
				{
				$view = new \App\View\Leader\Points($this->page);
				$this->page->addPageContent($view->Finance());
				}
			}
		}

	public function pointsDetail() : void
		{
		$memberId = $_GET['memberId'] ?? 0;

		if ($memberId != \App\Model\Session::getSignedInMemberId() || ! $this->page->isAuthorized('Outstanding Volunteer Points'))
			{
			$memberId = \App\Model\Session::getSignedInMemberId();
			}

		if ((int)($_GET['pointsAwarded'] ?? 0) > 0)
			{
			$callout = new \PHPFUI\Callout('success');
			}
		else
			{
			$callout = new \PHPFUI\Callout('warning');
			}
		$callout->add(new \App\Model\Volunteer()->getPointsDetail($_GET));

		$this->page->setRawResponse($callout, false);
		}

	public function pointsReport() : void
		{
		if ($this->page->addHeader($title = 'Volunteer Points Report'))
			{
			if (isset($_POST['submit']) && 'Download' == $_POST['submit'] && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\Leader($title);
				$report->generatePoints($_POST);
				$this->page->done();
				}
			else
				{
				$view = new \App\View\Leader\Points($this->page);
				$this->page->addPageContent($view->reportSettings());
				}
			}
		}

	public function pointsSettings() : void
		{
		if ($this->page->addHeader($title = 'Volunteer Points Settings'))
			{
			$view = new \App\View\Leader\Points($this->page);
			$this->page->addPageContent($view->pointSettings());
			}
		}
	}
