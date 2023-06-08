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
		$fieldSet->add($settingsSaver->generateField('NMEMtitle', 'New Member Followup Subject')->setRequired(false));
		$fieldSet->add($settingsSaver->generateField('NMEMdays', 'New Member Followup Days', 'Number')->setRequired(false));
		$fieldSet->add($settingsSaver->generateField('donationText', 'Additional Donation Text')->setRequired(false));
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Membership Term');
		$extraFields[] = $membershipTerm = 'MembershipTerm';
		$multiColumn = new \PHPFUI\MultiColumn();
		$memberTerm = new \PHPFUI\Input\RadioGroup($membershipTerm, 'Membership Term', $this->settingTable->value($membershipTerm));
		$memberTerm->addButton('Annual');
		$memberTerm->addButton('12 Months');
		$memberTerm->setRequired()->setToolTip('Annual membership terms all renew on the same month. 12 month memberships are good for 12 months from date of joining.');
		$extraFields[] = $membershipStartMonth = 'MembershipStartMonth';
		$startMonth = new \App\UI\Month($membershipStartMonth, 'Membership Start Month', $this->settingTable->value($membershipStartMonth));
		$startMonth->setToolTip('For annual memberships, The month when all the memberships renew');
		$extraFields[] = $membershipGraceMonth = 'MembershipGraceMonth';
		$graceMonth = new \App\UI\Month($membershipGraceMonth, 'Membership Grace Month', $this->settingTable->value($membershipGraceMonth));
		$graceMonth->setToolTip('For annual memberships, if joining after this month, membership is good through the end of the next renwal period');
		$fieldSet->add(new \PHPFUI\MultiColumn($memberTerm, $startMonth, $graceMonth));

		$extraFields[] = $membershipType = 'MembershipType';
		$fieldSet->add($settingsSaver->generateField($membershipType, '', new \PHPFUI\Input\Hidden($membershipType, 'Manual'), false));
//		$renewalType->addButton('Manual Renewal', 'Manual');
//		$renewalType->addButton('Subscription', 'Subscription');
//		$renewalType->addButton('Both', 'Both');
//		$fieldSet->add($renewalType);
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Membership Dues');
		$multiColumn = new \PHPFUI\MultiColumn();
		$multiColumn->add($settingsSaver->generateField('annualDues', 'Membership Dues', 'Number'));
		$multiColumn->add($settingsSaver->generateField('additionalMemberDues', 'Additional Member Dues', 'Number'));
//		$multiColumn->add($settingsSaver->generateField('subscriptionDues', 'Subscription Dues', 'Number'));
		$fieldSet->add($multiColumn);

		$extraFields[] = $paidMembers = 'PaidMembers';
		$memberType = new \PHPFUI\Input\RadioGroup($paidMembers, 'Membership Type', $this->settingTable->value($paidMembers));
		$memberType->addButton('Unlimited', 'Unlimited');
		$memberType->addButton('Paid Only', 'Paid');
		$memberType->addButton('Family (two paid)', 'Family');
		$memberType->setRequired();

		$maxMembersOnMembership = $settingsSaver->generateField('maxMembersOnMembership', 'Max Members On Membership', 'Number')->setRequired(false);
		$maxMembersOnMembership->setToolTip('You can limit total members on a membership, for family membership, all members above 2 are free');

		$fieldSet->add(new \PHPFUI\MultiColumn($memberType, $maxMembersOnMembership));

//		$multiColumn = new \PHPFUI\MultiColumn();
//		$multiColumn->add($settingsSaver->generateField('discountCD', 'New Member Discount Code'));
//		$multiColumn->add($settingsSaver->generateField('discountDL', 'New Member Discount Amount', 'number'));
//		$fieldSet->add($multiColumn);
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
