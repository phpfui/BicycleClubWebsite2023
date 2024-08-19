<?php

namespace App\WWW;

class Volunteer extends \App\Common\WWW\Volunteer
	{
	public function myPoints(\App\Record\Member $member = new \App\Record\Member(), int $year = 0) : void
		{
		if ($member->empty() || ! $this->page->isAuthorized('Outstanding Volunteer Points'))
			{
			$member = new \App\Record\Member(\App\Model\Session::signedInMemberId());
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

		if ($memberId != \App\Model\Session::signedInMemberId() || ! $this->page->isAuthorized('Outstanding Volunteer Points'))
			{
			$memberId = \App\Model\Session::signedInMemberId();
			}

		if ((int)($_GET['pointsAwarded'] ?? 0) > 0)
			{
			$callout = new \PHPFUI\Callout('success');
			$callout->add("Points {$_GET['pointsAwarded']} credited");
			}
		else
			{
			$callout = new \PHPFUI\Callout('warning');

			switch($_GET['table'] ?? '')
				{
				case \App\Table\SigninSheet::class:

					$callout->add('The Sign In Sheet has not been approved yet');

					break;

				case \App\Table\CueSheet::class:

					$callout->add('The Cue Sheet has not been approved yet');

					break;

				case \App\Table\VolunteerPoint::class:

					$callout->add('Shift was not marked as worked');

					break;

				case \App\Table\AssistantLeader::class:
				case \App\Table\Ride::class:

					$ride = new \App\Record\Ride($_GET['rideId'] ?? 0);

					if ($ride->loaded())
						{
						$model = new \App\Model\Volunteer();
						$assistantLeaders = $ride->assistantLeaders;
						$error = $model->validateRide($ride, $assistantLeaders);

						if ($error)
							{
							$callout->add($error);
							}
						}
					else
						{
						$callout->add('Ride not found');
						}

					break;

				default:
					$callout->add("Table {$_GET['table']} not found");
				}
			}
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
