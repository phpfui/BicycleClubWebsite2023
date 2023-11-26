<?php

namespace App\View\Setup;

class AssignMembers extends \PHPFUI\Container
	{
	public function __construct(private readonly \PHPFUI\Page $page, \App\View\Setup\WizardBar $wizardBar)
		{
		$this->add(new \PHPFUI\Header('Assign Member Roles', 4));
		$this->add($wizardBar);

		$callout = new \PHPFUI\Callout('info');
		$callout->add('The following are roles in the website you should assign members to be in charge of. The same person can do multiple jobs.');
		$this->add($callout);

		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add($this->generateMemberPicker('Banner Administrator'));
		$form->add($this->generateMemberPicker('Membership Chair'));
		$form->add($this->generateMemberPicker('Newsletter Editor'));
		$form->add($this->generateMemberPicker('Rides Chair'));
		$form->add($this->generateMemberPicker('RideWithGPS Coordinator'));
		$form->add($this->generateMemberPicker('Sign In Sheet Coordinator'));
		$form->add($this->generateMemberPicker('Store Manager'));
		$form->add($this->generateMemberPicker('Store Shipping'));
		$form->add($this->generateMemberPicker('Treasurer'));
		$form->add($this->generateMemberPicker('Web Master'));

		$this->add($form);
		}

	private function generateMemberPicker(string $name) : \PHPFUI\Input\Input
		{
		$chair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker($name));
		$editControl = $chair->getEditControl();

		return $editControl->setRequired();
		}
	}
