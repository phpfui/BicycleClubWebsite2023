<?php

namespace App\View\Ride;

class With
	{
	private \App\Record\Member $member;

	/**
	 * @param array<string,string> $get
	 */
	public function __construct(private readonly \PHPFUI\Page $page, array $get)
		{
		$this->member = new \App\Record\Member($get['memberId'] ?? 0);
		}

	public function __toString() : string
		{
		$form = new \PHPFUI\Form($this->page);
		$form->setAttribute('method', 'get');

		if ($this->member->loaded())
			{
			$form->add(new \PHPFUI\SubHeader($this->member->fullName()));
			$rideTable = new \App\Table\Ride();
			$paceTable = new \App\Table\Pace();
			$rides = $rideTable->with($this->member);

			$callout = new \PHPFUI\Callout('info');
			$callout->add("You have ridden {$rides->count()} times with {$this->member->fullName()}");
			$form->add($callout);

			$tabs = new \PHPFUI\Tabs(true);
			$startYear = '';
			$table = new \PHPFUI\Table();
			$table = new \PHPFUI\Table()->setHeaders(['Date', 'Time', 'Category', 'Ride Signup']);
			$active = true;
			$yearCount = 0;

			foreach ($rides as $ride)
				{
				$year = \substr($ride->rideDate, 0, 4);

				if ($year !== $startYear)
					{
					if ($startYear)
						{
						$table->addRow([
							'Date' => "<b>Total Rides in {$startYear}:</b>",
							'Time' => "<b>{$yearCount}</b>",
							'Category' => '',
							'Ride Signup' => '',
						]);
						$tabs->addTab($startYear, $table, $active);
						$yearCount = 0;
						$active = false;
						$table = new \PHPFUI\Table()->setHeaders(['Date', 'Time', 'Category', 'Ride Signup']);
						}
					$startYear = $year;
					}
				++$yearCount;
				$table->addRow([
					'Date' => \date('D M j', \App\Tools\Date::getUnixTimeStamp($ride->rideDate, '00:00:00')),
					'Time' => \App\Tools\TimeHelper::toSmallTime($ride->startTime),
					'Category' => $paceTable->getPace($ride->paceId),
					'Ride Signup' => new \PHPFUI\Link('/Rides/signedUp/' . $ride->rideId, $ride->title, false)->addAttribute('target', '_blank'),
				]);
				}

			$gridX = new \PHPFUI\GridX();
			$yearCell = new \PHPFUI\Cell(2, 2, 3)->add($tabs->getTabs());
			$rideCell = new \PHPFUI\Cell(10, 10, 9)->add($tabs->getContent());
			$gridX->add($yearCell);
			$gridX->add($rideCell);

			$form->add($gridX);
			}
		else
			{
			$form->add(new \PHPFUI\Header('Please select a member', 5));
			}

		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\NonMemberPickerNoSave('Enter Member Name'), 'memberId');
		$form->add($memberPicker->getEditControl());
		$form->add(new \PHPFUI\Submit('Show Rides With Member'));

		return "{$form}";
		}
	}
