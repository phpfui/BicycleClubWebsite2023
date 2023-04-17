<?php

namespace App\View\GA;

class Statistics implements \Stringable
	{
	// @phpstan-ignore-next-line
	public function __construct(\App\View\Page $page, private readonly \App\Record\GaEvent $event)
		{
		}

	public function __toString() : string
		{
		$container = new \PHPFUI\Container();
		$gaRiderTable = new \App\Table\GaRider();
		$gaRiderTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $this->event->gaEventId));
		$members = $nonMembers = $paidRiders = $unpaidRiders = 0;
		$routes = $incentivesClaimed = $answers = [];

		foreach ($gaRiderTable->getRecordCursor() as $rider)
			{
			if (0 == $rider->pending)
				{
				if ($rider->memberId > 0)
					{
					$members++;
					}
				else
					{
					$nonMembers++;
					}

				if (! isset($answers[$rider->referral]))
					{
					$answers[$rider->referral] = 0;
					}
				++$answers[$rider->referral];

				if (isset($routes[$rider->gaRideId]))
					{
					$routes[(int)$rider->gaRideId] += 1;
					}
				else
					{
					$routes[(int)$rider->gaRideId] = 1;
					}

				if (isset($incentivesClaimed[$rider->gaIncentiveId]))
					{
					$incentivesClaimed[(int)$rider->gaIncentiveId] += 1;
					}
				else
					{
					$incentivesClaimed[(int)$rider->gaIncentiveId] = 1;
					}
				$paidRiders++;
				}
			else
				{
				$unpaidRiders++;
				}
			}
		$container->add(new \PHPFUI\Header("{$paidRiders} paid attendees.", 6));
		$container->add(new \PHPFUI\Header("{$unpaidRiders} unpaid riders.", 6));
		$container->add(new \PHPFUI\Header("{$members} club members.", 6));
		$container->add(new \PHPFUI\Header("{$nonMembers} non members.", 6));
		$gaIncentivesTable = new \App\Table\GaIncentive();
		$gaIncentivesTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $this->event->gaEventId));

		if (\count($gaIncentivesTable))
			{
			$container->add(new \PHPFUI\SubHeader('Incentives Selected'));
			$table = new \PHPFUI\Table();
			$table->setHeaders(['incentive' => 'Incentive', 'number' => 'Count']);
			$total = 0;

			foreach ($incentivesClaimed as $key => $value)
				{
				$total += $value;
				$incentive = new \App\Record\GaIncentive($key);
				$description = $incentive->empty() ? 'Not Selected' : $incentive->description;
				$table->addRow(['incentive' => $description, 'number' => $value]);
				}
			$table->addRow(['incentive' => '<b>Grand Total</b>', 'number' => "<b>{$total}</b>"]);
			$container->add($table);
			}
		$gaAnswerTable = new \App\Table\GaAnswer();
		$gaAnswerTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $this->event->gaEventId));

		if (\count($gaAnswerTable))
			{
			$container->add(new \PHPFUI\SubHeader($this->event->question));
			$table = new \PHPFUI\Table();
			$table->setHeaders(['answer' => 'Answer', 'number' => 'Count']);
			$total = $value = 0;

			foreach ($answers as $key => $value)
				{
				$total += $value;
				$answer = new \App\Record\GaAnswer($key);

				if (! $answer->loaded())
					{
					$answer->answer = 'Not Selected (member)';
					}
				$table->addRow(['answer' => $answer['answer'], 'number' => $value]);
				}
			$table->addRow(['answer' => '<b>Grand Total</b>', 'number' => "<b>{$total}</b>"]);
			$container->add($table);
			}
		$container->add(new \PHPFUI\SubHeader('Sign Up Trends'));

		$gaRideTable = new \App\Table\GaRide();
		$gaRideTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $this->event->gaEventId))->addOrderBy('distance');
		$table = new \PHPFUI\Table();
		$table->addHeader('week', 'Week Starting');
		$table->addHeader((string)0, 'None');

		foreach ($gaRideTable->getRecordCursor() as $ride)
			{
			$table->addHeader($ride->gaRideId, $ride->distance);
			}
		$table->addHeader('total', 'Total');
		$table->addHeader('cume', 'Cume');
		$riders = $gaRiderTable->getRidersBySignup($this->event);

		if (\count($riders))
			{

			$baseDate = \unixtojd(\strtotime((string)$riders->current()->signedUpOn));
			$baseDate += 7 - ($baseDate % 7);
			$baseDate -= 7;
			$rideTotals = [];
			$cume = $total = 0;
			$endDate = $baseDate + 6;
			$rideCounts = [];
			$date = \App\Tools\Date::today();

			foreach ($riders as $rider)
				{
				$date = \unixtojd(\strtotime((string)$rider->signedUpOn));

				if ($date > $endDate)
					{
					$rideCounts['week'] = \App\Tools\Date::toString($date);
					$rideCounts['cume'] = $cume;
					$rideCounts['total'] = $total;
					$table->addRow($rideCounts);
					$rideCounts = [];
					$total = 0;
					$baseDate += 7;
					$endDate = $baseDate + 6;
					}
				$total++;
				$cume++;
				$gaRideId = (int)$rider->gaRideId;

				if (! isset($rideCounts[$gaRideId]))
					{
					$rideCounts[$gaRideId] = 0;
					}
				$rideCounts[$gaRideId]++;

				if (! isset($rideTotals[$gaRideId]))
					{
					$rideTotals[$gaRideId] = 0;
					}
				$rideTotals[$gaRideId]++;
				}
			$rideCounts['week'] = \App\Tools\Date::toString($date);
			$rideCounts['cume'] = $cume;
			$rideCounts['total'] = $total;
			$table->addRow($rideCounts);
			$rideTotals['week'] = 'Totals';
			$rideTotals['cume'] = $cume;
			$rideTotals['total'] = $cume;

			foreach ($rideTotals as &$value)
				{
				$value = "<b>{$value}</b>";
				}
			unset($value);
			$table->addRow($rideTotals);
			$container->add($table);
			}

		return (string)"{$container}";
		}
	}
