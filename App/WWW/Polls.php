<?php

namespace App\WWW;

class Polls extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Polls $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Polls($this->page);
		}

	public function current() : void
		{
		if ($this->page->addHeader('Current Polls'))
			{
			$this->page->addPageContent($this->view->listPolls(\App\Table\Poll::current(), 'No current polls'));
			}
		}

	public function delete(\App\Record\Poll $poll = new \App\Record\Poll()) : void
		{
		if ($this->page->addHeader('Delete Poll'))
			{
			if ($poll->startDate && $poll->startDate <= \App\Tools\Date::todayString())
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('This poll has started and can not be deleted'));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Poll has been deleted.'));
				$poll->delete();
				}
			}
		}

	public function edit(\App\Record\Poll $poll = new \App\Record\Poll()) : void
		{
		$title = $poll->pollId ? 'Edit' : 'Add';

		if ($this->page->addHeader($title . ' Poll'))
			{
			$this->page->addPageContent($this->view->edit($poll));
			}
		}

	public function future() : void
		{
		if ($this->page->addHeader('Future Polls'))
			{
			$this->page->addPageContent($this->view->listPolls(\App\Table\Poll::future(), 'No upcoming polls'));
			}
		}

	public function myVotes() : void
		{
		if ($this->page->addHeader('My Votes'))
			{
			$pollResponseTable = new \App\Table\PollResponse();
			$pollResponseTable->setMyVotesQuery();
			$this->page->addPageContent($this->view->myVotes($pollResponseTable));
			}
		}

	public function myMembershipVotes() : void
		{
		if ($this->page->addHeader('My Membership Votes'))
			{
			$pollResponseTable = new \App\Table\PollResponse();
			$pollResponseTable->setMyMembershipVotesQuery();
			$this->page->addPageContent($this->view->myVotes($pollResponseTable));
			}
		}

	public function past(int $year = 0) : void
		{
		if (! $year)
			{
			$year = \App\Tools\Date::format('Y');
			}

		if ($this->page->addHeader('Past Polls'))
			{
			$today = \App\Tools\Date::todayString();
			$oldest = \App\Table\Poll::oldest();
			$latest = \App\Table\Poll::latest();
			$yearSubNav = new \App\UI\YearSubNav(
				$this->page->getBaseURL(),
				$year,
				(int)\App\Tools\Date::formatString('Y', $oldest->startDate ?? $today),
				(int)\App\Tools\Date::formatString('Y', $latest->endDate ?? $today)
			);
			$this->page->addPageContent($yearSubNav);
			$polls = \App\Table\Poll::byYear($year);
			$this->page->addPageContent($this->view->listPolls($polls, 'No polls in ' . $year));
			}
		}

	public function viewVote(\App\Record\Poll $poll = new \App\Record\Poll()) : void
		{
		if ($this->page->addHeader('View Vote'))
			{
			if (! $poll->empty())
				{
				$this->page->addPageContent($this->view->viewVote($poll));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Poll not found'));
				}
			}
		}

	public function vote(\App\Record\Poll $poll = new \App\Record\Poll()) : void
		{
		if ($this->page->addHeader('Vote'))
			{
			if ($poll->endDate && $poll->endDate < \App\Tools\Date::todayString())
				{
				$this->page->addPageContent($this->view->viewVote($poll));
				}
			elseif ($poll->startDate && $poll->startDate <= \App\Tools\Date::todayString())
				{
				$this->page->addPageContent($this->view->changeVote($poll));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Poll not found'));
				}
			}
		}

	public function voted(\App\Record\Poll $poll = new \App\Record\Poll()) : void
		{
		if ($this->page->addHeader('People Who Voted'))
			{
			if ($poll->startDate && $poll->startDate <= \App\Tools\Date::todayString())
				{
				$this->page->addPageContent($this->view->voted($poll));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Poll not found'));
				}
			}
		}
	}
