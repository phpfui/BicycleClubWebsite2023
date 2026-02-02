<?php

namespace App\View\Text;

class Members implements \Stringable
	{
	/**
	 * @var array<string,string>
	 */
	private array $parameters = [];

	private string $testMessage = 'Send Test Text To You Only';

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->parameters = \App\Model\Session::getFlash('post') ?? [];
		$defaultFields = [];
		$defaultFields['currentMembers'] = 1;
		$defaultFields['newMembers'] = 0;
		$defaultFields['newMonths'] = '';
		$defaultFields['message'] = '';
		$requiredFields = \array_merge(['submit'], \array_keys($defaultFields));
		$defaultFields['categories'] = [0];

		if ($_POST)
			{
			\App\Model\Session::setFlash('post', $_POST);

			foreach ($requiredFields as $field)
				{
				if (! isset($_POST[$field]))
					{
					\App\Model\Session::setFlash('alert', "Missing required field {$field}");
					$this->page->redirect();

					return;
					}
				}
			}

		if (\App\Model\Session::checkCSRF())
			{
			$smsModel = new \App\Model\SMS();
			$sender = \App\Model\Session::signedInMemberRecord();
			$smsModel->setFromMember($sender);
			$message = $_POST['message'];
			$smsModel->setBody($message);

			$extra = '';

			$members = \App\Table\Member::getEmailableMembers(
				true,
				$_POST['currentMembers'],
				($this->page->isAuthorized('Text Past Members') && $_POST['pastMembers']) ? (int)($_POST['months']) : 0,
				$_POST['newMembers'] ? (int)($_POST['newMonths']) : 0,
				$_POST['categories'] ?? $defaultFields['categories'],
				$extra
			);

			if ($_POST['submit'] == $this->testMessage)
				{
				$smsModel->textMember($sender);
				\App\Model\Session::setFlash('success', 'Check your inbox for a test text.  It would have been sent to ' . \count($members) . ' members');
				$this->page->redirect();
				}
			else
				{
				foreach ($members as $member)
					{
					$smsModel->textMember($member);
					}
				\App\Model\Session::setFlash('success', 'You texted ' . \count($members) . ' club members');
				$this->page->redirect();
				}
			}
		}

	public function __toString() : string
		{
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Selection Criteria');
		$picker = new \App\UI\MultiCategoryPicker('categories', 'Category Restriction', $this->parameters['categories'] ?? []);
		$picker->setToolTip('Pick specific categories if you to restrict the text, optional');
		$memberTypes = new \PHPFUI\FieldSet('Membership Types');
		$currentMembers = new \PHPFUI\Input\CheckBoxBoolean('currentMembers', 'Current', $this->parameters['currentMembers'] ?? true);
		$currentMembers->setToolTip('Check to send to current members of the club');
		$memberTypes->add($currentMembers);

		if ($this->page->isAuthorized('Text Past Members'))
			{
			$multiColumn = new \PHPFUI\MultiColumn();
			$pastMembers = new \PHPFUI\Input\CheckBoxBoolean('pastMembers', 'Lapsed', $this->parameters['pastMembers'] ?? false);
			$pastMembers->setToolTip('Check to send to past members of the club who have not renewed.  Make sure the enter the number of months back of lapsed members.');
			$multiColumn->add($pastMembers);
			$months = new \PHPFUI\Input\Number('months', 'Months Lapsed', $this->parameters['months'] ?? '');
			$months->setToolTip('Lapsed members up to this number of months back texted');
			$multiColumn->add($months);
			$memberTypes->add($multiColumn);
			}
		$multiColumn = new \PHPFUI\MultiColumn();
		$newMembers = new \PHPFUI\Input\CheckBoxBoolean('newMembers', 'New', $this->parameters['newMembers'] ?? false);
		$newMembers->setToolTip('Check to send to recently joined members.  Make sure the enter the number of months they have been a member.');
		$multiColumn->add($newMembers);
		$newMonths = new \PHPFUI\Input\Number('newMonths', 'Months New', $this->parameters['newMonths'] ?? '');
		$newMonths->setToolTip('Members this number of months back and newer will be texted');
		$multiColumn->add($newMonths);
		$memberTypes->add($multiColumn);
		$container = new \PHPFUI\Container($memberTypes);
		$fieldSet->add(new \PHPFUI\MultiColumn($picker, $container));
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Text (1600 characters max)');
		$message = new \PHPFUI\Input\TextArea('message', 'Message', $this->parameters['message'] ?? '');
		$message->addAttribute('placeholder', 'Message to all members?');
		$message->setRequired()->addAttribute('maxlength', '1600');
		$fieldSet->add($message);
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$textAll = new \PHPFUI\Submit('Text All Members');
		$textAll->setConfirm('Are you sure you want to text all members?');
		$buttonGroup->addButton($textAll);
		$test = new \PHPFUI\Submit($this->testMessage);
		$test->addClass('warning');
		$buttonGroup->addButton($test);
		$form->add($buttonGroup);
		$output = $form;

		return (string)$output;
		}
	}
