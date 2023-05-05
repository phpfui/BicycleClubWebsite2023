<?php

namespace App\View\Public;

trait PageTrait
	{
	public function AreaCyclingCalendar() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$container->add(new \PHPFUI\Button('Add An Event', '/Calendar/addEvent'));
		$model = new \App\Model\Calendar();
		$view = new \App\View\Calendar($this);
		$container->add($view->showCalendar($model->getCalendarEntries($_GET), $_GET));

		return $container;
		}

	public function BikeShops() : \App\UI\Accordion
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

	public function Board() : \PHPFUI\Container
		{
		$view = new \App\View\Admin\Board($this);

		return $view->publicView();
		}

	public function ClubCalendar()
		{
		$abbrev = $this->settingTable->value('clubAbbrev');
		$tabs = [$abbrev . ' Only'];
		new \App\View\Content($this);
		$model = new \App\Model\Calendar();
		$view = new \App\View\Calendar($this);

		return $view->showCalendar($model->getCalendarEntries($_GET), $_GET, $tabs);
		}

	public function ContactUs() : \App\View\Public\ContactUs
		{
		$boardMemberTable = new \App\Table\BoardMember();

		return new \App\View\Public\ContactUs($this, $boardMemberTable->getBoardMembers());
		}

	public function Join() : string | \PHPFUI\HTML5Element
		{
		if (! isset($_POST['ForgotPassword']) && ! $this->getDone())
			{
			$join = new \App\View\Membership\Join($this);

			return $join->getEmail();
			}

		return '';
		}

	public function LeaderInfo() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$table = new \PHPFUI\Table();
		$boardMemberTable = new \App\Table\BoardMember();
		$container->add('<p>If you are thinking about volunteering to be a ride leader, contact one of the ride coordinators listed below or ' . $boardMemberTable->getBoardMember('Rides Chair') . ' who can authorize you to use the <a href="/Leaders"><strong>Ride Leader Functions</strong></a>. </p>');
		$table->setHeaders(['level' => 'Ride Level',
			'coordinator' => 'Coordinator',
			'speed' => 'Average Speed',
			'description' => 'Description', ]);
		$categoryTable = new \App\Table\Category();
		$categories = $categoryTable->getAllCategories();

		foreach ($categories as $category)
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
			$coordinator = new \App\Record\Member($category['coordinator']);
			$table->addRow(['level' => "<H3>{$category['category']}</H3>",
				'speed' => $speed,
				'coordinator' => $coordinator->fullName(),
				'description' => $category['description'], ]);
			}
		$container->add($table);

		return $container;
		}

	public function MemberOfMonth() : \PHPFUI\GridX
		{
		$memberOfMonthTable = new \App\Table\MemberOfMonth();
		$view = new \App\View\Member\OfMonth($this);
		$MOM = $memberOfMonthTable->current();

		return $view->view($MOM, '');
		}

	public function RideSchedule()
		{
		$ridesView = new \App\View\Rides($this);

		$settingTable = new \App\Table\Setting();
		$limit = (int)$settingTable->value('publicRideListLimit');

		return $ridesView->schedule(\App\Table\Ride::upcomingRides($limit));
		}

	public function Store()
		{
		$storeApp = new \App\WWW\Store($this->getController());
		$storeView = new \App\View\Store($this);

		return $storeView->shop($storeApp->getCartModel());
		}

	public function UpcomingClubEvents()
		{
		$view = new \App\View\Event\Events($this);
		$eventTable = new \App\Table\Event();
		$eventTable->setUpcomingCursor(false);
		$cursor = $eventTable->getDataObjectCursor();

		return $view->show($cursor);
		}
	}
