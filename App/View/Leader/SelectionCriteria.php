<?php

namespace App\View\Leader;

class SelectionCriteria
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	/**
	 * @param array<string,string> $post
	 */
	public function get(array $post) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Selection Criteria');

		$accordion = new \PHPFUI\Accordion();
		$accordion->addAttribute('data-multi-expand', 'true');
		$accordion->addAttribute('data-allow-all-closed', 'true');

		$fieldSet->add($accordion);
		$picker = new \App\UI\MultiCategoryPicker('categories', 'Category Restriction', $post['categories'] ?? []);
		$picker->setToolTip('Pick specific categories if you to restrict the text, optional');
		$accordion->addTab('Category Restriction', $picker);

		$coordinatorsOnly = new \PHPFUI\Input\CheckBoxBoolean('coordinatorsOnly', 'Ride Coordinators Only', $post['coordinatorsOnly'] ?? false);
		$minLed = new \PHPFUI\Input\Number('minLed', 'Minimum Number of Leads in Date Range', $post['minLed'] ?? '');
		$minLed->addAttribute('min', '0');
		$minLed->addAttribute('step', '1');
		$maxLed = new \PHPFUI\Input\Number('maxLed', 'Maximum Number of Leads in Date Range', $post['maxLed'] ?? '');
		$maxLed->addAttribute('min', '0');
		$maxLed->addAttribute('step', '1');
		$coordinators = new \PHPFUI\MultiColumn($coordinatorsOnly, $minLed, $maxLed);
		$accordion->addTab('Coordinators and Times Led', $coordinators);

		$dates = new \PHPFUI\MultiColumn();
		$from = new \PHPFUI\Input\Date($this->page, 'fromDate', 'Leading Rides From', $post['fromDate'] ?? '');
		$from->setToolTip('Only leaders leading a ride from this date and beyond will get the text');
		$dates->add($from);
		$to = new \PHPFUI\Input\Date($this->page, 'toDate', 'Leading Rides Until', $post['toDate'] ?? '');
		$to->setToolTip('Only leaders leading a ride upto this date will get the text');
		$dates->add($to);
		$accordion->addTab('Led Rides Dates', $dates);

		return $fieldSet;
		}
	}
