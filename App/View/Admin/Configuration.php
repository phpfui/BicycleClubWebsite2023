<?php

namespace App\View\Admin;

class Configuration
	{
	public function __construct(private readonly \PHPFUI\Page $page)
		{
		}

	public function site() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Club Information');
		$settingsSaver = new \App\Model\SettingsSaver();
		$fieldSet->add($settingsSaver->generateField('boardName', 'Member Only Area Name'));
		$fieldSet->add($settingsSaver->generateField('clubName', 'Club Name'));
		$fieldSet->add($settingsSaver->generateField('clubAbbrev', 'Club Abbreviation'));
		$fieldSet->add($settingsSaver->generateField('clubLocation', 'Club Location'));
		$homePage = $settingsSaver->generateField('homePage', 'Club Web Site Link', 'url');
		$homePage->setToolTip('Must start with "https://" or website will not work, and no trailing slash.');
		$fieldSet->add($homePage);
		$fieldSet->add($settingsSaver->generateField('generalAdmissionName', 'General Admission Section Name'));
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Various Settings');
		$necc = $settingsSaver->generateField('calendarName', 'Cycling Calendar Name');
		$necc->setRequired(false)->setToolTip('Leave blank to disable');
		$fieldSet->add($necc);
		$rideListLimit = $settingsSaver->generateField('publicRideListLimit', 'Public Ride List Limit');
		$rideListLimit->setRequired(false)->setToolTip('Number of rides to show on the public schedule');
		$homePageDaysBack = $settingsSaver->generateField('homePageDaysBack', 'Home Page Days Back');
		$homePageDaysBack->setRequired(false)->setToolTip('Number of days back to show What\'s Happening on user home page');
		$fieldSet->add(new \PHPFUI\MultiColumn($rideListLimit, $homePageDaysBack));
		$fieldSet->add($this->generateMemberPicker('Treasurer'));
		$fieldSet->add($this->generateMemberPicker('Web Master'));
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$settingsSaver->save($_POST);
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add(new \App\UI\CancelButtonGroup($submit));
			}

		return $form;
		}

	private function generateMemberPicker(string $name) : \PHPFUI\Input\Input
		{
		$chair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker($name));
		$editControl = $chair->getEditControl();

		return $editControl->setRequired();
		}
	}
