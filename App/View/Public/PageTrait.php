<?php

namespace App\View\Public;

trait PageTrait
	{
	public function AreaCyclingCalendar(\App\View\Page $page) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$container->add(new \PHPFUI\Button('Add An Event', '/Calendar/addEvent'));
		$model = new \App\Model\Calendar();
		$view = new \App\View\Calendar($page);
		$container->add($view->showCalendar($model->getCalendarEntries($_GET), $_GET));

		return $container;
		}

	public function BikeShops(\App\View\Page $page) : \App\UI\Accordion
		{
		$getShops = static function(int $bikeShopAreaId) : \PHPFUI\Table
			{
			$bikeShopTable = new \App\Table\BikeShop();
			$bikeShopTable->addOrderBy('town');
			$bikeShopTable->setWhere(new \PHPFUI\ORM\Condition('bikeShopAreaId', $bikeShopAreaId));
			$table = new \PHPFUI\Table();

			foreach ($bikeShopTable->getRecordCursor() as $bikeShop)
				{
				$discount = $bikeShop->notes;

				if ($bikeShop->url)
					{
					$link = new \PHPFUI\Link($bikeShop->url, $bikeShop->name);
					}
				else
					{
					$link = $bikeShop->name;
					}
				$link .= '<br>' . \PHPFUI\Link::phone($bikeShop->phone);
				$address = "{$bikeShop->address}<br>{$bikeShop->town}, {$bikeShop->state} {$bikeShop->zip}";
				$table->addRow([$bikeShop->town . '<br>&nbsp;',
					$link,
					$address,
					$discount, ]);
				}

			return $table;
			};

		$bikeShopAccordion = new \App\UI\Accordion();
		$bikeShopAreaTable = new \App\Table\BikeShopArea();
		$bikeShopAreaTable->addOrderBy('area');

		foreach ($bikeShopAreaTable->getRecordCursor() as $bikeShop)
			{
			$bikeShopAccordion->addTab($bikeShop->area, $getShops($bikeShop->bikeShopAreaId));
			}

		return $bikeShopAccordion;
		}

	public function Board(\App\View\Page $page) : \PHPFUI\Container
		{
		$view = new \App\View\Admin\Board($page);

		return $view->publicView();
		}

	public function ClubCalendar(\App\View\Page $page) : \PHPFUI\Tabs | \PHPFUI\Table
		{
		$settingTable = new \App\Table\Setting();
		$abbrev = $settingTable->value('clubAbbrev');
		$tabs = [$abbrev . ' Only'];
		new \App\View\Content($page);
		$model = new \App\Model\Calendar();
		$view = new \App\View\Calendar($page);

		return $view->showCalendar($model->getCalendarEntries($_GET), $_GET, $tabs);
		}

	public function ContactUs(\App\View\Page $page) : \App\View\Public\ContactUs
		{
		$boardMemberTable = new \App\Table\BoardMember();

		return new \App\View\Public\ContactUs($page, $boardMemberTable->getBoardMembers());
		}

	public function GearCalculator(\App\View\Page $page) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$view = new \App\UI\GearCalculator($page);

		$container->add($view->show());

		return $container;
		}

	public function Join(\App\View\Page $page) : string | \PHPFUI\HTML5Element
		{
		if (! isset($_POST['ForgotPassword']) && ! $page->isDone())
			{
			$join = new \App\View\Membership\Join($page);

			return $join->getEmail();
			}

		return '';
		}

	public function LeaderInfo(\App\View\Page $page) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$table = new \PHPFUI\Table();
		$boardMemberTable = new \App\Table\BoardMember();
		$ridesChair = $boardMemberTable->getBoardMember('Rides Chair');

		if ($ridesChair)
			{
			$ridesChair = ' or ' . $ridesChair;
			}
		$container->add('<p>If you are thinking about volunteering to be a ride leader, contact one of the ride coordinators listed below' . $ridesChair . ' who can authorize you to use the <a href="/Leaders"><strong>Ride Leader Functions</strong></a>. </p>');
		$table->setHeaders(['level' => 'Ride Level',
			'coordinator' => 'Coordinator',
			'speed' => 'Average Speed',
			'description' => 'Description', ]);
		$categoryTable = new \App\Table\Category();

		foreach ($categoryTable->getAllCategories() as $category)
			{
			if ($category['minSpeed'] && $category['maxSpeed'])
				{
				$speed = "{$category['minSpeed']} - {$category['maxSpeed']}";
				}
			elseif ($category['minSpeed'])
				{
				$speed = "{$category['minSpeed']} and up";
				}
			else
				{
				$speed = "up to {$category['maxSpeed']}";
				}
			$coordinator = $category->coordinator;
			$table->addRow(['level' => "<H3>{$category['category']}</H3>",
				'speed' => $speed,
				'coordinator' => $coordinator->fullName(),
				'description' => $category['description'], ]);
			}
		$container->add($table);

		return $container;
		}

	public function MemberOfMonth(\App\View\Page $page) : \PHPFUI\GridX
		{
		$memberOfMonthTable = new \App\Table\MemberOfMonth();
		$view = new \App\View\Member\OfMonth($page);
		$MOM = $memberOfMonthTable->current();

		return $view->view($MOM, '');
		}

	public function Newsletters(\App\View\Page $page, int $year = 0) : \PHPFUI\Container
		{
		$view = new \App\View\Newsletter($page);

		return $view->display($year);
		}

	public function RideSchedule(\App\View\Page $page) : \App\UI\Accordion | \PHPFUI\Header
		{
		$ridesView = new \App\View\Rides($page);

		$settingTable = new \App\Table\Setting();
		$limit = (int)$settingTable->value('publicRideListLimit');
		$showNoLeader = (int)$settingTable->value('NoLeadersOnPublicSchedule');

		return $ridesView->schedule(\App\Table\Ride::upcomingRides($limit), showNoLeader:$showNoLeader);
		}

	public function Store(\App\View\Page $page) : \PHPFUI\Container
		{
		$storeView = new \App\View\Store($page);

		return $storeView->shop(new \App\Model\Cart());
		}

	public function UpcomingClubEvents(\App\View\Page $page) : \App\UI\Accordion | \PHPFUI\Header
		{
		$view = new \App\View\Event\Events($page);
		$eventTable = new \App\Table\Event();
		$eventTable->setUpcomingCursor(false);
		$cursor = $eventTable->getDataObjectCursor();

		return $view->show($cursor);
		}
	}
