<?php

namespace App\View\Volunteer;

class Jobs
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function list(\App\Record\JobEvent $jobEvent) : string
		{
		$output = '';
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteJob':

					$job = new \App\Record\Job((int)$_POST['jobId']);
					$job->delete();
					$this->page->setResponse($_POST['jobId']);

					break;


				case 'Add':

					$job = new \App\Record\Job();
					$job->setFrom($_POST);
					$job->insert();
					$this->page->redirect();

					break;


				default:

					$this->page->redirect();

				}
			}
		else
			{
			if ($jobEvent->empty())
				{
				$this->page->redirect('/Volunteer/events');
				}
			$add = new \PHPFUI\Button('Add New Job');
			$add->addClass('success');
			$modal = $this->addJobModal($add, $jobEvent);

			$form->add(new \PHPFUI\SubHeader($jobEvent->name));
			$form->add(new \App\View\Volunteer\Menu($jobEvent, 'Jobs'));

			$jobTable = new \App\Table\Job();
			$jobs = $jobTable->getJobs($jobEvent->jobEventId);
			$form->saveOnClick($add);
			$delete = new \PHPFUI\AJAX('deleteJob', 'Permanently delete this job and all associated shifts?');
			$delete->addFunction('success', '$("#jobId-"+data.response).css("background-color","red").hide("fast").remove()');
			$this->page->addJavaScript($delete->getPageJS());
			$table = new \PHPFUI\Table();
			$table->setRecordId('jobId');
			$table->addHeader('date', 'Date');
			$table->addHeader('title', 'Title (Click To Edit)');
			$table->addHeader('location', 'Location');
			$table->addHeader('delete', 'Del');

			if (! \count($jobs))
				{
				$modal->showOnPageLoad();
				}

			foreach ($jobs as $job)
				{
				$row = $job->toArray();
				$id = $row['jobId'];
				$row['title'] = "<a href='/Volunteer/jobEdit/{$id}'>{$row['title']}</a>";
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['jobId' => $id]));
				$row['delete'] = $icon;
				$table->addRow($row);
				}
			$form->add($table);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($add);
			$form->add($buttonGroup);
			$output = (string)$form;
			}

		return $output;
		}

	public function showJob(\App\Record\Job $job) : string
		{
		$fieldSet = new \PHPFUI\FieldSet('Job Details:');
		$fieldSet->add(new \App\UI\Display('Date of Job', $job->date));
		$fieldSet->add(new \App\UI\Display('Job Title', $job->title));
		$fieldSet->add(new \App\UI\Display('Location', $job->location));
		$fieldSet->add(new \App\UI\Display('Job Description', $job->description));

		return (string)$fieldSet;
		}

	private function addJobModal(\PHPFUI\HTML5Element $modalLink, \App\Record\JobEvent $jobEvent) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$jobEdit = new \App\View\Volunteer\JobEdit($this->page);
		$form = $jobEdit->getJobForm($jobEvent, new \App\Record\Job());
		$form->setAreYouSure(false);
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}
	}
