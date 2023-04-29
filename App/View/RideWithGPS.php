<?php

namespace App\View;

class RideWithGPS
	{
	private readonly \App\Model\RideWithGPS $model;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->model = new \App\Model\RideWithGPS();

		$RWGPSId = \App\Model\RideWithGPS::getRWGPSIdFromLink($_POST['rwgpsUrl'] ?? '');

		if (\App\Model\Session::checkCSRF() && ! empty($_POST['submit']) && $RWGPSId['RWGPSId'])
			{
			$rwgpsRecord = new \App\Record\RWGPS($RWGPSId['RWGPSId']);

			if ($rwgpsRecord->empty())
				{
				$rwgpsRecord->setFrom($RWGPSId);
				$rwgpsRecord->insertOrUpdate();
				}

			$rwgps = $this->model->scrape($rwgpsRecord, false);

			if ($rwgps)
				{
				$rwgps->update();
				}
			$this->page->redirect($this->page->getBaseURL() . '/' . $RWGPSId['RWGPSId']);
			}
		}

	public function addUpdate(\App\Record\RWGPS $rwgps) : \PHPFUI\Form | \PHPFUI\FieldSet
		{
		if ($rwgps->loaded())
			{
			return $this->info($rwgps);
			}
		$form = new \PHPFUI\Form($this->page);

		$fieldSet = new \PHPFUI\FieldSet('Enter Ride With GPS URL to Add / Update');
		$rwgpsUrl = new \PHPFUI\Input\Url('rwgpsUrl', 'Ride With GPS URL');
		$rwgpsUrl->setRequired();
		$fieldSet->add($rwgpsUrl);
		$fieldSet->add(new \App\UI\CancelButtonGroup(new \PHPFUI\Submit('Add / Update')));
		$form->add($fieldSet);

		return $form;
		}

	public function editSettings() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$settingsSaver = new \App\Model\SettingsSaver();
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('RideWithGPS API Settings');
		$fieldSet->add(' You will need to get the User Id, Club Id and API Key from a RideWithGPS representative. Once you have those, sign into the club account and generate the auth token here: <a href="https://ridewithgps.com/api" target="_blank">RideWithGPS API</a>. Leave the Club Id empty to turn off.');
		$clubId = $settingsSaver->generateField('RideWithGPSClubId', 'Club Id');
		$clubId->setRequired(false);
		$fieldSet->add($clubId);
		$userId = $settingsSaver->generateField('RideWithGPSUserId', 'User Id');
		$fieldSet->add($userId);
		$apiKey = $settingsSaver->generateField('RideWithGPSAPIKey', 'API Key');
		$fieldSet->add($apiKey);
		$authToken = $settingsSaver->generateField('RideWithGPSAuthToken', 'Auth Token');
		$fieldSet->add($authToken);
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Club RideWithGPS Settings');
		$view = new \App\View\Coordinators($this->page);
		$fieldSet->add($view->getEmail('RideWithGPS Coordinator'));
		$clubUrl = $settingsSaver->generateField('RideWithGPSURL', 'Club Library URL', 'url', false);
		$fieldSet->add($clubUrl);
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$settingsSaver->save();
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add(new \App\UI\CancelButtonGroup($submit));
			}

		return $form;
		}

	public function info(\App\Record\RWGPS $rwgps) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('RWGPS Information');

		if ($rwgps->loaded())
			{
			$idLink = new \PHPFUI\Link($this->model->getRouteLink($rwgps->RWGPSId));
			$idLink->addAttribute('target', '_blank');
			$fieldSet->add(new \App\UI\Display('Ride With GPS Link', $idLink));
			$directionsLink = new \PHPFUI\Link($this->model->getDirectionsLink($rwgps), 'Google');
			$directionsLink->addAttribute('target', '_blank');
			$fieldSet->add(new \App\UI\Display('Directions', $directionsLink));
			$fieldSet->add(new \App\UI\Display('Title', $rwgps->title));
			$fieldSet->add(new \App\UI\Display('Description', $rwgps->description));
			$fieldSet->add(new \App\UI\Display('Mileage', $rwgps->mileage ?? 0));
			$fieldSet->add(new \App\UI\Display('Elevation', $rwgps->elevation ?? 0));
			$fieldSet->add(new \App\UI\Display('Town', $rwgps->town));
			$fieldSet->add(new \App\UI\Display('State', $rwgps->state));
			$fieldSet->add(new \App\UI\Display('Zip', $rwgps->zip));
			$fieldSet->add(new \App\UI\Display('Club Library Route', $rwgps->club ? 'Yes' : 'Not Yet'));
			$fieldSet->add(new \App\UI\Display('Last Updated', $rwgps->lastUpdated));
			}
		else
			{
			$fieldSet->add(new \PHPFUI\SubHeader('Ride With GPS not found'));
			}

		return $fieldSet;
		}

	public function list(\App\Table\RWGPS $rwgpsTable, array $additionalHeaders = []) : \App\UI\ContinuousScrollTable
		{
		$sortableHeaders = ['title' => 'Name', 'mileage' => 'Mile<wbr>age', 'elevation' => 'Elev<wbr>ation', 'feetPerMile' => 'Ft/Mi', 'town' => 'Start', 'club' => 'Club', ];
		$normalHeaders = $additionalHeaders + ['cuesheet' => 'Cue', 'stats' => 'Stats'];

		$view = new \App\UI\ContinuousScrollTable($this->page, $rwgpsTable);

		$view->addCustomColumn('title', static function(array $rwgps)
			{
			$name = new \PHPFUI\Link(\App\Model\RideWithGPS::getRouteLink($rwgps['RWGPSId']), \PHPFUI\TextHelper::unhtmlentities($rwgps['title']));
			$name->addAttribute('target', '_blank');

			return $name;
			});
		$view->addCustomColumn('town', static function(array $rwgps)
			{
			$url = \App\Model\RideWithGPS::getDirectionsLink(new \App\Record\RWGPS($rwgps));

			if (! \str_starts_with($url, 'http'))
				{
				return '';
				}
			$start = new \PHPFUI\Link($url, $rwgps['town'], false);
			$start->addAttribute('target', '_blank');

			return $start;
			});

		$view->addCustomColumn('club', static fn (array $rwgps) => $rwgps['club'] ? '<b>&check;</b>' : '');
		$view->addCustomColumn('stats', $this->getStatsReveal(...));
		$view->addCustomColumn('cuesheet', static function(array $rwgps)
			{
			if (empty($rwgps['csv']))
				{
				return '';
				}

			return new \PHPFUI\FAIcon('fas', 'file-download', '/RWGPS/cueSheetRWGPS/' . $rwgps['RWGPSId']);
			});
		$view->addCustomColumn('date', static fn (array $rwgps) => $rwgps['rideDate'] ?? '');

		$view->setHeaders($sortableHeaders + $normalHeaders)->setSortableColumns(\array_keys($sortableHeaders));
		unset($sortableHeaders['club']);
		$view->setSearchColumns($sortableHeaders);

		return $view;
		}

	public function stats() : \PHPFUI\Container
		{
		$rwgpsTable = new \App\Table\RWGPS();

		$synced = $missing = $total = $clubCount = $mileage = $elevation = $mileageCount = $elevationCount = 0;

		foreach ($rwgpsTable->getRecordCursor() as $rwgps)
			{
			if ($rwgps->mileage > 0)
				{
				$mileage += $rwgps->mileage;
				++$mileageCount;
				}

			if ($rwgps->elevation > 0)
				{
				$elevation += $rwgps->elevation;
				++$elevationCount;
				}
			$clubCount += $rwgps->club;
			++$total;

			if ($rwgps->status > 200)
				{
				++$missing;
				}

			if (\strlen($rwgps->csv ?? '') > 0)
				{
				++$synced;
				}
			}
		$container = new \PHPFUI\Container();

		$fieldSet = new \PHPFUI\FieldSet('Overall Ride With GPS Stats');
		$fieldSet->add(new \App\UI\Display('Total Routes', $total));
		$fieldSet->add(new \App\UI\Display('Missing Routes', $missing));
		$fieldSet->add(new \App\UI\Display('Synced Routes', $synced));
		$fieldSet->add(new \App\UI\Display('Unsynced Routes', $total - $missing - $synced));
		$fieldSet->add(new \App\UI\Display('Club Routes', $clubCount));
		$container->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Interesting Numbers');
		$fieldSet->add(new \App\UI\Display('Total Mileage', \number_format($mileage, 0)));
		$fieldSet->add(new \App\UI\Display('Average Ride Mileage', \number_format($mileage / ($mileageCount ?: 1), 1)));
		$fieldSet->add(new \App\UI\Display('Total Miles of Elevation', \number_format($elevation / 5180, 0)));
		$fieldSet->add(new \App\UI\Display('Average Ride Elevation (ft)', \number_format($elevation / ($elevationCount ?: 1), 0)));
		$container->add($fieldSet);

		$container->add(new \PHPFUI\SubHeader('Start Towns'));
		$rwgpsTable->addSelect(new \PHPFUI\ORM\Literal('count(*)'), 'count');
		$rwgpsTable->addSelect('town');
		$rwgpsTable->setGroupBy('town');
		$rwgpsTable->setOrderBy('count', 'desc');
		$view = new \App\UI\ContinuousScrollTable($this->page, $rwgpsTable);
		$headers = ['town', 'count'];
		$view->setSearchColumns(['town'])->setHeaders($headers)->setSortableColumns($headers);
		$container->add($view);

		return $container;
		}

	private function getStatsReveal(array $rwgps) : \PHPFUI\FAIcon
		{
		$opener = new \PHPFUI\FAIcon('fas', 'info-circle');
		$reveal = new \PHPFUI\Reveal($this->page, $opener);
		$reveal->addClass('large');
		$reveal->add(new \PHPFUI\SubHeader($rwgps['title']));
		$div = new \PHPFUI\HTML5Element('div');
		$reveal->add($div);
		$reveal->add($reveal->getCloseButton());
		$reveal->loadUrlOnOpen('/RWGPS/stats/' . $rwgps['RWGPSId'], $div->getId());

		return $opener;
		}
	}
