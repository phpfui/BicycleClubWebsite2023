<?php

namespace App\View\Member;

// Member home page

class HomePage implements \Stringable
	{
	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Member $member)
		{
		}

	public function __toString() : string
		{
		$order = [];

		$today = \App\Tools\Date::today();
		// upcoming rides in my category
		$rides = \App\Table\Ride::getMyCategoryRides($this->member);

		$ride = $rides->current();

		if (! $ride->empty())
			{
			$daysOut = \App\Tools\Date::fromString($ride['rideDate']) - $today;
			$order[] = ['priority' => $daysOut, 'link' => '/Rides/My/category', 'li' => 'Upcoming rides in your categories'];
			}

		// new newsletter
		$newsletterTable = new \App\Table\Newsletter();
		$newsletter = $newsletterTable->getLatest();

		if ($newsletter->loaded())
			{
			$daysOut = $today - \App\Tools\Date::fromString($newsletter->dateAdded);
			$order[] = ['priority' => $daysOut, 'li' => \App\Tools\Date::formatString('F Y', $newsletter->date) . ' Newsletter published on ' . $newsletter->dateAdded,
				'link' => '/Newsletter/download/' . $newsletter->newsletterId, ];
			}

		// Member of the month
		$memberOfMonthTable = new \App\Table\MemberOfMonth();
		$MOM = $memberOfMonthTable->current();

		if ($MOM->loaded())
			{
			$daysOut = $today - \App\Tools\Date::fromString($MOM->month);

			if ($daysOut <= 31)
				{
				$year = (int)$MOM->month;
				$order[] = ['priority' => $daysOut, 'li' => \App\Tools\Date::formatString('F Y', $MOM->month) . ' Member Of The Month', 'link' => "/Membership/mom/{$year}/{$MOM->memberOfMonthId}"];
				}
			}

		// upcoming events
		$eventTable = new \App\Table\Event();
		$eventTable->setUpcomingCursor(false);

		if (\count($eventTable))
			{
			$output = new \PHPFUI\Container();
			$output->add(new \PHPFUI\Header('Upcoming Events', 4));
			$table = new \PHPFUI\Table();
			$table->setHeaders(['title' => 'Event', 'date' => 'Date', 'status' => 'Attending']);
			$first = 0;

			foreach ($eventTable->getArrayCursor() as $event)
				{
				$event['date'] = $event['eventDate'];

				if (! $first)
					{
					$first = \App\Tools\Date::fromString($event['eventDate']);
					}

				$reservation = new \App\Record\Reservation(['eventId' => $event['eventId'], 'memberId' => \App\Model\Session::signedInMemberId()]);

				if (! $reservation->loaded() || ($event['price'] && ! $reservation->paymentId))
					{
					$event['status'] = new \PHPFUI\Button('Sign Up', '/Events/signup/' . $event['eventId']);
					}
				else
					{
					$event['status'] = new \PHPFUI\Button('Attending', '/Events/confirm/' . $reservation->reservationId);
					}
				$table->addRow($event);
				}
			$output->add($table);
			$daysOut = $first - $today;
			$order[] = ['priority' => $daysOut, 'html' => $output, 'li' => 'Upcoming Events'];
			}

		// open polls
		$polls = \App\Table\Poll::current();

		if (\count($polls))
			{
			$output = new \PHPFUI\Container();
			$output->add(new \PHPFUI\Header('Open Polls', 4));
			$view = new \App\View\Polls($this->page);
			$output->add($view->listPolls($polls));
			$order[] = ['priority' => 0, 'html' => $output, 'li' => 'Member Poll Closing Soon'];
			}

		// new cuesheet added
		$cueSheetTable = new \App\Table\CueSheet();
		$cueSheetTable->setRecentlyAddedCursor();

		if (\count($cueSheetTable))
			{
			$cuesheet = $cueSheetTable->getRecordCursor()->current();
			$daysOut = $today - \App\Tools\Date::fromString($cuesheet->dateAdded);
			$order[] = ['priority' => $daysOut, 'li' => 'New Cuesheet from ' . $cuesheet->dateAdded, 'link' => '/CueSheets/recent'];
			}

		// Volunteer events
		$jobEventTable = new \App\Table\JobEvent();
		$events = $jobEventTable->getJobEvents(\App\Tools\Date::todayString());

		if (\count($events))
			{
			$event = $events->current();
			$daysOut = \App\Tools\Date::diff(\App\Tools\Date::todayString(), $event->cutoffDate);
			$order[] = ['priority' => $daysOut, 'li' => 'Volunteer for ' . $event->name, 'link' => '/Volunteer/pickAJob/' . $event->jobEventId];
			}

		// Public Page Content
		$publicPageTable = new \App\Table\PublicPage();
		$publicPageTable->setWhere(new \PHPFUI\ORM\Condition('homePageNotification', 1));

		foreach ($publicPageTable->getRecordCursor() as $page)
			{
			$story = \App\Table\Blog::getNewestStory($page->name);
			$daysOut = $today - \App\Tools\Date::fromString($story['date'] ?? '');

			if ($daysOut <= 14)
				{
				$order[] = ['priority' => $daysOut, 'li' => "<b>{$page->name}:</b> {$story['headline']}", 'link' => $page->url];
				}
			}

		\usort($order, static fn ($a, $b) => $a['priority'] <=> $b['priority']);

		$output = new \PHPFUI\Container();
		$ol = new \PHPFUI\UnorderedList();
		$counter = 0;

		$daysCutoff = (int)$this->page->value('homePageDaysBack');

		foreach ($order as $item)
			{
			if ($item['priority'] > $daysCutoff)
				{
				continue;
				}
			++$counter;
			$contents = "<a href='";

			if (isset($item['link']))
				{
				$contents .= $item['link'];
				}
			else
				{
				$contents .= '#' . $counter;
				}
			$contents .= "'>{$item['li']}</a>";
			$listItem = new \PHPFUI\ListItem($contents);
			$ol->addItem($listItem);
			}

		if (\count($ol))
			{
			$output->add(new \PHPFUI\SubHeader("What's Happening"));
			$output->add($ol);
			}
		$counter = 0;

		foreach ($order as $item)
			{
			if ($item['priority'] > $daysCutoff)
				{
				continue;
				}
			++$counter;

			if (isset($item['html']))
				{
				$output->add("<a name='{$counter}'></a>");
				$output->add($item['html']);
				}
			}

		$content = new \App\View\Content($this->page);
		$output->add($content->getDisplayCategoryHTML('User Home Page'));

		$rideView = new \App\View\Rides($this->page);
		$output->add(new \PHPFUI\SubHeader('Upcoming Rides'));
		$content = new \App\View\Content($this->page);
		$output->add($content->getDisplayCategoryHTML('Ride Schedule'));
		$limit = (int)$this->page->value('publicRideListLimit');
		$output->add($rideView->schedule(\App\Table\Ride::upcomingRides($limit)));

		return (string)$output;
		}
	}
