<?php

namespace App\View\CueSheet;

class Search implements \Stringable
	{
	private readonly \App\Table\CueSheet $cueSheetTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->cueSheetTable = new \App\Table\CueSheet();
		}

	public function __toString() : string
		{
		$button = new \PHPFUI\Button('Search Cue Sheets');
		$modal = $this->getSearchModal($button, $_GET);
		$output = '';
		$row = new \PHPFUI\GridX();
		$row->add('<br>');

		if (! empty($_GET['order']))
			{
			$view = new \App\View\CueSheet($this->page);
			$output = $view->show($this->setCursor($_GET));

			if ($this->cueSheetTable->count())
				{
				$output .= $row . $button;
				}
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $button . $output;
		}

	/**
	 * @param array<string,mixed> $parameters
	 */
	public function setCursor(array $parameters) : \App\Table\CueSheet
		{
		$this->cueSheetTable->setLimit(50);
		$this->cueSheetTable->addOrderBy($parameters['sort'] ?? 'Mileage', $parameters['order'] ?? 'A');
		$condition = new \PHPFUI\ORM\Condition();

		foreach (['destination', 'description', 'name'] as $field)
			{
			if (\strlen((string)($parameters[$field] ?? '')))
				{
				$condition->and($field, '%' . $parameters[$field] . '%', new \PHPFUI\ORM\Operator\Like());
				}
			}

		foreach (['cueSheetId', 'startLocationId'] as $field)
			{
			if (! empty($parameters[$field]))
				{
				$condition->and($field, $parameters[$field]);
				}
			}

		foreach (['mileageFrom' => new \PHPFUI\ORM\Operator\GreaterThanEqual(), 'mileageTo' => new \PHPFUI\ORM\Operator\LessThanEqual()] as $field => $operator)
			{
			if (! empty($parameters[$field]))
				{
				$condition->and('mileage', $parameters[$field], $operator);
				}
			}

		if (! empty($parameters['terrain']) && \is_array($parameters['terrain']))
			{
			$condition->and('terrain', $parameters['terrain'], new \PHPFUI\ORM\Operator\In());
			}
		$this->cueSheetTable->setWhere($condition);

		return $this->cueSheetTable;
		}

	/**
	 * @param array<string,string> $parameters
	 */
	protected function getSearchModal(\PHPFUI\HTML5Element $modalLink, array $parameters) : \PHPFUI\Reveal
		{
		$this->setDefaults($parameters);
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'get');
		$fieldSet = new \PHPFUI\FieldSet('Find a Cue Sheet');
		$row = new \PHPFUI\GridX();
		$col = new \PHPFUI\Cell(6);
		$field = new \PHPFUI\Input\Number('cueSheetId', 'Number', $parameters['cueSheetId']);
		$field->addAttribute('max', (string)999999)->addAttribute('min', (string)0);
		$col->add($field);
		$field = new \PHPFUI\Input\Number('mileageFrom', 'Mileage at least', $parameters['mileageFrom']);
		$field->addAttribute('max', (string)999)->addAttribute('min', (string)0);
		$col->add($field);
		$field = new \PHPFUI\Input\Number('mileageTo', 'Mileage up to', $parameters['mileageTo']);
		$field->addAttribute('max', (string)999)->addAttribute('min', (string)0);
		$col->add($field);
		$row->add($col);
		$col = new \PHPFUI\Cell(6);
		$cuesheetView = new \App\View\CueSheet($this->page);
		$terrain = $cuesheetView->getTerrainEditControl(0, 'terrain', 'Terrain (Select one or more)', true);
		$terrain->select($parameters['terrain']);
		$col->add($terrain);
		$row->add($col);
		$fieldSet->add($row);
		$startLocationView = new \App\View\StartLocation($this->page);
		$fieldSet->add($startLocationView->getEditControl($parameters['startLocationId']));
		$fieldSet->add(new \PHPFUI\Input\Text('name', 'Ride Name includes', $parameters['name']));
		$fieldSet->add(new \PHPFUI\Input\Text('destination', 'Destination includes', $parameters['destination']));
		$fieldSet->add(new \PHPFUI\Input\Text('description', 'Description includes', $parameters['description']));
		$row = new \PHPFUI\GridX();
		$col = new \PHPFUI\Cell(12, 6);
		$field = new \PHPFUI\Input\Select('sort', 'Sort By');
		$sortOptions = [];
		$sortOptions['mileage'] = 'Mileage';
		$sortOptions['memberId'] = 'Author';
		$sortOptions['cueSheetId'] = 'Cue Sheet Number';
		$sortOptions['dateAdded'] = 'Date Added';
		$sortOptions['destination'] = 'Destination';
		$sortOptions['revisionDate'] = 'Revision Date';
		$sortOptions['name'] = 'CueSheet Name';
		$sortOptions['startLocationId'] = 'Start Location';
		$sortOptions['terrain'] = 'Terrain';

		foreach ($sortOptions as $value => $label)
			{
			$field->addOption($label, $value, $parameters['sort'] == $value);
			}
		$col->add($field);
		$row->add($col);
		$col = new \PHPFUI\Cell(12, 6);
		$radio = new \PHPFUI\Input\RadioGroup('order', '', $parameters['order']);
		$radio->addButton('Ascending', 'A');
		$radio->addButton('Descending', 'D');
		$radio->setSeparateRows();
		$col->add($radio);
		$row->add($col);
		$fieldSet->add($row);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	/**
	 * @param array<string,string> $parameters
	 */
	protected function setDefaults(array &$parameters) : void
		{
		$searchFields = [];
		$searchFields['cueSheetId'] = '';
		$searchFields['mileageFrom'] = '';
		$searchFields['mileageTo'] = '';
		$searchFields['startLocationId'] = '';
		$searchFields['destination'] = '';
		$searchFields['description'] = '';
		$searchFields['name'] = '';
		$searchFields['terrain'] = [];
		$searchFields['sort'] = 'mileage';
		$searchFields['order'] = 'A';

		foreach ($searchFields as $key => $value)
			{
			if (! isset($parameters[$key]))
				{
				$parameters[$key] = $value;
				}
			}
		}
	}
