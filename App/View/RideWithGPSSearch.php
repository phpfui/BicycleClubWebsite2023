<?php

namespace App\View;

class RideWithGPSSearch implements \Stringable
	{
	/**
	 * @var string[]
	 *
	 * @psalm-var array{0: string, 1: string, 2: string, 3: string}
	 */
	private array $hidden = ['p', 'l', 'c', 's'];

	private readonly bool $metric;

	private readonly \App\Table\RWGPS $rwgpsTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->rwgpsTable = new \App\Table\RWGPS();
		$this->metric = 'km' == $this->page->value('RWGPSUnits');
		}

	public function __toString() : string
		{
		$button = new \PHPFUI\Button('Search RideWithGPS');
		$button->addClass('success');
		$modal = $this->getSearchModal($button, $_GET);
		$output = '';
		$row = new \PHPFUI\GridX();
		$row->add('<br>');

		if ($_GET)
			{
			$view = new \App\View\RideWithGPS($this->page);
			$this->setSearch($_GET);
			$output = $view->list($this->rwgpsTable);

			if ($this->rwgpsTable->count())
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
	 * @param array<string,string> $parameters
	 */
	protected function getRangeSlider(string $name, array $parameters, int $minValue = 1, int $maxValue = 250, string $subtitle = '') : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$title = \PHPFUI\TextHelper::capitalSplit($name);
		$multiColumn = new \PHPFUI\MultiColumn('', "<b>{$title}</b> {$subtitle}", '');
		$multiColumn->addClass('align-center');
		$container->add($multiColumn);

		// make slider with two handles
		$min = $name . '_min';
		$firstHandle = new \PHPFUI\Input\Text($min, '', $parameters[$min]);
		$slider = new \PHPFUI\Slider((int)$parameters[$min], new \PHPFUI\SliderHandle((int)$parameters[$min], $firstHandle));
		// add second handle for a range
		$max = $name . '_max';
		$secondHandle = new \PHPFUI\Input\Text($max, '', $parameters[$max]);
		$slider->setRangeHandle(new \PHPFUI\SliderHandle((int)$parameters[$max], $secondHandle));
		// Set min and max for the slider
		$slider->setMin($minValue);
		$slider->setMax($maxValue);

		// add the inputs and sliders to the page
		$row = new \PHPFUI\GridX();
		$col = new \PHPFUI\Cell(2, 1);
		$col->add($firstHandle);
		$row->add($col);
		$col = new \PHPFUI\Cell(8, 10);
		$col->add($slider);
		$row->add($col);
		$col = new \PHPFUI\Cell(2, 1);
		$col->add($secondHandle);
		$row->add($col);
		$container->add($row);

		return $container;
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
		$fieldSet = new \PHPFUI\FieldSet('Find a Ride With GPS Route');

		if ($this->metric)
			{
			$fieldSet->add($this->getRangeSlider('km', $parameters, 1, 350));
			$fieldSet->add($this->getRangeSlider('metersPerKm', $parameters, 0, 100));
			}
		else
			{
			$fieldSet->add($this->getRangeSlider('miles', $parameters));
			$fieldSet->add($this->getRangeSlider('feetPerMile', $parameters, 0, 150));
			}

		$fieldSet->add(new \PHPFUI\MultiColumn(new \PHPFUI\Input\Text('town', 'Starting Town', $parameters['town']), new \PHPFUI\Input\CheckBoxBoolean('club', 'Club Routes Only', $parameters['club'])));
		$fieldSet->add(new \PHPFUI\Input\Text('title', 'Title includes', $parameters['title']));
		$fieldSet->add(new \PHPFUI\Input\Text('description', 'Description includes', $parameters['description']));
		$fieldSet->add(new \PHPFUI\Input\Text('csv', 'Road Name', $parameters['csv']));

		foreach ($this->hidden as $field)
			{
			if (isset($parameters[$field]))
				{
				$fieldSet->add(new \PHPFUI\Input\Hidden($field, $parameters[$field]));
				}
			}
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	/**
	 * @param array<string,string> &$parameters
	 */
	protected function setDefaults(array &$parameters) : void
		{
		$searchFields = [];
		$searchFields['RWGPSId'] = '';

		if ($this->metric)
			{
			$searchFields['km_min'] = 20;
			$searchFields['km_max'] = 300;
			$searchFields['metersPerKm_min'] = 1;
			$searchFields['metersPerKm_max'] = 100;
			}
		else
			{
			$searchFields['miles_min'] = 20;
			$searchFields['miles_max'] = 80;
			$searchFields['feetPerMile_min'] = 1;
			$searchFields['feetPerMile_max'] = 100;
			}
		$searchFields['club'] = 0;
		$searchFields['town'] = '';
		$searchFields['title'] = '';
		$searchFields['status'] = 200;
		$searchFields['description'] = '';
		$searchFields['csv'] = '';

		foreach ($searchFields as $key => $value)
			{
			if (! isset($parameters[$key]))
				{
				$parameters[$key] = $value;
				}
			}
		$parameters['p'] = 0;
		}

	/**
	 * @param array<string,string> $parameters
	 */
	protected function setSearch(array $parameters) : static
		{
		$condition = new \PHPFUI\ORM\Condition();

		if (! empty($parameters['RWGPSId']))
			{
			$condition->and('RWGPSId', $parameters['RWGPSId']);
			}

		$fields = $this->metric ? ['km', 'metersPerKm'] : ['miles', 'feetPerMile'];

		foreach ($fields as $field)
			{
			$index = $field . '_min';

			if (! empty($parameters[$index]))
				{
				$condition->and('RWGPS.' . $field, $parameters[$index], new \PHPFUI\ORM\Operator\GreaterThanEqual());
				}

			$index = $field . '_max';

			if (! empty($parameters[$index]))
				{
				$condition->and('RWGPS.' . $field, $parameters[$index], new \PHPFUI\ORM\Operator\LessThanEqual());
				}
			}

		if (! empty($parameters['club']))
			{
			$condition->and('club', 1);
			}

		if (! empty($parameters['town']))
			{
			$condition->and('town', '%' . $parameters['town'] . '%', new \PHPFUI\ORM\Operator\Like());
			}

		if (! empty($parameters['title']))
			{
			$condition->and('RWGPS.title', '%' . $parameters['title'] . '%', new \PHPFUI\ORM\Operator\Like());
			}

		if (! empty($parameters['description']))
			{
			$condition->and('RWGPS.description', '%' . $parameters['description'] . '%', new \PHPFUI\ORM\Operator\Like());
			}

		if (! empty($parameters['csv']))
			{
			$condition->and('csv', '%' . $parameters['csv'] . '%', new \PHPFUI\ORM\Operator\Like());
			}

		$this->rwgpsTable->setWhere($condition);
		$this->rwgpsTable->setOrderBy($this->metric ? 'km' : 'miles');

		return $this;
		}
	}
