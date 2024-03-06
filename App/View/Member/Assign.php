<?php

namespace App\View\Member;

class Assign
	{
	public function __construct(private readonly \PHPFUI\Page $page)
		{
		}

	public function getForm() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);

		$callout = new \PHPFUI\Callout('info');
		$callout->add('The following are roles in the website you should assign members to be in charge of. The same person can do multiple jobs.');
		$form->add($callout);

		$form->setAreYouSure(false);
		$form->add($this->generateMemberPicker('Banner Administrator'));
		$form->add($this->generateMemberPicker('Calendar Coordinator'));
		$form->add($this->generateMemberPicker('Membership Chair'));
		$form->add($this->generateMemberPicker('Newsletter Editor'));
		$form->add($this->generateMemberPicker('Rides Chair'));
		$form->add($this->generateMemberPicker('RideWithGPS Coordinator'));
		$form->add($this->generateMemberPicker('Sign In Sheet Coordinator'));
		$form->add($this->generateMemberPicker('Store Manager'));
		$form->add($this->generateMemberPicker('Store Shipping'));
		$form->add($this->generateMemberPicker('Treasurer'));
		$form->add($this->generateMemberPicker('Web Master'));

		return $form;
		}

	private function generateMemberPicker(string $name) : \PHPFUI\Input\Input
		{
		$chair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker($name));
		$editControl = $chair->getEditControl();

		return $editControl->setRequired();
		}
	}
