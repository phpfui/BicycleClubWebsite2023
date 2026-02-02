<?php

namespace App\View\Text;

class Leaders implements \Stringable
	{
	private string $testText = 'Sent Test Text';

	private string $textText = 'Text All Ride Leaders';

	public function __construct(private readonly \App\View\Page $page)
		{
		if (\App\Model\Session::checkCSRF())
			{
			$message = 'Unknown command';
			$status = 'error';
			$post = $_POST;
			$post['categories'] ??= [];
			$post['fromDate'] ??= \App\Tools\Date::todayString();
			$post['toDate'] ??= \App\Tools\Date::todayString(100);
			\App\Model\Session::setFlash('post', $post);

			$type = empty($post['coordinatorsOnly']) ? 'Ride Leader' : 'Ride Coordinator';
			$leaders = \App\Table\Member::getLeaders($post['categories'], $type, $post['fromDate'], $post['toDate'], $post['minLed'], $post['maxLed']);
			$smsModel = new \App\Model\SMS();
			$member = \App\Model\Session::signedInMemberRecord();
			$smsModel->setFromMember($member);
			$smsModel->setBody(\App\Tools\TextHelper::cleanUserHtml($post['message']));

			if ($post['submit'] == $this->textText)
				{
				foreach ($leaders as $leader)
					{
					$smsModel->textMember($leader);
					}
				$message = 'Your text was sent to ' . \count($leaders) . ' leaders';
				$status = 'success';
				}
			elseif ($post['submit'] == $this->testText)
				{
				$smsModel->textMember($member);
				$message = '<b>Check your phone for a test text. Your text would be sent to the following ' . \count($leaders) . ' leaders:</b>';
				$multiColumn = new \PHPFUI\MultiColumn();

				foreach ($leaders as $leader)
					{
					$multiColumn->add($leader->fullName());

					if (4 == \count($multiColumn))
						{
						$message .= $multiColumn;
						$multiColumn = new \PHPFUI\MultiColumn();
						}
					}

				if (\count($multiColumn))
					{
					while (\count($multiColumn) < 4)
						{
						$multiColumn->add('&nbsp;');
						}
					$message .= $multiColumn;
					}
				$status = 'success';
				}
			\App\Model\Session::setFlash($status, $message);
			$this->page->redirect();
			}
		}

	public function __toString() : string
		{
		$post = \App\Model\Session::getFlash('post');

		$form = new \PHPFUI\Form($this->page);

		$selectionCriteria = new \App\View\Leader\SelectionCriteria($this->page);

		$form->add($selectionCriteria->get($post ?? []));

		$fieldSet = new \PHPFUI\FieldSet('Text (1600 characters max)');
		$message = new \PHPFUI\Input\TextArea('message', 'Message', $post['message'] ?? '');
		$message->addAttribute('placeholder', 'Message to leaders?');
		$message->setRequired()->addAttribute('maxlength', '1600');
		$fieldSet->add($message);
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$sendButton = new \PHPFUI\Submit($this->textText);
		$sendButton->setConfirm('Text all leaders?');
		$sendButton->addClass('warning');
		$buttonGroup->addButton($sendButton);
		$testButton = new \PHPFUI\Submit($this->testText);
		$testButton->addClass('success');
		$buttonGroup->addButton($testButton);
		$form->add($buttonGroup);

		return (string)$form;
		}
	}
