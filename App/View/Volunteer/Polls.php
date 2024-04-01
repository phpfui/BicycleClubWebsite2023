<?php

namespace App\View\Volunteer;

class Polls implements \Stringable
	{
	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\JobEvent $jobEvent)
		{
		}

	public function __toString() : string
		{
		$output = '';
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if (\App\Model\Session::checkCSRF())
			{
			switch ($_POST['submit'] ?? $_POST['action'] ?? '')
				{
				case 'deletePoll':

					$volunteerPoll = new \App\Record\VolunteerPoll((int)$_POST['volunteerPollId']);
					$volunteerPoll->delete();
					$this->page->setResponse($_POST['volunteerPollId']);

					break;


				case 'Add':

					$volunteerPoll = new \App\Record\VolunteerPoll();
					$volunteerPoll->setFrom($_POST);
					$volunteerPoll->insert();
					$this->page->redirect();

					break;


				default:

					$this->page->redirect();

				}
			}
		else
			{
			$add = new \PHPFUI\Button('Add New Poll');
			$add->addClass('success');
			$this->addPollModal($add);

			if ($this->jobEvent->empty())
				{
				$this->page->redirect('/Volunteer/events');
				}
			$form->add(new \PHPFUI\SubHeader($this->jobEvent->name));
			$form->add(new \App\View\Volunteer\Menu($this->jobEvent, 'Polls'));
			$volunteerPollTable = new \App\Table\VolunteerPoll();
			$polls = $volunteerPollTable->getPolls($this->jobEvent);
			$form->saveOnClick($add);
			$delete = new \PHPFUI\AJAX('deletePoll', 'Permanently delete this poll?');
			$delete->addFunction('success', '$("#volunteerPollId-"+data.response).css("background-color","red").hide("fast").remove()');
			$this->page->addJavaScript($delete->getPageJS());
			$table = new \PHPFUI\Table();
			$table->setRecordId('volunteerPollId');
			$table->addHeader('question', 'Question (click to edit)');
			$table->addHeader('delete', 'Del');

			foreach ($polls as $poll)
				{
				$row = $poll->toArray();
				$id = $row['volunteerPollId'];
				$row['question'] = "<a href='/Volunteer/pollEdit/{$id}'>{$row['question']}</a>";
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['volunteerPollId' => $id]));
				$row['delete'] = $icon;
				$table->addRow($row);
				}
			$form->add($table);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($add);
			$form->add($buttonGroup);
			$output = $form;
			}

		return (string)$output;
		}

	private function addPollModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$pollEdit = new \App\View\Volunteer\PollEdit($this->page);
		$form = $pollEdit->getPollForm($this->jobEvent, new \App\Record\VolunteerPoll());
		$form->setAreYouSure(false);
		$submit = new \PHPFUI\Submit('Add');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
