<?php

namespace App\View\Ride;

class Signup
	{
	protected iterable $members;

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Ride $ride, private \App\Record\Member $member = new \App\Record\Member())
		{
		$this->settingTable = new \App\Table\Setting();

		$membershipId = $member->membershipId ?: \App\Model\Session::signedInMembershipId();
		$this->members = \App\Table\Member::membersInMembership($membershipId);

		if (! $this->member->loaded())
			{
			$this->member = \App\Model\Session::signedInMemberRecord();
			}

		if (\App\Model\Session::checkCSRF() && 'Save' == ($_POST['submit'] ?? ''))
			{
			$model = new \App\Model\RideSignup($ride, new \App\Record\Member((int)$_POST['memberId']));
			$model->updateSignup($_POST);
			$this->page->redirect('/Rides/signedUp/' . $ride->rideId);
			}
		}

	public function getForm() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Sign Up For Ride ' . $this->ride->title);

		if ($this->ride->releasePrinted > '2000-00-00')
			{
			$alert = new \PHPFUI\Callout('warning');
			$alert->add('The <b>Ride Sign In Sheet</b> was printed on ' . \App\Tools\Date::formatString('n/j/Y \a\\t g:i a', $this->ride->releasePrinted) . '. You may not be listed.');
			$fieldSet->add($alert);
			}

		if (\count($this->members) > 1)
			{
			$memberSelect = new \PHPFUI\Input\Select('memberId', 'Select a Member to sign up');

			foreach ($this->members as $member)
				{
				$memberSelect->addOption($member['firstName'] . ' ' . $member['lastName'], $member['memberId'], $member['memberId'] == $this->member->memberId);
				}
			$memberSelect->setToolTip('You can sign up any member in your membership by selecting them here');
			}
		else
			{
			$memberSelect = new \PHPFUI\Input\Hidden('memberId', (string)$this->member->memberId);
			}
		$fieldSet->add($memberSelect);
		$rider = new \App\Record\RideSignup();

		$rider->read(['rideId' => $this->ride->rideId, 'memberId' => $this->member->memberId, ]);

		if ($rider->loaded())
			{
			$newSignup = false;
			}
		else
			{
			$newSignup = true;
			$rider->status = \App\Table\RideSignup::DEFINITELY_RIDING;
			}

		$rideSignupTable = new \App\Table\RideSignup();
		$status = $rideSignupTable->getRiderStatus();
		unset($status[\App\Table\RideSignup::POSSIBLY_RIDING], $status[\App\Table\RideSignup::CANCELLED]);

		$model = new \App\Model\RideSignup($this->ride, \App\Model\Session::signedInMemberRecord());
		$signupLimit = $model->getRiderSignupLimit();

		if ($signupLimit)
			{
			unset($status[\App\Table\RideSignup::PROBABLY_RIDING]);
			}
		else
			{
			unset($status[\App\Table\RideSignup::WAIT_LIST]);
			}

		$select = new \PHPFUI\Input\Select('status', 'Status');

		foreach ($status as $key => $value)
			{
			$select->addOption($value, $key, $key == $rider->status);
			}
		$select->setToolTip('Select "Definitely Riding" if you plan on being there for sure. Update if your status changes.');
		$fieldSet->add($select);
		$fieldSet->add(new \PHPFUI\Input\Hidden('rideId', (string)$this->ride->rideId));
		$text = new \PHPFUI\Input\Text('comments', 'Comments to Leader', $rider->comments ?? '');
		$text->setToolTip('Comments to the leader. These are not public comments, they just go to the leader. See the ride comments section for public comments.');
		$fieldSet->add($text);
		$cell = new \PHPFUI\Input\Tel($this->page, 'cellPhone', 'Cell Phone Number', $this->member->cellPhone);
		$cell->setToolTip('In case the leader has to contact on the ride.');
		$cell->setRequired();
		$fieldSet->add($cell);
		$contact = new \PHPFUI\Input\Text('emergencyContact', 'Emergency Contact', $this->member->emergencyContact);
		$contact->setToolTip('Person we should call in case of emergency.');
		$fieldSet->add($contact);
		$ephone = new \PHPFUI\Input\Tel($this->page, 'emergencyPhone', 'Emergency Phone Number', $this->member->emergencyPhone);
		$ephone->setToolTip('Phone number of the emergency contact.');
		$fieldSet->add($ephone);
		$plate = new \PHPFUI\Input\Text('license', 'Car License Plate', $this->member->license ?: '');
		$plate->setToolTip('To identify missing riders at the end of the ride.');
		$fieldSet->add($plate);
		$rideComments = new \PHPFUI\Input\CheckBoxBoolean('rideComments', 'Subscribe to Ride Comments', (bool)$rider->rideComments);
		$rideComments->setToolTip('If you check this box, you will receive ride comment updates via email. You can comment on a ride at any time reguardless of this setting. You can also turn this on or off on each comment you post.');
		$fieldSet->add($rideComments);
		$additional = new \PHPFUI\FieldSet('Additional information for the ride leader:');
		$firstClubRide = new \PHPFUI\Input\CheckBoxBoolean('firstRide', 'First ride with the club?', $rider->firstRide ?? false);
		$additional->add($firstClubRide);
		$firstLevelRide = new \PHPFUI\Input\CheckBoxBoolean('firstRideInCategory', 'First ride in category?', $rider->firstRideInCategory ?? false);
		$additional->add($firstLevelRide);
		$fieldSet->add($additional);
		$form->add($fieldSet);

		if ($newSignup && $this->settingTable->value('RequireRiderWaiver'))
			{
			$clubName = $this->settingTable->value('clubName');
			$waiverLink = new \PHPFUI\Link('#', $clubName . ' Waiver');
			$modal = new \PHPFUI\Reveal($this->page, $waiverLink);
			$modal->addClass('large');
			$modal->add('<h3>I Agree To The Following</h3>');
			$modal->add($this->settingTable->value('WaiverText'));
			$modal->add('<hr>');
			$modal->add(new \PHPFUI\CloseButton($modal));
			$modal->add(new \PHPFUI\Cancel('Close'));
			$waiver = new \PHPFUI\Input\CheckBoxBoolean('agreeToWaiver', 'You must agree to the ' . $waiverLink);
			$waiver->setRequired();
			$form->add($waiver);
			}
		$form->add(new \PHPFUI\FormError('Make sure you agree to the waiver (above) and enter a cell number'));

		return $form;
		}
	}
