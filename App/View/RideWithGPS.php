<?php

namespace App\View;

class RideWithGPS
	{
	private readonly bool $metric;

	private readonly \App\Model\RideWithGPS $model;

	private readonly \App\UI\RWGPSPicker $rwgpsPicker;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->model = new \App\Model\RideWithGPS();
		$this->rwgpsPicker = new \App\UI\RWGPSPicker($page, 'RWGPSAlternateId', 'Select an alternate route by title, street name or town');
		$this->metric = 'km' == $page->value('RWGPSUnits');

		$RWGPSId = \App\Model\RideWithGPS::getRWGPSIdFromLink($_POST['rwgpsUrl'] ?? '');

		if (\App\Model\Session::checkCSRF() && ! empty($_POST['submit']) && $RWGPSId['RWGPSId'])
			{
			$rwgpsRecord = new \App\Record\RWGPS($RWGPSId['RWGPSId']);

			if ($rwgpsRecord->empty())
				{
				$rwgpsRecord->setFrom($RWGPSId);
				$rwgpsRecord->insertOrUpdate();
				}

			$rwgps = $this->model->scrape($rwgpsRecord);

			if ($rwgps)
				{
				$rwgps->update();
				}
			$this->page->redirect($this->page->getBaseURL() . '/' . $RWGPSId['RWGPSId']);
			}
		}

	public function additional(\App\Record\RWGPS $rwgps) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$key = ['RWGPSId' => $rwgps->RWGPSId, 'memberId' => \App\Model\Session::signedInMemberId()];
		$rwgpsRating = new \App\Record\RWGPSRating($key);
		$rwgpsComment = new \App\Record\RWGPSComment($key);

		if (\App\Model\Session::checkCSRF())
			{
			if (($_POST['submit'] ?? '') == 'Rate It')
				{
				$rwgpsRating->setFrom($key);
				$rwgpsRating->rating = (int)($_POST['rating'] ?? 0);
				$rwgpsRating->insertOrUpdate();
				$this->page->redirect();
				}
			elseif (($_POST['submit'] ?? '') == 'Add Comment')
				{
				$rwgpsComment->setFrom($key);
				$rwgpsComment->comments = $_POST['comments'];
				$rwgpsComment->save();
				$this->page->redirect();
				}
			elseif (($_POST['submit'] ?? '') == 'Add Alternate Route')
				{
				$alternateRoute = new \App\Record\RWGPSAlternate();
				$alternateRoute->RWGPS = $rwgps;
				$alternateRoute->RWGPSAlternateId = (int)$_POST['RWGPSAlternateId'];
				$alternateRoute->memberId = \App\Model\Session::signedInMemberId();
				$alternateRoute->insertOrIgnore();

				$this->page->redirect();
				}
			}

		$ratingSet = new \PHPFUI\FieldSet('Rating');
		$multiColumn = new \PHPFUI\HTML5Element('div');
		$multiColumn->addClass('clearfix');
		$ratingResult = $rwgps->rating();

		if (null !== $ratingResult['rating'])
			{
			$rating = \number_format((float)$ratingResult['rating'], 1);
			$starBar = new \App\UI\StarBar(5, (float)$rating);
			$starBar->add(" <b>{$rating}</b> ");
			$starBar->add(" <b>Total: {$ratingResult['count']}</b> ");
			$multiColumn->add($starBar);
			}
		$rateItButton = new \PHPFUI\Button('Rate It');
		$rateItButton->addClass('success');
		$rateItButton->addClass('float-right');
		$this->addRatingReveal($rateItButton, $rwgpsRating);
		$multiColumn->add($rateItButton);

		$ratingSet->add($multiColumn);
		$container->add($ratingSet);

		$routesSet = new \PHPFUI\FieldSet('Alternate Routes');
		$alternateRouteButton = new \PHPFUI\Button('Add Alternate Route');

		$alternateRouteTable = new \App\UI\RecordCursorTable($rwgps->alternateRoutes);
		$headers = ['Route', 'Member', 'Del'];
		$canDeleteAlternate = $this->page->isAuthorized('Delete Alternate RWGPS Route');

		$deleter = new \App\Model\DeleteRecord($this->page, $alternateRouteTable, new \App\Table\RWGPSAlternate(), 'Are you sure you want to permanently delete this alternate route?');
		$deleter->setConditionalCallback(static fn (array $comment) => $comment['memberId'] == \App\Model\Session::signedInMemberId() || $canDeleteAlternate);
		$alternateRouteTable->addCustomColumn('RWGPSId_RWGPSAlternateId', static fn (array $alternate) => $alternate['RWGPSId'] . '_' . $alternate['RWGPSAlternateId']);
		$alternateRouteTable->addCustomColumn('Route', static function(array $alternate) {$rwgps = new \App\Record\RWGPS($alternate['RWGPSAlternateId']);

			return new \PHPFUI\Link("/RWGPS/detail/{$rwgps->RWGPSId}", \PHPFUI\TextHelper::unhtmlentities($rwgps->title) . ' - ' . $rwgps->RWGPSId, false);});
		$alternateRouteTable->addCustomColumn('Del', $deleter->columnCallback(...));
		$alternateRouteTable->addCustomColumn('Member', static function(array $alternate) {$member = new \App\Record\Member($alternate['memberId']);

			return $member->fullName();});
		$alternateRouteTable->setHeaders($headers);
		$routesSet->add($alternateRouteTable);
		$this->addAlternateRouteReveal($alternateRouteButton, $rwgps);
		$routesSet->add($alternateRouteButton);
		$container->add($routesSet);

		$commentSet = new \PHPFUI\FieldSet('Route Comments');

		$commentTable = new \App\UI\RecordCursorTable($rwgps->comments);
		$headers = ['Comments', 'Member', 'At', 'Del'];
		$canDeleteComment = $this->page->isAuthorized('Delete RWGPS Comment');

		$deleter = new \App\Model\DeleteRecord($this->page, $commentTable, new \App\Table\RWGPSComment(), 'Are you sure you want to permanently delete this comment?');
		$deleter->setConditionalCallback(static fn (array $comment) => $comment['memberId'] == \App\Model\Session::signedInMemberId() || $canDeleteComment);
		$commentTable->addCustomColumn('RWGPSId_memberId', static fn (array $comment) => $comment['RWGPSId'] . '_' . $comment['memberId']);
		$commentTable->addCustomColumn('Del', $deleter->columnCallback(...));
		$commentTable->addCustomColumn('Comments', static fn (array $comment) => \str_replace("\n", '<br>', $comment['comments']));
		$commentTable->addCustomColumn('Member', static function(array $comment) {$member = new \App\Record\Member($comment['memberId']);

			return $member->fullName();});
		$commentTable->addCustomColumn('At', static fn (array $comments) => $comments['lastEdited']);
		$commentTable->setHeaders($headers);
		$commentSet->add($commentTable);

		$commentButton = new \PHPFUI\Button('Add Comment');
		$this->addCommentReveal($commentButton, $rwgpsComment, $rwgps->title);
		$commentSet->add($commentButton);
		$container->add($commentSet);

		return $container;
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

	public function edit() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$settingsSaver = new \App\Model\SettingsSaver();
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Club RideWithGPS Settings');
		$fieldSet->add(' You will need your RWGPS Club Id to enable RWGPS integration. Leave the Club Id empty to turn off.');
		$clubId = $settingsSaver->generateField('RideWithGPSClubId', 'Club Id');
		$clubId->setRequired(false);
		$fieldSet->add($clubId);

		$view = new \App\View\Coordinators($this->page);
		$fieldSet->add($view->getEmail('RideWithGPS Coordinator'));

		$clubUrl = $settingsSaver->generateField('RideWithGPSURL', 'Club Library URL', 'url', false);
		$fieldSet->add($clubUrl);

		$units = new \PHPFUI\Input\RadioGroup('RWGPSUnits', 'Measurement Units');
		$units->addButton('Imperial', 'Miles');
		$units->addButton('Metric', 'km');
		$fieldSet->add($settingsSaver->generateField('RWGPSUnits', 'Measurement Units', $units));

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

			if ($this->metric)
				{
				$fieldSet->add(new \App\UI\Display('Distance (km)', $rwgps->km ?? 0));
				$fieldSet->add(new \App\UI\Display('Elevation (m)', \number_format($rwgps->elevationMeters ?? 0, 1)));
				}
			else
				{
				$fieldSet->add(new \App\UI\Display('Mileage', $rwgps->miles ?? 0));
				$fieldSet->add(new \App\UI\Display('Elevation (ft)', \number_format($rwgps->elevationFeet ?? 0, 0)));
				}
			$fieldSet->add(new \App\UI\Display('Town', $rwgps->town));
			$fieldSet->add(new \App\UI\Display('State', $rwgps->state));
			$fieldSet->add(new \App\UI\Display('Zip', $rwgps->zip));
			$fieldSet->add(new \App\UI\Display('Club Library Route', $rwgps->club ? 'Yes' : 'Not Yet'));

			if ($rwgps->lastUpdated)
				{
				$fieldSet->add(new \App\UI\Display('Last Updated', $rwgps->lastUpdated));
				}
			}
		else
			{
			$fieldSet->add(new \PHPFUI\SubHeader('Ride With GPS not found'));
			}

		return $fieldSet;
		}

	public function list(\App\Table\RWGPS $rwgpsTable, array $additionalHeaders = []) : \App\UI\ContinuousScrollTable
		{
		$sortableHeaders = ['title' => 'Name', ];

		if ($this->metric)
			{
			$sortableHeaders['km'] = 'km';
			$sortableHeaders['elevationMeters'] = 'Elev<wbr>ation';
			$sortableHeaders['metersPerKm'] = 'm/km';
			}
		else
			{
			$sortableHeaders['miles'] = 'Miles';
			$sortableHeaders['elevationFeet'] = 'Elev<wbr>ation';
			$sortableHeaders['feetPerMile'] = 'Ft/Mi';
			}
		$sortableHeaders['town'] = 'Start';
		$sortableHeaders['club'] = 'Club';
		$normalHeaders = $additionalHeaders + ['cuesheet' => 'Cue', 'stats' => 'Stats'];

		$view = new \App\UI\ContinuousScrollTable($this->page, $rwgpsTable);

		$view->addCustomColumn('title', static function(array $rwgps)
			{
			$name = new \PHPFUI\Link("/RWGPS/detail/{$rwgps['RWGPSId']}", \PHPFUI\TextHelper::unhtmlentities($rwgps['title']) . ' - ' . $rwgps['RWGPSId'], false);

			return $name;
			});
		$view->addCustomColumn('town', static function(array $rwgps)
			{
			$rwgpsRecord = new \App\Record\RWGPS();
			$rwgpsRecord->setFrom($rwgps);
			$url = \App\Model\RideWithGPS::getDirectionsLink($rwgpsRecord);

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

		$synced = $total = $clubCount = $mileage = $elevation = $mileageCount = $elevationCount = 0;

		foreach ($rwgpsTable->getRecordCursor() as $rwgps)
			{
			if ($rwgps->miles > 0)
				{
				if ($this->metric)
					{
					$mileage += $rwgps->km;
					}
				else
					{
					$mileage += $rwgps->miles;
					}
				++$mileageCount;
				}

			if ($rwgps->elevationFeet > 0)
				{
				if ($this->metric)
					{
					$elevation += $rwgps->elevationMeters;
					}
				else
					{
					$elevation += $rwgps->elevationFeet;
					}
				++$elevationCount;
				}
			$clubCount += $rwgps->club;
			++$total;

			if (\strlen($rwgps->csv ?? '') > 0)
				{
				++$synced;
				}
			}
		$container = new \PHPFUI\Container();

		$fieldSet = new \PHPFUI\FieldSet('Overall Ride With GPS Stats');
		$fieldSet->add(new \App\UI\Display('Total Routes', $total));
		$fieldSet->add(new \App\UI\Display('Synced Routes', $synced));
		$fieldSet->add(new \App\UI\Display('Unsynced Routes', $total - $synced));
		$fieldSet->add(new \App\UI\Display('Club Routes', $clubCount));
		$container->add($fieldSet);

		if ($this->metric)
			{
			$unitLarge = 'km';
			$unitSmall = 'm';
			}
		else
			{
			$unitLarge = 'miles';
			$unitSmall = 'ft';
			}

		$fieldSet = new \PHPFUI\FieldSet('Interesting Numbers');
		$fieldSet->add(new \App\UI\Display("Total Distance ({$unitLarge})", \number_format($mileage, 0)));
		$fieldSet->add(new \App\UI\Display("Average Ride Distance ({$unitLarge})", \number_format($mileage / ($mileageCount ?: 1), 1)));

		if ($this->metric)
			{
			$fieldSet->add(new \App\UI\Display('Total Kilometers of Elevation', \number_format($elevation / 1000, 0)));
			}
		else
			{
			$fieldSet->add(new \App\UI\Display('Total Miles of Elevation', \number_format($elevation / 5180, 0)));
			}
		$fieldSet->add(new \App\UI\Display("Average Ride Elevation ({$unitSmall})", \number_format($elevation / ($elevationCount ?: 1), 0)));
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

	private function addAlternateRouteReveal(\PHPFUI\HTML5Element $modalLink, \App\Record\RWGPS $rwgps) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Header("Alternate for {$rwgps->title}", 5));

		$form->add($this->rwgpsPicker->getEditControl());
		$submit = new \PHPFUI\Submit('Add Alternate Route');
		$submit->addClass('success');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function addCommentReveal(\PHPFUI\HTML5Element $modalLink, \App\Record\RWGPSComment $rwgpsComment, string $title) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\SubHeader($title));
		$textArea = new \PHPFUI\Input\TextArea('comments', 'Comments (limited to 255 characters)', $rwgpsComment->comments ?? '');
		$textArea->setRequired()->setAttribute('maxlength', (string)65760)->setAttribute('rows', (string)10);

		$form->add($textArea);
		$submit = new \PHPFUI\Submit('Add Comment');
		$submit->addClass('success');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function addRatingReveal(\PHPFUI\HTML5Element $modalLink, \App\Record\RWGPSRating $rwgpsRating) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \App\UI\RatingBar($this->page, 'rating', 5, $rwgpsRating->rating ?? 0));
		$form->add('<hr>');
		$submit = new \PHPFUI\Submit('Rate It');
		$submit->addClass('success');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
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
