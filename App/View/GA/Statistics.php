<?php

namespace App\View\GA;

class Statistics implements \Stringable
	{
	public function __construct(\App\View\Page $page, private readonly \App\Record\GaEvent $event) // @phpstan-ignore constructor.unusedParameter
		{
		}

	public function __toString() : string
		{
		$container = new \PHPFUI\Container();
		$gaRiderTable = new \App\Table\GaRider();
		$gaRiderTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $this->event->gaEventId));
		$gaRiderTable->addOrderBy('signedUpOn');
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

		$container->add(new \PHPFUI\SubHeader('Rider Options Selected'));
		$options = $this->event->GaOptionChildren;
		$accordion = new \App\UI\Accordion();

		foreach ($options as $option)
			{
			$accordion->addTab($option->optionName, $this->getOptionSummary($option));
			}
		$container->add($accordion);

		$container->add(new \PHPFUI\SubHeader('Sign Up Trends'));

		$headers = ['week' => 'Week Starting'];
		$dayOfWeek = [];

		for ($i = 1; $i <= 7; ++$i)
			{
			$dow = \date('D', \strtotime(\App\Tools\Date::toString($i)));
			$dayOfWeek["<b>{$dow}</b>"] = $dow;
			}

		foreach ($dayOfWeek as $dow)
			{
			$headers[$dow] = $dow;
			}
		$headers['total'] = 'Total';
		$headers['cume'] = 'Cume';

		$table = new \PHPFUI\Table();
		$table->setHeaders($headers);
		$riders = $gaRiderTable->getRecordCursor();

		if (\count($riders))
			{
			$baseDate = \App\Tools\Date::fromString(\substr($riders->current()->signedUpOn, 0, 10));
			$baseDate += 7 - ($baseDate % 7);
			$baseDate -= 7;
			$rideCounts = [];
			$rideTotals = [];

			foreach ($dayOfWeek as $dow)
				{
				$rideCounts[$dow] = 0;
				$rideTotals[$dow] = 0;
				}
			$cume = $total = 0;
			$endDate = $baseDate + 6;
			$date = \App\Tools\Date::today();

			foreach ($riders as $rider)
				{
				$date = \App\Tools\Date::fromString(\substr($rider->signedUpOn, 0, 10));

				if ($date > $endDate)
					{
					$rideCounts['week'] = \App\Tools\Date::toString($baseDate);
					$rideCounts['cume'] = $cume;
					$rideCounts['total'] = $total;
					$table->addRow($rideCounts);
					$rideCounts = [];

					foreach ($dayOfWeek as $dow)
						{
						$rideCounts[$dow] = 0;
						}
					$total = 0;
					$baseDate += 7;
					$endDate = $baseDate + 6;
					}

				$dow = \date('D', \strtotime($rider->signedUpOn));
				++$rideCounts[$dow];
				++$rideTotals[$dow];
				$total++;
				$cume++;
				}
			$rideTotals['week'] = '<b>Day Totals</b>';
			$table->addRow(\array_flip($dayOfWeek));
			$table->addRow($rideTotals);
			$container->add($table);
			}

		return (string)"{$container}";
		}

	private function getOptionSummary(\App\Record\GaOption $option) : \PHPFUI\Container
		{
		$table = new \PHPFUI\Table();
		$gaRiderSelectionTable = new \App\Table\GaRiderSelection();
		$gaRiderSelectionTable->addJoin('gaSelection');
		$condition = new \PHPFUI\ORM\Condition('gaEventId', $option->gaEventId);
		$condition->and(new \PHPFUI\ORM\Literal('gaSelection.gaOptionId'), $option->gaOptionId);
		$gaRiderSelectionTable->setWhere($condition);
		$gaRiderSelectionTable->addGroupBy(new \PHPFUI\ORM\Literal('gaSelection.gaSelectionId'));
		$gaRiderSelectionTable->addOrderBy(new \PHPFUI\ORM\Literal('gaSelection.ordering'));
		$gaRiderSelectionTable->addSelect('selectionName');
		$gaRiderSelectionTable->addSelect(new \PHPFUI\ORM\Literal('count(*)'), 'count');

		foreach ($gaRiderSelectionTable->getArrayCursor() as $row)
			{
			$table->addRow(\array_values($row));
			}

		$container = new \PHPFUI\Container();
		$container->add($table);

		return $container;
		}
	}
