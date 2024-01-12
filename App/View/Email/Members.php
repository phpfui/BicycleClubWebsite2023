<?php

namespace App\View\Email;

class Members implements \Stringable
	{
	/**
	 * @var array<string,string>
	 */
	private array $parameters = [];

	private string $testMessage = 'Send Test Email To You Only';

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->parameters = \App\Model\Session::getFlash('post') ?? [];
		$defaultFields = [];
		$defaultFields['currentMembers'] = 1;

		if ($this->page->isAuthorized('Email Past Members'))
			{
			$defaultFields['pastMembers'] = 0;
			$defaultFields['months'] = '';
			}
		$defaultFields['newMembers'] = 0;
		$defaultFields['newMonths'] = '';
		$defaultFields['eventDate'] = '';
		$defaultFields['subject'] = '';
		$defaultFields['message'] = '';
		$defaultFields['town'] = '';
		$defaultFields['zipCodes'] = '';
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
			$email = new \App\Tools\EMail();
			$sender = \App\Model\Session::getSignedInMember();
			$email->setSubject($_POST['subject']);
			$name = $sender['firstName'] . ' ' . $sender['lastName'];
			$emailAddress = $sender['email'];
			$phone = $sender['phone'];
			$email->setFromMember($sender);
			$email->setHtml();
			$settings = new \App\Table\Setting();
			$link = $settings->value('homePage');
			$message = \App\Tools\TextHelper::cleanUserHtml($_POST['message']) . "<p>This email was sent to all members from <a href='{$link}'>{$link}</a> by {$name} {$emailAddress} ";
			$message .= \PHPFUI\Link::phone($phone);
			$message .= "</p><p>You can edit your email preferences <a href='{$link}/Membership/myNotifications'>here</a>";
			$message .= " or <a href='{$link}/Membership/~unsubscribe~'>Unsubscribe Here</a></p>";
			$email->setBody($message);

			if (isset($_FILES['file']))
				{
				if (! $_FILES['file']['error'])
					{
					$file = $_FILES['file']['tmp_name'];

					if (\is_uploaded_file($file))
						{
						$email->addAttachment(\file_get_contents($file), $_FILES['file']['name']);
						\App\Model\Session::setFlash('warning', 'If you want to resend this email, you must select the file again.  Sorry about that!');
						}
					}
				}
			$email->setHtml();
			$extra = '';

			if ($this->page->isAuthorized('Email All Members Criteria'))
				{
				$zips = [];
				$codes = \explode(',', $_POST['zipCodes'] ?? '');

				foreach ($codes as $code)
					{
					$zip = (int)$code;

					if ($zip)
						{
						$zips[] = \sprintf('%05d', $zip);
						}
					}

				if (\count($zips))
					{
					$extra = ' and s.zip in (' . \implode(',', $zips) . ')';
					}

				if (! empty($_POST['town']))
					{
					$town = \preg_replace('/[^A-Za-z\ ]/', '', (string)$_POST['town']);
					$extra .= " and s.town='{$town}'";
					}
				}
			$members = \App\Table\Member::getEmailableMembers(
				! empty($_POST['allMembers']),
				$_POST['currentMembers'],
				($this->page->isAuthorized('Email Past Members') && $_POST['pastMembers']) ? (int)($_POST['months']) : 0,
				$_POST['newMembers'] ? (int)($_POST['newMonths']) : 0,
				$_POST['categories'] ?? $defaultFields['categories'],
				$extra
			);

			if ($_POST['submit'] == $this->testMessage)
				{
				$email->addToMember($sender);
				$email->send();
				\App\Model\Session::setFlash('success', 'Check your inbox for a test email.  It would have been sent to ' . \count($members) . ' members');
				$this->page->redirect();
				}
			else
				{
				if (empty($_POST['journalOnly']))
					{
					foreach ($members as $member)
						{
						$email->addBCCMember($member);
						}
					$email->bulkSend();
					}

				if (empty($_POST['allMembers']))    // don't add to the journal if emailing all members
					{
					$index = 1;
					$nextJournal = \App\Tools\Date::todayString($index);

					while (4 != \App\Tools\Date::formatString('w', $nextJournal))
						{
						$nextJournal = \App\Tools\Date::todayString(++$index);
						}

					if ($_POST['eventDate'] >= $nextJournal)
						{
						$journalItem = new \App\Record\JournalItem();
						$journalItem->body = $_POST['message'];
						$journalItem->memberId = (int)$sender['memberId'];
						$journalItem->title = $_POST['subject'];
						$journalItem->insert();
						}
					}
				\App\Model\Session::setFlash('success', 'You emailed ' . \count($members) . ' club members');
				$this->page->redirect();
				}
			}
		}

	public function __toString() : string
		{
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Selection Criteria');
		$categoryView = new \App\View\Categories($this->page, new \PHPFUI\Button('back'));
		$picker = $categoryView->getMultiCategoryPicker('categories', 'Category Restriction', $this->parameters['categories'] ?? []);
		$picker->setToolTip('Pick specific categories if you to restrict the email, optional');
		$memberTypes = new \PHPFUI\FieldSet('Membership Types');
		$currentMembers = new \PHPFUI\Input\CheckBoxBoolean('currentMembers', 'Current', $this->parameters['currentMembers'] ?? true);
		$currentMembers->setToolTip('Check to send to current members of the club');
		$memberTypes->add($currentMembers);

		if ($this->page->isAuthorized('Email Past Members'))
			{
			$multiColumn = new \PHPFUI\MultiColumn();
			$pastMembers = new \PHPFUI\Input\CheckBoxBoolean('pastMembers', 'Lapsed', $this->parameters['pastMembers'] ?? false);
			$pastMembers->setToolTip('Check to send to past members of the club who have not renewed.  Make sure the enter the number of months back of lapsed members.');
			$multiColumn->add($pastMembers);
			$months = new \PHPFUI\Input\Number('months', 'Months Lapsed', $this->parameters['months'] ?? '');
			$months->setToolTip('Lapsed members up to this number of months back emailed');
			$multiColumn->add($months);
			$memberTypes->add($multiColumn);
			}
		$multiColumn = new \PHPFUI\MultiColumn();
		$newMembers = new \PHPFUI\Input\CheckBoxBoolean('newMembers', 'New', $this->parameters['newMembers'] ?? false);
		$newMembers->setToolTip('Check to send to recently joined members.  Make sure the enter the number of months they have been a member.');
		$multiColumn->add($newMembers);
		$newMonths = new \PHPFUI\Input\Number('newMonths', 'Months New', $this->parameters['newMonths'] ?? '');
		$newMonths->setToolTip('Members this number of months back and newer will be emailed');
		$multiColumn->add($newMonths);
		$memberTypes->add($multiColumn);
		$container = new \PHPFUI\Container($memberTypes);

		if ($this->page->isAuthorized('Email All Members Criteria'))
			{
			$criteriaFieldset = new \PHPFUI\FieldSet('Other Criteria');
			$zipCodes = new \PHPFUI\Input\Text('zipCodes', 'Limit to Zip Codes', $this->parameters['zipCodes'] ?? '');
			$zipCodes->setToolTip('5 digit zip codes, comma (,) separated');
			$criteriaFieldset->add($zipCodes);
			$town = new \PHPFUI\Input\Text('town', 'Town', $this->parameters['town'] ?? '');
			$town->setToolTip('This is an exact match');
			$criteriaFieldset->add($town);
			$container->add($criteriaFieldset);
			}
		else
			{
			$container->add(new \PHPFUI\Input\Hidden('town'));
			$container->add(new \PHPFUI\Input\Hidden('zipCodes'));
			}
		$fieldSet->add(new \PHPFUI\MultiColumn($picker, $container));
		$from = new \PHPFUI\Input\Date($this->page, 'eventDate', 'Event Date', $this->parameters['eventDate'] ?? '');
		$from->setMinDate(\App\Tools\Date::todayString());
		$from->setToolTip('Please specify the date this email mentions. Example, the date of the event you are emailing about happens. This will make sure we don\'t send out an old email in the weekly journal.');
		$from->setRequired();
		$multiColumn = new \PHPFUI\MultiColumn($from);

		$journalOnly = new \PHPFUI\Input\CheckBoxBoolean('journalOnly', 'Send to people on the journal only', ! empty($this->parameters['journalOnly']));
		$journalOnly->setToolTip('Check if this email should just be sent to weekly journal subscribers only.');
		$multiColumn->add($journalOnly);

		if ($this->page->isAuthorized('Force email to all members'))
			{
			$allMembers = new \PHPFUI\Input\CheckBoxBoolean('allMembers', 'Force email to all members', ! empty($this->parameters['allMembers']));
			$allMembers->setToolTip('Use with caution!');
			$multiColumn->add($allMembers);
			}
		$fieldSet->add($multiColumn);
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Email');
		$subject = new \PHPFUI\Input\Text('subject', 'Subject', $this->parameters['subject'] ?? '');
		$subject->setRequired();
		$subject->addAttribute('placeholder', 'Email Subject');
		$fieldSet->add($subject);
		$message = new \PHPFUI\Input\TextArea('message', 'Message', $this->parameters['message'] ?? '');
		$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$message->addAttribute('placeholder', 'Message to all members?');
		$message->setRequired();
		$fieldSet->add($message);
		$fieldSet->add(new \PHPFUI\Input\File($this->page, 'file', 'Optional file to attach'));
		$settingTable = new \App\Table\Setting();
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$emailAll = new \PHPFUI\Submit('Email All Members');
		$emailAll->setConfirm('Are you sure you want to email all members?');
		$buttonGroup->addButton($emailAll);
		$test = new \PHPFUI\Submit($this->testMessage);
		$test->addClass('warning');
		$buttonGroup->addButton($test);
		$form->add($buttonGroup);
		$output = $form;

		return (string)$output;
		}
	}
