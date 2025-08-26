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
		$rowId = 'volunteerPollId';
		$submit = new \PHPFUI\Submit('Save')->addClass('sucess');
		$form = new \App\UI\ErrorFormSaver($this->page, $this->jobEvent, $submit);
		$volunteerPollTable = new \App\Table\VolunteerPoll();

		$post = $_POST;

		if (($post['action'] ?? '') === 'deletePoll')
			{
			$volunteerPoll = new \App\Record\VolunteerPoll((int)$post[$rowId]);
			$volunteerPoll->delete();
			$this->page->setResponse($post[$rowId])->setDone();

			return '';
			}
		elseif (($post['submit'] ?? '') == 'Add')
			{
			$volunteerPoll = new \App\Record\VolunteerPoll();
			$volunteerPoll->setFrom($post);
			$volunteerPoll->insert();
			$this->page->redirect();

			return '';
			}

		if ($form->save())
			{
			$order = 1;

			foreach ($post[$rowId] ?? [] as $index => $value)
				{
				$post['ordering'][$index] = $order++;
				}
			\App\Tools\Logger::get()->debug($post);

			$volunteerPollTable->updateFromTable($post);

			return $form;
			}

		$add = new \PHPFUI\Button('Add New Poll')->addClass('success');
		$this->addPollModal($add);

		if ($this->jobEvent->empty())
			{
			$this->page->redirect('/Volunteer/events');
			}
		$form->add(new \PHPFUI\SubHeader($this->jobEvent->name));
		$form->add(new \App\View\Volunteer\Menu($this->jobEvent, 'Polls'));
		$polls = $volunteerPollTable->getPolls($this->jobEvent);
		$form->saveOnClick($add);

		$delete = new \PHPFUI\AJAX('deletePoll', 'Permanently delete this poll?');
		$delete->addFunction('success', '$("#' . $rowId . '-"+data.response).css("background-color","red").hide("fast").remove()');
		$this->page->addJavaScript($delete->getPageJS());
		$table = new \PHPFUI\OrderableTable($this->page);
		$table->setRecordId($rowId);
		$table->addHeader('question', 'Question (click to edit, or drag and drop to change ordering)');
		$table->addHeader('delete', 'Del');

		foreach ($polls as $poll)
			{
			$row = $poll->toArray();
			$id = $poll->{$rowId};
			$row['question'] = "<a href='/Volunteer/pollEdit/{$id}'>{$poll->question}</a>" . new \PHPFUI\Input\Hidden("{$rowId}[{$id}]", (string)$id);
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $delete->execute([$rowId => $id]));
			$row['delete'] = $icon;
			$table->addRow($row);
			}
		$form->add($table);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton($submit);
		$buttonGroup->addButton($add);
		$form->add($buttonGroup);
		$output = $form;

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
