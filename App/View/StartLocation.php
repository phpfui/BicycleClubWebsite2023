<?php

namespace App\View;

class StartLocation
	{
	private readonly \App\Table\StartLocation $startLocationTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->startLocationTable = new \App\Table\StartLocation();
		$this->processAJAXRequest();
		}

	public function checkForAdd() : bool
		{
		if (isset($_POST['submit']) && 'Add' == $_POST['submit'] && \App\Model\Session::checkCSRF())
			{
			$model = new \App\Model\StartLocation();
			$model->add($_POST);
			$firstChar = $_POST['name'][0];
			$firstChar = \is_numeric($firstChar) ? '0-9' : $firstChar;
			$this->page->redirect("/Locations/locations/{$firstChar}");
			$this->page->done();

			return true;
			}

		return false;
		}

	public function edit(\App\Record\StartLocation $location) : \PHPFUI\Form
		{
		if ($location->startLocationId)
			{
			$submit = new \PHPFUI\Submit();
			$form = new \PHPFUI\Form($this->page, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add');
			$form = new \PHPFUI\Form($this->page);
			}

		if ($form->isMyCallback())
			{
			unset($_POST['startLocationId']);
			$location->setFrom($_POST);
			$location->update();
			$this->page->setResponse('Saved');
			}
		$type = $location->startLocationId ? 'Edit' : 'Add';
		$fieldSet = new \PHPFUI\FieldSet($type . ' Location');
		$location = new \App\Record\StartLocation($location->startLocationId);
		$form->add(new \PHPFUI\Input\Hidden('startLocationId', (string)$location->startLocationId));
		$name = new \PHPFUI\Input\Text('name', 'Location Name', $location->name);
		$name->setToolTip('Enter enough information so people know where it is.');
		$name->setRequired();
		$fieldSet->add($name);
		$url = new \PHPFUI\Input\Url('link', 'Link to web site', $location->link);
		$url->addAttribute('placeholder', 'http://www.');
		$url->setToolTip('Optional link to a web site of the start location, like Google Maps for example.');
		$fieldSet->add($url);
		$directions = new \PHPFUI\Input\TextArea('directions', 'Directions / Description', $location->directions);
		$directions->setToolTip('More detail about the start location like directions and where to park, etc.');
		$fieldSet->add($directions);

		if ($this->page->isAuthorized('Start Location Active Editing'))
			{
			$active = new \PHPFUI\Input\CheckBoxBoolean('active', 'Active', (bool)$location->active);
			$active->setToolTip('Uncheck to remove the ability to select this location.');
			$fieldSet->add($active);
			}

		$form->add($fieldSet);
		$cancelButton = new \PHPFUI\Button('Cancel', '/Locations/locations');
		$cancelButton->addClass('hollow')->addClass('alert');
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$buttonGroup->addButton($cancelButton);
		$form->add($buttonGroup);

		return $form;
		}

	public function getEditControl($value, string $name = 'startLocationId', string $title = 'Start Location') : \PHPFUI\Input\SelectAutoComplete
		{
		$select = new \PHPFUI\Input\SelectAutoComplete($this->page, $name, $title);
		$locations = $this->startLocationTable->getAll(['active' => 1]);
		$select->addOption('Please select a starting location', '', 0 == $value);

		foreach ($locations as $location)
			{
			$select->addOption($location['name'], $location['startLocationId'], $value == $location['startLocationId']);
			}

		return $select;
		}

	public function getText(int $id) : string
		{
		if ($id)
			{
			$location = new \App\Record\StartLocation($id);
			$link = "Start location {$id} not found";

			if (! $location->empty())
				{
				$link = \App\View\StartLocation::getTextFromArray($location->toArray());
				}
			}
		else
			{
			$link = 'location not set';
			}

		return $link;
		}

	public static function getTextFromArray(array $location) : string
		{
		if (empty($location['link']))
			{
			$link = $location['name'] ?? '';
			}
		else
			{
			$link = new \PHPFUI\Link($location['link'], \App\Tools\TextHelper::unhtmlentities($location['name']));
			}

		if (! empty($location['directions']))
			{
			$toolTip = new \PHPFUI\ToolTip($link, $location['directions']);
			$link = $toolTip;
			}

		return $link;
		}

	public function Merge() : string
		{
		$error = $form = '';

		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['keep']))
				{
				$count = 0;

				for ($i = 0; $i < $_POST['count']; ++$i)
					{
					if (! empty($_POST['delete' . $i]))
						{
						$this->startLocationTable->merge($_POST['number' . $i], $_POST['keep']);
						++$count;
						}
					}
				}
			}

		if (isset($_GET['startLocation']))
			{
			$locations = $this->startLocationTable->getByName($_GET['startLocation']);

			if (\count($locations) > 1)
				{
				$form = new \PHPFUI\Form($this->page);
				$fieldSet = new \PHPFUI\FieldSet('Instructions');
				$fieldSet->add('<ul>');
				$fieldSet->add('<li>Check the <b>Master</b> location.  This is the correct location you want to keep.</li>');
				$fieldSet->add('<li>All rides and cue sheets will now be listed with this start location.</li>');
				$fieldSet->add('<li>Check <b>Delete</b> on the duplicates you want to delete.</li>');
				$fieldSet->add('<li>Cue Sheets and rides from the duplicates will be reset to the <b>Master</b>.</li>');
				$fieldSet->add('<li>Don\'t check <b>Delete</b> on unrelated start locations and they will be left alone.</li>');
				$fieldSet->add('</ul>');
				$form->add($fieldSet);

				if (isset($count))
					{
					if ($count > 1)
						{
						$message = $count . ' start locations merged';
						}
					elseif ($count)
						{
						$message = '1 start location merged';
						}
					else
						{
						$message = 'No start locations merged';
						}
					$alert = new \App\UI\Alert($message);
					$form->add($alert);
					}
				$table = new \PHPFUI\Table();
				$table->setHeaders(['master' => 'Master',
					'delete' => 'Delete',
					'name' => 'Start Location', ]);
				$count = 0;

				foreach ($locations as $startLocation)
					{
					$row = $startLocation->toArray();
					$number = $row['startLocationId'];
					$hidden = new \PHPFUI\Input\Hidden('number' . $count, $number);
					$row['master'] = "<input type='radio' name='keep' value='{$number}'>" . $hidden;
					$row['delete'] = new \PHPFUI\Input\CheckBoxBoolean('delete' . $count);

					if ($row['link'])
						{
						$link = new \PHPFUI\Link($row['link'], $row['name']);
						$link->setAttribute('target', '_blank');
						$name = new \PHPFUI\ToolTip($link, $row['directions']);
						}
					else
						{
						$link = new \PHPFUI\HTML5Element('div');
						$link->add($row['name']);
						$name = new \PHPFUI\ToolTip($link, $row['directions']);
						}
					$row['name'] = $name;
					++$count;
					$table->addRow($row);
					}
				$form->add($table);
				$form->add(new \PHPFUI\Input\Hidden('count', (string)$count));
				$buttonGroup = new \App\UI\CancelButtonGroup();
				$buttonGroup->addButton(new \PHPFUI\Submit('Merge Start Locations'));
				$another = new \PHPFUI\Button('Merge Another Location', '/Locations/merge');
				$another->addClass('secondary');
				$buttonGroup->addButton($another);
				$form->add($buttonGroup);
				unset($error);
				}
			elseif (1 == \count($locations))
				{
				$error = "Only one match found for {$_GET['startLocation']}, nothing to merge.";
				}
			else
				{
				$error = "No matches found for {$_GET['startLocation']}";
				}
			}

		if (isset($error))
			{
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$form->setAttribute('method', 'get');

			if (! empty($error))
				{
				$alert = new \App\UI\Alert($error);
				$alert->addClass('warning');
				$form->add($alert);
				}
			$fieldSet = new \PHPFUI\FieldSet('Enter some text of a start location you want to merge');
			$fieldSet->add(new \PHPFUI\Input\Text('startLocation', 'Start Location'));
			$form->add($fieldSet);
			$form->add(new \PHPFUI\Submit('Find Start Locations'));
			}

		return $form;
		}

	public function showLocations() : \App\UI\ContinuousScrollTable
		{
		$startLocationTable = new \App\Table\StartLocation();

		$searchableHeaders = ['name', 'link'];
		$countHeaders = ['rides', 'cuesheets' => 'Cue<br>Sheets'];

		if ($this->page->isAuthorized('Delete Start Location'))
			{
			$countHeaders[] = 'del';
			}

		$rides = \App\Table\Ride::getCountByStartLocation();
		$cuesheets = \App\Table\CueSheet::getCountByStartLocation();

		$view = new \App\UI\ContinuousScrollTable($this->page, $startLocationTable);

		$deleter = new \App\Model\DeleteRecord($this->page, $view, $startLocationTable, 'Are you sure you want to permanently delete this start location?');
		$deleter->setConditionalCallback(static fn (array $location) => empty($rides[$location['startLocationId']]['count']) && empty($cuesheets[$location['startLocationId']]['count']));
		$view->addCustomColumn('del', $deleter->columnCallback(...));
		$view->addCustomColumn('active', static fn (array $location) => $location['active'] ? '<b>&check;</b>' : '');
		$view->addCustomColumn('link', static function(array $location)
			{
			$url = \parse_url($location['link'] ?? '', PHP_URL_HOST);

			return $url ? new \PHPFUI\Link($location['link'], $url) : '';
			});
		$view->addCustomColumn('rides', static function(array $location) use ($rides)
			{
			$count = $rides[$location['startLocationId']]['count'] ?? 0;

			if (! $count)
				{
				return $count;
				}

			return new \PHPFUI\Link('/Rides/forLocation/' . $location['startLocationId'], $count, false);
			});

		$view->addCustomColumn('cuesheets', static function(array $location) use ($cuesheets)
			{
			$count = $cuesheets[$location['startLocationId']]['count'] ?? 0;

			if (! $count)
				{
				return $count;
				}

			return new \PHPFUI\Link("/CueSheets/find?startLocation={$location['startLocationId']}&order=A", $count, false);
			});

		$view->addCustomColumn('name', static function(array $location)
			{
			$editLink = new \PHPFUI\Link('/Locations/edit/' . $location['startLocationId'], $location['name'], false);

			if (empty($location['directions']))
				{
				return $editLink;
				}

			return new \PHPFUI\ToolTip($editLink, $location['directions']);
			});

		$view->setSearchColumns($searchableHeaders);
		$searchableHeaders[] = 'active';
		$view->setSortableColumns($searchableHeaders);
		$view->setHeaders(\array_merge($searchableHeaders, $countHeaders));

		return $view;
		}

	protected function processAJAXRequest() : void
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteLocation':

					$location = new \App\Record\StartLocation((int)$_POST['startLocationId']);
					$location->delete();
					$this->page->setResponse($_POST['startLocationId']);

				}
			}
		}
	}
