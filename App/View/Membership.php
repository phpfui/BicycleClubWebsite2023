<?php

namespace App\View;

class Membership
	{
	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \PHPFUI\Page $page)
		{
		$this->settingTable = new \App\Table\Setting();

		if (isset($_GET['delete']))
			{
			$this->page->redirect();
			}
		}

	public function configure() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$settingsSaver = new \App\Model\SettingsSaver();
		$extraFields = [];

		$fieldSet = new \PHPFUI\FieldSet('Membership Information');
		$fieldSet->add($this->generateMemberPicker('Membership Chair'));
		$fieldSet->add($settingsSaver->generateField('memberAddr', 'Membership Chair Snail Mail Address')->setRequired(false));
		$fieldSet->add($settingsSaver->generateField('memberTown', 'Membership Chair Town, State, Zip')->setRequired(false));
		$tel = new \PHPFUI\Input\Tel($this->page, 'phone', 'Membership Phone Number');
		$fieldSet->add($settingsSaver->generateField('phone', 'Membership Phone Number')->setRequired(false));
		$fieldSet->add($settingsSaver->generateField('joinPage', 'Join Club Link', 'url'));
		$fieldSet->add($settingsSaver->generateField('donationText', 'Additional Donation Text')->setRequired(false));
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Membership Defaults');
		$multiColumn = new \PHPFUI\MultiColumn();

		$multiColumn->add($settingsSaver->generateField('rideJournal', 'Ride Journal Emails Days', 'Number', false));
		$multiColumn->add($settingsSaver->generateField('newRideEmail', 'New Ride Posted Emails', 'CheckBox', false));
		$fieldSet->add($multiColumn);
		$multiColumn = new \PHPFUI\MultiColumn();
		$multiColumn->add($settingsSaver->generateField('emailNewsletterDefault', 'Newsletter', 'CheckBox', false));
		$multiColumn->add($settingsSaver->generateField('emailAnnouncementsDefault', 'Announcements', 'CheckBox', false));
		$multiColumn->add($settingsSaver->generateField('rideCommentsDefault', 'Ride Comments', 'CheckBox', false));
		$fieldSet->add($multiColumn);

		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('New Member Question');
		$extraFields[] = $questionName = 'NewMemberQuestions';
		$affilations = \json_decode($this->settingTable->value($questionName), true);
		$ul = new \PHPFUI\UnorderedList($this->page);

		if (\is_array($affilations))
			{
			foreach ($affilations as $question)
				{
				$ul->addItem($this->getQuestionItem($questionName, $question));
				}
			}
		$fieldSet->add($ul);
		$add = new \PHPFUI\Button('Add Question');
		$newField = $this->getQuestionItem($questionName);
		$newField = \str_replace(["\n", '"', "'"], ['', '\x22', '\x27'], $newField);
		$add->addAttribute('onclick', '$("#' . $ul->getId() . '").append("' . $newField . '");');
		$this->page->addJavaScript('$("#' . $add->getId() . '").click(function(e){e.preventDefault();})');
		$fieldSet->add($add);
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$settingsSaver->save($_POST);

			foreach ($extraFields as $field)
				{
				if (isset($_POST[$field]))
					{
					$data = $_POST[$field];

					if (\is_array($data))
						{
						$data = \json_encode($_POST[$field], JSON_THROW_ON_ERROR);
						}
					$this->settingTable->save($field, $data);
					}
				}

			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add($submit);
			}

		return $form;
		}

	public function csvOptions() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Download CSV');
		$submit->addClass('info');
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Download Options');
		$radio = new \PHPFUI\Input\RadioGroup('type', '', 'full');
		$radio->setSeparateRows();
		$radio->addButton('Full Download', 'full');
		$radio->addButton('Newsletter Subscribers', 'newsletter');
		$radio->addButton('Announcement Members', 'annoucements');
		$fieldSet->add($radio);
		$multiColumn = new \PHPFUI\MultiColumn();
		$startDate = new \PHPFUI\Input\Date($this->page, 'start', 'Earliest Expiration Date', \App\Tools\Date::todayString());
		$startDate->setToolTip('Memberships that lapsed before this date will not be included');
		$multiColumn->add($startDate);
		$endDate = new \PHPFUI\Input\Date($this->page, 'end', 'Latest Expiration Date');
		$endDate->setToolTip('Memberships current through this date wil be included');
		$multiColumn->add($endDate);
		$fieldSet->add($multiColumn);
		$form->add($fieldSet);
		$form->add(new \App\UI\CancelButtonGroup($submit));

		return $form;
		}

	public function updateSubscriptions() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Update Subscriptions');
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$memberModel = new \App\Model\Member();
			$count = $memberModel->updateSubscriptions($_POST);
			$this->page->setResponse($count . ' Subscriptions Updated');

			return $form;
			}
		$options = new \PHPFUI\FieldSet('Subscription Settings');
		$radio = new \PHPFUI\Input\RadioGroup('subscribe', '', 'no');
		$radio->addButton('Unsubscribe', 'no');
		$radio->addButton('Subscribe', 'yes');
		$options->add($radio);
		$newsletter = new \PHPFUI\Input\CheckBoxBoolean('emailNewsletter', 'Newsletter');
		$announcements = new \PHPFUI\Input\CheckBoxBoolean('emailAnnouncements', 'Club Announcements');
		$journal = new \PHPFUI\Input\CheckBoxBoolean('journal', 'Weekly Journal');
		$multiColumn = new \PHPFUI\MultiColumn($newsletter, $announcements, $journal);
		$options->add($multiColumn);
		$form->add($options);
		$emails = new \PHPFUI\Input\TextArea('emails', 'Emails one per line');
		$emails->setRequired();
		$form->add($emails);
		$form->add($submit);

		return $form;
		}

	private function generateMemberPicker(string $name) : \PHPFUI\Input\Input
		{
		$chair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker($name));
		$editControl = $chair->getEditControl();

		return $editControl->setRequired();
		}

	private function getQuestionItem(string $field, string $question = '') : \PHPFUI\ListItem
		{
		$row = new \PHPFUI\GridX();
		$listItem = new \PHPFUI\ListItem($row);
		$titleColumn = new \PHPFUI\Cell(11);
		$titleColumn->add(new \PHPFUI\Input\Text($field . '[]', ' ', $question));
		$row->add($titleColumn);

		if ($question)
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
	}
