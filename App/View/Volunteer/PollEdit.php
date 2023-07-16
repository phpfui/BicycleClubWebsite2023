<?php

namespace App\View\Volunteer;

class PollEdit
	{
	private readonly \App\Table\VolunteerPollAnswer $volunteerPollAnswerTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->volunteerPollAnswerTable = new \App\Table\VolunteerPollAnswer();
		$this->processAJAXRequest();
		}

	public function getPollForm(\App\Record\JobEvent $jobEvent, \App\Record\VolunteerPoll $volunteerPoll, ?\PHPFUI\Submit $submit = null) : \App\UI\ErrorFormSaver
		{
		$form = new \App\UI\ErrorFormSaver($this->page, $jobEvent, $submit);

		if ($form->save())
			{
			$this->volunteerPollAnswerTable->updateFromTable($_POST);

			return $form;
			}

		if ($jobEvent->empty())
			{
			$this->page->redirect('/Volunteer/events');
			}

		$fieldSet = new \PHPFUI\FieldSet('Poll for ' . $jobEvent->name);
		$fieldSet->add(new \PHPFUI\Input\Hidden('jobEventId', (string)$jobEvent->jobEventId));
		$fieldSet->add(new \PHPFUI\Input\Hidden('volunteerPollId', (string)$volunteerPoll->volunteerPollId));
		$title = new \PHPFUI\Input\Text('question', 'Poll Question', $volunteerPoll->question ?? '');
		$title->setRequired()->setToolTip('This is the only thing the person will see for the poll, so make it clear and descriptive');
		$fieldSet->add($title);

		if ($volunteerPoll->volunteerPollId) // if previously saved, allow editing of poll answers
			{
			$answers = $this->volunteerPollAnswerTable->getPollAnswers($volunteerPoll->volunteerPollId);
			$delete = new \PHPFUI\AJAX('deleteAnswer', 'Permanently delete this answer and all responses?');
			$delete->addFunction('success', '$("#volunteerPollAnswerId-"+data.response).css("background-color","red").hide("fast").remove();');
			$this->page->addJavaScript($delete->getPageJS());
			$table = new \PHPFUI\Table();
			$table->setRecordId('volunteerPollAnswerId');
			$table->addHeader('answer', 'Answers');
			$table->addHeader('delete', 'Del');

			foreach ($answers as $answer)
				{
				$row = $answer->toArray();
				$id = (int)$row['volunteerPollAnswerId'];
				$input = new \PHPFUI\Input\Text("answer[{$id}]", '', $row['answer']);
				$hidden = new \PHPFUI\Input\Hidden("volunteerPollAnswerId[{$id}]", $row['volunteerPollAnswerId']);
				$row['answer'] = $input . $hidden;
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['volunteerPollAnswerId' => (string)$id]));
				$row['delete'] = $icon;
				$table->addRow($row);
				}
			$fieldSet->add($table);
			$submit = new \PHPFUI\Button('Add Answer');
			$submit->addClass('success');
			$form->saveOnClick($submit);
			$this->addAnswerModal($submit, $volunteerPoll);
			$fieldSet->add($submit);
			}
		$form->add($fieldSet);

		return $form;
		}

	public function output(\App\Record\VolunteerPoll $volunteerPoll) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$jobEvent = $volunteerPoll->jobEvent;
		$form = $this->getPollForm($jobEvent, $volunteerPoll, $submit);
		$form->addAsFirst(new \App\View\Volunteer\Menu($jobEvent, 'Polls'));
		$form->addAsFirst(new \PHPFUI\SubHeader($jobEvent->name));

		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton($submit);
		$allPolls = new \PHPFUI\Button('All Polls', '/Volunteer/polls/' . $volunteerPoll->jobEventId);
		$allPolls->addClass('hollow');
		$buttonGroup->addButton($allPolls);
		$form->add($buttonGroup);

		return $form;
		}

	protected function processAJAXRequest() : void
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'Add Answer':

					$volunteerPollAnswer = new \App\Record\VolunteerPollAnswer();
					$volunteerPollAnswer->setFrom($_POST);
					$volunteerPollAnswer->insert();
					$this->page->redirect();

					break;


				case 'deleteAnswer':

					$volunteerPollAnswer = new \App\Record\VolunteerPollAnswer((int)$_POST['volunteerPollAnswerId']);
					$volunteerPollAnswer->delete();
					$volunteerPollResponseTable = new \App\Table\VolunteerPollResponse();
					$volunteerPollResponseTable->setWhere(new \PHPFUI\ORM\Condition('answer', $_POST['volunteerPollAnswerId']));
					$volunteerPollResponseTable->delete();
					$this->page->setResponse($_POST['volunteerPollAnswerId']);

					break;

				}
			}
		}

	private function addAnswerModal(\PHPFUI\HTML5Element $modalLink, \App\Record\VolunteerPoll $volunteerPoll) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet("Add answer to '{$volunteerPoll->question}'");
		$fieldSet->add(new \PHPFUI\Input\Hidden('volunteerPollId', (string)$volunteerPoll->volunteerPollId));
		$title = new \PHPFUI\Input\Text('answer', 'Answer');
		$title->setRequired()->setToolTip('Possible answer to poll.');
		$fieldSet->add($title);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add Answer', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
