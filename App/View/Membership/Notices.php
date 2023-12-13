<?php

namespace App\View\Membership;

class Notices
	{
	public function __construct(private readonly \PHPFUI\Page $page)
		{
		}

	public function edit(\App\Record\MemberNotice $notice) : \App\UI\ErrorFormSaver
		{
		if ($notice->memberNoticeId)
			{
			$submit = new \PHPFUI\Submit();
			$form = new \App\UI\ErrorFormSaver($this->page, $notice, $submit);

			if ($form->save())
				{
				return $form;
				}
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add Notification');
			$form = new \App\UI\ErrorFormSaver($this->page, $notice);

			if (\App\Model\Session::checkCSRF() && ($_POST['submit'] ?? '') == $submit->getText())
				{
				$notice->setFrom($_POST);
				$notice->memberNoticeId = 0;
				$notice->insert();

				$this->page->redirect('/Membership/notifications');

				return $form;
				}
			}

		$fieldSet = new \PHPFUI\FieldSet('Email Details');
		$title = new \PHPFUI\Input\Text('title', 'Email Subject', $notice->title ?? '');
		$title->setToolTip('Subject does full substitutions (see Substitutions tab)')->setRequired();
		$fieldSet->add($title);

		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('From Member'), 'memberId', $notice->member->toArray());
		$memberInput = $memberPicker->getEditControl()->setToolTip('Defaults to Membership Chair if blank')->setNoFreeForm(false);

		$overridePreferences = new \PHPFUI\Input\CheckBoxBoolean('overridePreferences', 'Override Member Email Preferences', (bool)$notice->overridePreferences);
		$overridePreferences->setToolTip('This should only be checked for membership renewal reasons.');
		$fieldSet->add(new \PHPFUI\MultiColumn($memberInput, $overridePreferences));

		$fieldChoices = ['lastLogin', 'acceptedWaiver', 'expires', 'joined', 'lastRenewed', 'renews'];
		$fields = new \PHPFUI\Input\Select('field', 'Date Field');

		foreach ($fieldChoices as $field)
			{
			$label = \PHPFUI\TextHelper::capitalSplit($field);
			$fields->addOption($label, $field, $field == $notice->field);
			}
		$fields->setRequired()->setToolTip('The date field to base the sending of the email on. Plus the Day Offset');

		$dayOffsets = new \PHPFUI\Input\Text('dayOffsets', 'Day Offsets', $notice->dayOffsets ?? '');
		$dayOffsets->setToolTip('Comma separated list of day offsets from choosen date to send email. Negative numbers indicate that number of days before the date, positive numbers are days after the date, zero is on the date.')->setRequired();
		$fieldSet->add(new \PHPFUI\MultiColumn($fields, $dayOffsets));
		$form->add($fieldSet);

		$message = new \PHPFUI\Input\TextArea('body', 'Email Text', $notice->body ?? '');
		$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$message->setToolTip('Use can use any substitition field from the substitution tab.');
		$message->setRequired();
		$tabs = new \PHPFUI\Tabs();
		$tabs->addTab('Email Body', $message, true);

		$memberFields = new \App\Model\Email\Member();
		$tabs->addTab('Substitutions', new \App\UI\SubstitutionFields($memberFields->toArray()));
		$form->add($tabs);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);

		if ($notice->loaded())
			{
			$testButton = new \PHPFUI\Button('Test Email', '/Membership/notifications/' . $notice->memberNoticeId . '/test');
			$testButton->addClass('warning');
			$form->saveOnClick($testButton);
			$buttonGroup->addButton($testButton);
			}
		$backButton = new \PHPFUI\Button('Member Notifications', '/Membership/notifications');
		$backButton->addClass('hollow secondary');
		$buttonGroup->addButton($backButton);

		$form->add($buttonGroup);

		return $form;
		}

	public function list() : \App\UI\ContinuousScrollTable
		{
		$memberNoticeTable = new \App\Table\MemberNotice();
		$memberNoticeTable->addJoin('member');
		$table = new \App\UI\ContinuousScrollTable($this->page, $memberNoticeTable);

		$headers = [
			'title',
			'field',
		];

		$table->setSearchColumns($headers)->setSortableColumns($headers);
		$headers[] = 'Member';
		$headers[] = 'Del';

		$table->setHeaders($headers);
		$deleter = new \App\Model\DeleteRecord($this->page, $table, $memberNoticeTable, 'Are you sure you want to permanently delete this notice email?');
		$table->addCustomColumn('Del', $deleter->columnCallback(...));
		$table->addCustomColumn('title', static fn (array $row) => new \PHPFUI\Link('/Membership/notifications/' . $row['memberNoticeId'], $row['title'], false));
		$table->addCustomColumn('Member', static function(array $row) {$member = new \App\Record\Member($row['memberId']);

			return $member->loaded() ? $member->fullName() : 'Ride Chair';});

		return $table;
		}
	}
