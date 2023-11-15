<?php

namespace App\View;

class Polls
	{
	private readonly \App\Model\Poll $model;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->model = new \App\Model\Poll();
		$this->processRequest();
		}

	public function changeVote(\App\Record\Poll $poll, bool $postBack = true) : string | \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Vote');

		if ($postBack)
			{
			$form = new \PHPFUI\Form($this->page, $submit);

			if ($form->isMyCallback())
				{
				$_POST['pollId'] = $poll->pollId;
				$this->model->saveVote($poll, $_POST);
				$this->page->setResponse('Vote Recorded');

				return '';
				}
			}
		else
			{
			$form = new \PHPFUI\Form($this->page);
			}
		$form->add($this->getQuestion($poll));
		$yourVote = new \PHPFUI\FieldSet('Your Vote');
		$vote = $this->model->getVote($poll);
		$radio = new \PHPFUI\Input\RadioGroup('answer', '', (string)$vote);
		$radio->setSeparateRows();
		$radio->setRequired();

		foreach ($poll->PollAnswerChildren as $answer)
			{
			$radio->addButton($answer->answer, (string)$answer->pollAnswerId);
			}
		$yourVote->add($radio);
		$form->add($yourVote);
		$form->add($submit);

		return $form;
		}

	public function edit(\App\Record\Poll $poll) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if ($poll->pollId)
			{
			$submit = new \PHPFUI\Submit();
			$form = new \App\UI\ErrorFormSaver($this->page, $poll, $submit);

			if (! $this->page->isAuthorized('Edit Poll After Start') && $poll->startDate <= \App\Tools\Date::todayString())
				{
				$container->add(new \PHPFUI\SubHeader('This poll has started and can not be edited'));

				return $container;
				}
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add', 'action');
			$poll->memberId = \App\Model\Session::signedInMemberId();
			$form = new \App\UI\ErrorFormSaver($this->page, $poll);
			}

		if ($form->save())
			{
			if (isset($_POST['answer']) && \is_array($_POST['answer']))
				{
				$pollAnswerTable = new \App\Table\PollAnswer();
				$pollAnswerTable->saveAnswers($poll->pollId, $_POST['answer']);
				}

			return $container;
			}
		$form->add(new \PHPFUI\Input\Hidden('pollId', (string)$poll->pollId));
		$form->add(new \PHPFUI\Input\Hidden('memberId', (string)$poll->memberId));
		$questionGroup = new \PHPFUI\FieldSet('Question');
		$question = new \PHPFUI\Input\TextArea('question', '', $poll->question);
		$question->setRequired();
		$question->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$questionGroup->add($question);
		$form->add($questionGroup);
		$answerGroup = new \PHPFUI\FieldSet('Answers');
		$ul = new \PHPFUI\UnorderedList($this->page);

		foreach ($poll->PollAnswerChildren as $answer)
			{
			$ul->addItem($this->getAnswerItem($answer));
			}
		$answerGroup->add($ul);
		$add = new \PHPFUI\Button('Add Answer');
		$newField = $this->getAnswerItem();
		$newField = \str_replace(["\n", '"', "'"], ['', '\x22', '\x27'], $newField);
		$add->addAttribute('onclick', '$("#' . $ul->getId() . '").append("' . $newField . '");');
		$this->page->addJavaScript('$("#' . $add->getId() . '").click(function(e){e.preventDefault();})');
		$answerGroup->add($add);
		$form->add($answerGroup);
		$settingGroup = new \PHPFUI\FieldSet('Settings');
		$startDate = new \PHPFUI\Input\Date($this->page, 'startDate', 'Start Date', $poll->startDate);
		$startDate->setRequired();
		$endDate = new \PHPFUI\Input\Date($this->page, 'endDate', 'End Date', $poll->endDate);
		$endDate->setRequired();
		$settingGroup->add(new \PHPFUI\MultiColumn($startDate, $endDate));
		$required = new \PHPFUI\Input\CheckBoxBoolean('required', 'Required', (bool)$poll->required);
		$required->setToolTip('If checked, members will be required to answer this when they sign in.');
		$membershipOnly = new \PHPFUI\Input\CheckBoxBoolean('membershipOnly', '1 Vote<wbr>/<wbr>Membership', (bool)$poll->membershipOnly);
		$membershipOnly->setToolTip('If checked, each membership will only have one vote, but any member can change it.');
		$emailConfirmation = new \PHPFUI\Input\CheckBoxBoolean('emailConfirmation', 'Email Confirm', (bool)$poll->emailConfirmation);
		$emailConfirmation->setToolTip('If checked, an email will be set to the member and the Secretary');
		$settingGroup->add(new \PHPFUI\MultiColumn($membershipOnly, $emailConfirmation));
		$member = new \App\Record\Member($poll->memberId);
		$settingGroup->add(new \PHPFUI\MultiColumn($required, '<strong>Author:</strong> ' . ($member->firstName ?? '') . ' ' . ($member->lastName ?? '')));
		$storyPicker = new \App\UI\StoryPicker($this->page, 'storyId', 'Optional story to include before poll', $poll->story);
		$settingGroup->add($storyPicker->getEditControl());
		$form->add($settingGroup);
		$form->add($submit);
		$container->add($form);

		return $container;
		}

	public function listPolls(\PHPFUI\ORM\RecordCursor $polls, string $default = 'No Polls Found') : \PHPFUI\Table | \PHPFUI\SubHeader
		{
		if (! \count($polls))
			{
			return new \PHPFUI\SubHeader($default);
			}
		$table = new \PHPFUI\Table();
		$headers = ['question' => 'Question', 'startDate' => 'Start', 'endDate' => 'End', 'vote' => 'Vote',
			'voted' => 'Voted', ];

		if ($this->page->isAuthorized('Edit Poll'))
			{
			$headers['edit'] = 'Edit';
			}

		if ($this->page->isAuthorized('Delete Poll'))
			{
			$headers['del'] = 'Del';
			}
		$table->setHeaders($headers);

		foreach ($polls as $pollObject)
			{
			$poll = $pollObject->toArray();

			if ($pollObject->startDate > \App\Tools\Date::todayString())
				{
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '/Polls/delete/' . $pollObject->pollId);
				$icon->setConfirm('Are you sure you want to delete this poll?');
				$poll['del'] = $icon;
				}
			$poll['question'] = $this->minimizeQuestion($pollObject->question);
			$poll['voted'] = new \PHPFUI\FAIcon('fas', 'users', '/Polls/voted/' . $pollObject->pollId);
			$poll['vote'] = new \PHPFUI\FAIcon('fas', 'check-square', '/Polls/vote/' . $pollObject->pollId);
			$poll['edit'] = new \PHPFUI\FAIcon('far', 'edit', '/Polls/edit/' . $pollObject->pollId);
			$table->addRow($poll);
			}

		return $table;
		}

	public function myVotes(\App\Table\PollResponse $pollResponseTable) : \App\UI\ContinuousScrollTable
		{
		$headers = ['question', 'endDate'];

		$view = new \App\UI\ContinuousScrollTable($this->page, $pollResponseTable);
		$view->addCustomColumn('question', fn (array $vote) => $this->minimizeQuestion($vote['question'] ?? ''));
		$view->addCustomColumn('pollId', static fn (array $vote) => new \PHPFUI\FAIcon('fas', 'check-square', '/Polls/vote/' . $vote['pollId']));
		$view->addCustomColumn('memberId', static function(array $vote) { $member = new \App\Record\Member($vote['memberId']);

return $member->fullName();});
		$view->setSearchColumns($headers);
		$headers[] = 'answer';
		$view->setSortableColumns($headers);
		$headers['memberId'] = 'Who';
		$headers['pollId'] = 'Vote';
		$view->setHeaders($headers);

		return $view;
		}

	public function viewVote(\App\Record\Poll $poll) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$container->add($this->getQuestion($poll));
		$pollResponseTable = new \App\Table\PollResponse();
		$totals = $pollResponseTable->getVotes($poll->pollId);
		$totalVotes = 0;

		foreach ($totals as $total)
			{
			$totalVotes += $total->count;
			}

		foreach ($totals as $total)
			{
			$row = new \PHPFUI\GridX();
			$label = new \PHPFUI\Cell(3, 3, 2);
			$strong = new \PHPFUI\HTML5Element('strong');
			$strong->add($total->answer . ' - ' . $total->count);
			$label->add($strong);
			$row->add($label);
			$graph = new \PHPFUI\Cell(9, 9, 10);
			$progressBar = new \PHPFUI\ProgressBar();
			$percent = $total->count * 100.0 / (float)$totalVotes;
			$progressBar->setPercent((int)\round($percent));
			$graph->add($progressBar);
			$row->add($graph);
			$container->add($row);
			}

		return $container;
		}

	public function voted(\App\Record\Poll $poll) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$container->add($this->getQuestion($poll));
		$container->add($this->getAnswers($poll));
		$container->add(new \PHPFUI\SubHeader('The following people have voted'));
		$table = new \PHPFUI\Table();
		$headers = ['firstName', 'lastName'];
		$table->setHeaders($headers);
		$pollResponseTable = new \App\Table\PollResponse();
		$pollResponseTable->setVotersQuery($poll->pollId);
		$numberVoters = \count($pollResponseTable);

		$view = new \App\UI\ContinuousScrollTable($this->page, $pollResponseTable);
		$view->setHeaders($headers);

		if (! $poll->membershipOnly)
			{
			$view->setSearchColumns($headers)->setSortableColumns($headers);
			}
		$view->addCustomColumn('firstName', static fn (array $response) => $response['memberId'] ? $response['firstName'] : \App\Table\Membership::getMembershipsLastNames($response['membershipId']));
		$view->addCustomColumn('lastName', static fn (array $response) => $response['memberId'] ? $response['lastName'] : 'Membership');
		$container->add($view);

		$container->add(new \App\UI\Display('Total Votes', $numberVoters));

		return $container;
		}

	private function getAnswerItem(\App\Record\PollAnswer $answer = new \App\Record\PollAnswer()) : \PHPFUI\ListItem
		{
		$row = new \PHPFUI\GridX();
		$listItem = new \PHPFUI\ListItem($row);
		$titleColumn = new \PHPFUI\Cell(11);
		$titleColumn->add(new \PHPFUI\Input\Hidden('pollAnswerId[]', (string)$answer->pollAnswerId));
		$titleColumn->add(new \PHPFUI\Input\Text('answer[]', '', $answer->answer));
		$row->add($titleColumn);

		if ($answer->pollId)
			{
			$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$trash->addClass('float-right');
			$trash->addAttribute('onclick', '$("#' . $listItem->getId() . '").remove();');
			$trashColumn = new \PHPFUI\Cell(1);
			$trashColumn->addClass('clearfix');
			$trashColumn->add($trash);
			$row->add($trashColumn);
			}

		return $listItem;
		}

	private function getAnswers(\App\Record\Poll $poll) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Answers');
		$ul = new \PHPFUI\UnorderedList();

		foreach ($poll->PollAnswerChildren as $answer)
			{
			$ul->addItem(new \PHPFUI\ListItem($answer->answer));
			}
		$fieldSet->add($ul);

		return $fieldSet;
		}

	private function getQuestion(\App\Record\Poll $poll) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$story = $poll->story;

		if ($story->loaded())
			{
			$view = new \App\View\Content($this->page);
			$container->add($view->getStoryHTML($story));
			}
		$fieldSet = new \PHPFUI\FieldSet('Question');
		$fieldSet->add($poll->question);
		$container->add($fieldSet);

		return $container;
		}

	private function minimizeQuestion(string $question) : string
		{
		$length = 50;
		$question = \substr($question, 0, $length);

		if (\strlen($question) == $length)
			{
			$question .= '...';
			}

		return $question;
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'Add':
						unset($_POST['pollId']);
						$poll = new \App\Record\Poll();
						$poll->setFrom($_POST);
						$pollId = $poll->insert();

						if (isset($_POST['answer']) && \is_array($_POST['answer']))
							{
							$pollAnswerTable = new \App\Table\PollAnswer();
							$pollAnswerTable->saveAnswers($pollId, $_POST['answer']);
							}
						$this->page->redirect('/Polls/edit/' . $pollId);

						break;
					}
				}
			}
		}
	}
