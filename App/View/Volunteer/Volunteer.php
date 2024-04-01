<?php

namespace App\View\Volunteer;

class Volunteer
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function getEvents(\PHPFUI\ORM\DataObjectCursor $events) : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();
		$table->addHeader('name', 'Events');
		$table->addHeader('date', 'Date');

		foreach ($events as $eventRecord)
			{
			$event = $eventRecord->toArray();
			$event['name'] = "<a href='{$this->page->getBaseURL()}/{$event['jobEventId']}'>{$event['name']}</a>";
			$event['date'] = \App\Tools\Date::formatString('D M j Y', $event['date']);
			$table->addRow($event);
			}

		return $table;
		}

	public function output(\App\Record\JobEvent $jobEvent) : \PHPFUI\Container
		{
		$output = new \PHPFUI\Container();

		$content = new \App\View\Content($this->page);
		$output->add($content->getDisplayCategoryHTML('Volunteer'));

		if (! $jobEvent->empty())
			{
			$jobTable = new \App\Table\Job();
			$jobs = $jobTable->getJobs($jobEvent);
			$table = new \PHPFUI\Table();
			$table->addHeader('date', 'Date');
			$table->addHeader('title', 'Job');
			$table->addHeader('available', '# Left');
			$table->addHeader('location', 'Location');
			$totalAvailable = 0;

			foreach ($jobs as $jobObject)
				{
				$job = $jobObject->toArray();
				$avail = $job['needed'] - $job['taken'];

				if ($avail > 0)
					{
					++$totalAvailable;
					$job['available'] = $avail;
					$url = "/Volunteer/signup/{$job['jobId']}";
					$icon = new \PHPFUI\FAIcon('fas', 'plus-square', $url);
					$toolTip = new \PHPFUI\ToolTip("<a href='{$url}'>{$job['title']}</a>", $job['description']);
					$job['title'] = "{$toolTip}&nbsp;{$icon}";
					}
				else
					{
					$job['available'] = 0;
					$job['title'] = new \PHPFUI\ToolTip("{$job['title']} <b>SOLD OUT!</b>", $job['description']);
					}
				$table->addRow($job);
				}

			if ($totalAvailable)
				{
				$output->add(new \PHPFUI\SubHeader('Please Select A Specific Job for ' . $jobEvent->name));
				$output->add($table);
				}
			else
				{
				$output->add(new \PHPFUI\SubHeader('There are currently no jobs available for ' . $jobEvent->name));
				}
			}
		else
			{
			$jobEventTable = new \App\Table\JobEvent();
			$events = $jobEventTable->getJobEvents(\App\Tools\Date::todayString());
			$count = \count($events);

			if ($count > 1)
				{
				$output->add(new \PHPFUI\SubHeader('Select An Event To Volunteer'));
				$output->add($this->getEvents($events));
				}
			elseif (1 == $count)
				{
				$event = $events->current();
				$this->page->redirect("{$this->page->getBaseURL()}/{$event->jobEventId}");
				}
			else
				{
				$output->add(new \PHPFUI\Header('There are no events that need Volunteers at this time', 4));
				}
			}

		return $output;
		}
	}
