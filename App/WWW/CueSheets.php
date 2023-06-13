<?php

namespace App\WWW;

class CueSheets extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \PHPFUI\Button $backButton;

	private readonly \App\View\CueSheet $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\CueSheet($this->page);
		$this->backButton = new \PHPFUI\Button('Cue Sheet Configuration', '/CueSheets/configure');
		$this->backButton->addClass('hollow')->addClass('secondary');
		}

	public function acceptEmail() : void
		{
		if ($this->page->addHeader('Accept Cue Sheet Email'))
			{
			$editor = new \App\View\Email\Settings($this->page, 'acceptCue', new \App\Model\Email\CueSheet());
			$editor->addButton($this->backButton);
			$this->page->addPageContent($editor);
			}
		}

	public function addCue() : void
		{
		if ($this->page->addHeader('Add A New Cue Sheet'))
			{
			$this->page->addPageContent($this->view->edit(new \App\Record\CueSheet()));
			}
		}

	public function approve(\App\Record\CueSheet $cuesheet = new \App\Record\CueSheet()) : void
		{
		if ($this->page->addHeader('Approve Cue Sheets'))
			{
			if ($cuesheet->loaded())
				{
				if ($cuesheet['pending'])
					{
					$this->page->addPageContent("<h3>Cue Sheet {$cuesheet->cueSheetId} has been approved</h3>");
					$model = new \App\Model\CueSheet();
					$model->approve($cuesheet, $this->view);
					}
				else
					{
					$this->page->addPageContent('<h3>Cue Sheet has already been approved</h3>');
					}
				}
			else
				{
				$this->page->addPageContent('<h3>Cue Sheet not found</h3>');
				}
			$this->page->redirect('/CueSheets/pending', '', 2);
			}
		}

	public function configure() : void
		{
		if ($this->page->addHeader('Cue Sheet Configuration'))
			{
			$landing = $this->page->mainMenu->getLandingPage($this->page, '/CueSheets/configure');
			$this->page->addPageContent($landing);
			}
		}

	public function coordinator() : void
		{
		if ($this->page->addHeader($name = 'Cue Sheet Coordinator'))
			{
			$view = new \App\View\Coordinators($this->page);
			$this->page->addPageContent($view->getEmail($name));
			}
		}

	public function deny(\App\Record\CueSheet $cuesheet = new \App\Record\CueSheet()) : void
		{
		if ($this->page->addHeader('Reject Cue Sheet'))
			{
			if ($cuesheet->loaded())
				{
				$this->page->addPageContent($this->view->reject($cuesheet));
				}
			}
		}

	public function download(\App\Record\CueSheet $cueSheet = new \App\Record\CueSheet()) : void
		{
		if ($this->page->isAuthorized('View Cue Sheet'))
			{
			$versions = $cueSheet->CueSheetVersionChildren;

			$versionCount = \count($versions);

			if ($versionCount)
				{
				$version = $versions->current();

				if ($versionCount > 1 || empty($version->extension))
					{
					$this->View($cueSheet);
					}
				elseif (empty($version->link))
					{
					$model = new \App\Model\CueSheet();
					$error = $model->download($version);

					if ($error)
						{
						$this->page->addPageContent("<h3>{$error}</h3>");
						}
					else
						{
						$this->page->done();
						}
					}
				else
					{
					$this->page->addHeader('View Cue Sheet');
					$this->page->addSubHeader('Choose A Version');
					$this->page->addPageContent(new \PHPFUI\MultiColumn(
						$this->view->getIconLink($version),
						new \PHPFUI\Link($version->link)
					));
					}
				}
			}
		}

	public function downloadRevision(\App\Record\CueSheetVersion $cuesheetVersion = new \App\Record\CueSheetVersion()) : void
		{
		if ($this->page->isAuthorized('View Cue Sheet'))
			{
			$model = new \App\Model\CueSheet();
			$error = $model->download($cuesheetVersion);

			if ($error)
				{
				$this->page->addPageContent("<h3>{$error}</h3>");
				}
			else
				{
				$this->page->done();
				}
			}
		}

	public function edit(\App\Record\CueSheet $cuesheet = new \App\Record\CueSheet()) : void
		{
		if ($cuesheet->loaded())
			{
			if ($this->page->addHeader('Edit Cue Sheet', '', $cuesheet->memberId == \App\Model\Session::signedInMemberId()))
				{
				$this->page->addPageContent($this->view->edit($cuesheet));
				}
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Cue Sheet not found'));
			}
		}

	public function find() : void
		{
		if ($this->page->addHeader('Search Cue Sheets'))
			{
			$this->page->addPageContent(new \App\View\CueSheet\Search($this->page));
			}
		}

	public function merge() : void
		{
		if ($this->page->addHeader('Merge Cue Sheets'))
			{
			$this->page->addPageContent($this->view->Merge());
			}
		}

	public function my() : void
		{
		if ($this->page->addHeader('My Cue Sheets'))
			{
			$cuesheetTable = new \App\Table\CueSheet();
			$cuesheetTable->setFromMemberCursor(\App\Model\Session::signedInMemberId());

			$this->page->addPageContent($this->view->show($cuesheetTable));
			}
		}

	public function notes() : void
		{
		if ($this->page->addHeader($title = 'Cue Sheet Notes'))
			{
			$content = new \App\View\Content($this->page);
			$this->page->addPageContent($content->getDisplayCategoryHTML($title));
			}
		}

	public function pending() : void
		{
		if ($this->page->addHeader('Approve Cue Sheets'))
			{
			$cuesheetTable = new \App\Table\CueSheet();
			$cuesheetTable->setPendingCursor();

			$this->page->addPageContent($this->view->show($cuesheetTable));
			}
		}

	public function recent() : void
		{
		if ($this->page->addHeader('Recent Cue Sheets'))
			{
			$cuesheetTable = new \App\Table\CueSheet();
			$cuesheetTable->setRecentlyAddedCursor();

			$this->page->addPageContent($this->view->show($cuesheetTable));
			}
		}

	public function rejectEmail() : void
		{
		if ($this->page->addHeader('Reject Cue Sheet Email'))
			{
			$editor = new \App\View\Email\Settings($this->page, 'rejectCue', new \App\Model\Email\CueSheet());
			$editor->addButton($this->backButton);
			$this->page->addPageContent($editor);
			}
		}

	public function statistics(int $year = 0) : void
		{
		if (! $year)
			{
			$year = \App\Tools\Date::format('Y');
			}

		if ($this->page->addHeader('Cue Sheet Statistics'))
			{
			$today = \App\Tools\Date::todayString();
			$first = \App\Table\Ride::getFirstRideWithCueSheet();
			$latest = \App\Table\Ride::getLatestRideWithCueSheet();
			$yearSubNav = new \App\UI\YearSubNav(
				$this->page->getBaseURL(),
				$year,
				(int)\App\Tools\Date::formatString('Y', $first['rideDate'] ?? $today),
				(int)\App\Tools\Date::formatString('Y', $latest['rideDate'] ?? $today)
			);
			$this->page->addPageContent($yearSubNav);

			$this->page->addPageContent($this->view->stats($year));
			}
		}

	public function templates() : void
		{
		if ($this->page->addHeader($title = 'Cue Sheet Templates'))
			{
			$content = new \App\View\Content($this->page);
			$this->page->addPageContent($content->getDisplayCategoryHTML($title));
			}
		}

	public function view(\App\Record\CueSheet $cuesheet = new \App\Record\CueSheet()) : void
		{
		if ($this->page->isAuthorized('View Cue Sheet'))
			{
			if ($cuesheet->loaded())
				{
				$this->page->addPageContent('<h1>Cue Sheet</h1>');
				$this->page->addPageContent("<h3>{$cuesheet->cueSheetId} - {$cuesheet->name}</h3>");
				$cuesheetTable = new \App\Table\CueSheet();
				$cuesheetTable->setWhere(new \PHPFUI\ORM\Condition('cueSheetId', $cuesheet->cueSheetId));
				$this->page->addPageContent($this->view->show($cuesheetTable));
				}
			else
				{
				$this->page->addPageContent('<h3>Cue Sheet not found</h3>');
				}
			}
		}
	}
