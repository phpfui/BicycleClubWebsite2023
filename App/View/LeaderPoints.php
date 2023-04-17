<?php

namespace App\View;

class LeaderPoints
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function Finance() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Date Range');
		$fieldSet->add(new \PHPFUI\MultiColumn(
			new \PHPFUI\Input\Date($this->page, 'startDate', 'Start Date'),
			new \PHPFUI\Input\Date($this->page, 'endDate', 'End Date')
		));
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Report Selection');
		$type = new \PHPFUI\Input\RadioGroup('report', '', 'outstanding');
		$type->addButton('Outstanding Volunteer Points', 'outstanding');
		$type->addButton('Redeemed Volunteer Points', 'redeemed');
		$fieldSet->add($type);
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Sort Order');
		$sort = new \PHPFUI\Input\RadioGroup('sort', '', (string)0);
		$sort->addButton('Points', (string)0);
		$sort->addButton('Member', (string)1);
		$fieldSet->add($sort);
		$form->add($fieldSet);
		$form->add($this->getDownloadType());
		$form->add(new \App\UI\CancelButtonGroup(new \PHPFUI\Submit('Download')));

		return $form;
		}

	public function pointSettings() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$prefix = 'VolunteerPoints';
		$settingsSaver = new \App\Model\SettingsSaver($prefix);
		$form = new \PHPFUI\Form($this->page, $submit);
		$form->add('The following settings will control volunteer points automatically credited based on member activity. If you want compute points directly, please see the <a href="/Leaders/pointsReport">Volunteer Points Report</a>');
		$fieldSet = new \PHPFUI\FieldSet('FRED Volunteer Points Settings');

		$categories = $this->getPointsCategories($prefix);

		$fieldSet->add($this->getEditFields($categories, $settingsSaver));
		$form->add($fieldSet);
		$form->add($submit);

		if ($form->isMyCallback())
			{
			$settingsSaver->save();
			$this->page->setResponse('Saved');
			}

		return $form;
		}

	public function reportSettings() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$prefix = 'VolunteerReport';
		$settingsSaver = new \App\Model\SettingsSaver($prefix);
		$form = new \PHPFUI\Form($this->page, $submit);

		$fieldSet = new \PHPFUI\FieldSet('Report Settings');

		$categories = $this->getPointsCategories($prefix);

		$fieldSet->add($this->getEditFields($categories, $settingsSaver));

		$startValue = $settingsSaver->getValue($prefix . 'StartDateX');

		if (! $startValue)
			{
			$startValue = \App\Tools\Date::year(\App\Tools\Date::today()) . '-01-01';
			}
		$endValue = $settingsSaver->getValue($prefix . 'EndDateX');

		if (! $endValue)
			{
			$endValue = \App\Tools\Date::year(\App\Tools\Date::today()) . '-12-31';
			}

		$jobEventTable = new \App\Table\JobEvent();
		$volunteerEvents = $jobEventTable->getJobEventsBetween($startValue, $endValue);

		$multiSelect = new \PHPFUI\Input\MultiSelect($prefix . 'EventX', 'Select Volunteer Events for Credit');
		$multiSelect->selectAll();
		$multiSelect->setColumns(2);

		foreach ($volunteerEvents as $event)
			{
			$multiSelect->addOption($event['name'] . ' - ' . $event['date'], $event['jobEventId']);
			}
		$multiSelect->select($settingsSaver->getValue($prefix . 'EventX'));
		$fieldSet->add($multiSelect);
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Date Range');
		$start = new \PHPFUI\Input\Date($this->page, $prefix . 'StartDateX', 'Start Date', $startValue);
		$start->setRequired();
		$end = new \PHPFUI\Input\Date($this->page, $prefix . 'EndDateX', 'End Date', $endValue);
		$end->setRequired();
		$fieldSet->add(new \PHPFUI\MultiColumn($start, $end));
		$form->add($fieldSet);
		$form->add($this->getDownloadType());

		if ($form->isMyCallback())
			{
			$settingsSaver->save();
			$this->page->setResponse('Saved');
			}
		else
			{
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$download = new \PHPFUI\Submit('Download');
			$download->addClass('info');
			$buttonGroup->addButton($download);
			$form->add($buttonGroup);
			}

		return $form;
		}

	private function getDownloadType() : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Download Type');
		$downloadType = new \PHPFUI\Input\RadioGroup('downloadType', '', 'CSV');
		$downloadType->addButton('CSV');
		$downloadType->addButton('PDF');
		$fieldSet->add($downloadType);

		return $fieldSet;
		}

	private function getEditFields(array $categories, \App\Model\SettingsSaver $settingsSaver) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$container->add('You need to weight each of the following categories:<p>');
		$ul = new \PHPFUI\UnorderedList();

		foreach ($categories as $name)
			{
			$ul->addItem(new \PHPFUI\ListItem($name));
			}
		$container->add($ul);
		$container->add('The number you enter is a
			 multiplier of the number of times the condition is met. For example, if you wanted to award one point for leading a ride,
			 enter "1" next to the <b>D Ride Leader</b> field. If you want to give a half point for an assistant leader, enter ".5" next to
			 <b>Assistant Ride Leader</b>.  You can also weight things more by putting a higher number, for example, you could enter "2"
			 for <b>Volunteered</b> and that would count twice as much as leading a ride. Enter "0" to not use that category.<hr>');

		$multiColumn = new \PHPFUI\MultiColumn();

		foreach ($categories as $name => $title)
			{
			$multiColumn->add($settingsSaver->generateField($name, $title));

			if (4 == \count($multiColumn))
				{
				$container->add($multiColumn);
				$multiColumn = new \PHPFUI\MultiColumn();
				}
			}

		if (\count($multiColumn))
			{
			$container->add($multiColumn);
			}

		return $container;
		}

	/**
	 * @return string[]
	 *
	 * @psalm-return array<string, string>
	 */
	private function getPointsCategories(string $prefix) : array
		{
		$categoryTable = new \App\Table\Category();
		$categories = [];

		foreach ($categoryTable->getAllCategories() as $cat)
			{
			$categories[$prefix . 'Lead' . $cat['category']] = $cat['category'] . ' Ride Leader';
			}
		$categories[$prefix . 'LeadAll'] = 'All Ride Leader';
		$categories[$prefix . 'Assist'] = 'Assistant Leader';
		$categories[$prefix . 'Status'] = 'Ride Status Reported';
		$categories[$prefix . 'CueSheet'] = 'Cue Sheet Submitted';
		$categories[$prefix . 'SignIn'] = 'Sign In Sheet';
		$categories[$prefix . 'Volunteer'] = 'Volunteered';

		return $categories;
		}
	}
