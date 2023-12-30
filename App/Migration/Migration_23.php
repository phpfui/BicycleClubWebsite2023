<?php

namespace App\Migration;

class Migration_23 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add General Admission options';
		}

	public function down() : bool
		{
		$this->dropTable('gaRiderSelection');
		$this->dropTable('gaOption');
		$this->dropTable('gaSelection');

		return true;
		}

	public function up() : bool
		{
		$this->dropTable('gaRiderSelection');
		$this->dropTable('gaOption');
		$this->dropTable('gaSelection');

		$this->runSQL('create table gaOption (
									gaOptionId int not null primary key auto_increment,
									gaEventId int not null,
									optionName varchar(255) not null,
									ordering int not null default 0,
									required int not null default 0,
									maximumAllowed int not null default 0,
									price DECIMAL(7,2));');

		$this->runSQL('create table gaSelection (
									gaSelectionId int not null primary key auto_increment,
									gaEventId int not null,
									gaOptionId int not null,
									selectionName varchar(255) not null,
									ordering int not null default 0,
									additionalPrice DECIMAL(7,2));');

		$this->runSQL('create table gaRiderSelection (
									gaRiderId int not null,
									gaOptionId int not null,
									gaSelectionId int not null,
									primary key (gaRiderId,	gaOptionId, gaSelectionId));');

		$incentives = \PHPFUI\ORM::getArrayCursor('select * from gaIncentive order by gaEventId');
		$eventId = $order = 0;
		$option = new \App\DB\Migration\GaOption();

		foreach ($incentives as $incentive)
			{
			if ($incentive['gaEventId'] != $eventId)
				{
				$eventId = (int)$incentive['gaEventId'];
				$order = 1;
				$option = new \App\DB\Migration\GaOption();
				$option->ordering = (int)\PHPFUI\ORM::getValue('select count(*) from gaOption where gaEventId=?', [$eventId]) + 1;
				$option->optionName = \PHPFUI\ORM::getValue('select incentiveName from gaEvent where gaEventId=?', [$eventId]);
				$option->maximumAllowed = (int)\PHPFUI\ORM::getValue('select incentiveCount from gaEvent where gaEventId=?', [$eventId]);
				$option->gaEventId = $eventId;
				$option->insert();
				}
			$selection = new \App\DB\Migration\GaSelection();
			$selection->gaOptionId = $option->gaOptionId;
			$selection->gaEventId = $eventId;
			$selection->selectionName = $incentive['description'];
			$selection->ordering = $order++;
			$selection->insert();
			$this->runSQL("update gaRider set gaIncentiveId={$selection->gaSelectionId} where gaIncentiveId={$incentive['gaIncentiveId']}");
			}

		$rides = \PHPFUI\ORM::getArrayCursor('select * from gaRide order by gaEventId');

		$eventId = 0;

		foreach ($rides as $ride)
			{
			if ($ride['gaEventId'] != $eventId)
				{
				$eventId = (int)$ride['gaEventId'];
				$order = 1;
				$option = new \App\DB\Migration\GaOption();
				$option->ordering = (int)\PHPFUI\ORM::getValue('select count(*) from gaOption where gaEventId=?', [$eventId]) + 1;
				$option->optionName = 'Route';
				$option->gaEventId = $eventId;
				$option->insert();
				}
			$selection = new \App\DB\Migration\GaSelection();
			$selection->gaOptionId = $option->gaOptionId;
			$selection->gaEventId = $eventId;
			$selection->selectionName = $ride['distance'] . ' miles';

			if ($ride['description'] > '')
				{
				$selection->selectionName .= ' - ' . $ride['description'];
				}

			$selection->ordering = $order++;
			$selection->additionalPrice = (float)$ride['extraPrice'];

			if (! $selection->additionalPrice)
				{
				$selection->additionalPrice = null;
				}
			$selection->insert();
			$this->runSQL("update gaRider set gaRideId={$selection->gaSelectionId} where gaRideId={$ride['gaRideId']}");
			}

		$answers = \PHPFUI\ORM::getArrayCursor('select * from gaAnswer order by gaEventId,ordering');

		$eventId = 0;

		foreach ($answers as $answer)
			{
			if ($answer['gaEventId'] != $eventId)
				{
				$eventId = (int)$answer['gaEventId'];
				$order = 1;
				$option = new \App\DB\Migration\GaOption();
				$option->ordering = (int)\PHPFUI\ORM::getValue('select count(*) from gaOption where gaEventId=?', [$eventId]) + 1;
				$option->optionName = 'Question';
				$option->gaEventId = $eventId;
				$option->insert();
				}
			$selection = new \App\DB\Migration\GaSelection();
			$selection->gaOptionId = $option->gaOptionId;
			$selection->gaEventId = $eventId;
			$selection->selectionName = $answer['answer'];
			$selection->ordering = $order++;
			$selection->insert();
			$this->runSQL("update gaRider set referral={$selection->gaSelectionId} where referral={$answer['gaAnswerId']}");
			}

		$riders = \PHPFUI\ORM::getArrayCursor('select * from gaRider');

		foreach ($riders as $rider)
			{
			$this->addRiderSelection($rider, 'gaRideId');
			$this->addRiderSelection($rider, 'gaIncentiveId');
			$this->addRiderSelection($rider, 'referral');
			}

		return true;
		}

	/**
	 * @param array<string,string> $rider
	 */
	private function addRiderSelection(array $rider, string $field) : void
		{
		if ($rider[$field] <= 0)
			{
			return;
			}
		$row = \PHPFUI\ORM::getRow('select * from gaSelection where gaSelectionId=?', [$rider[$field]] );
		$row['gaRiderId'] = $rider['gaRiderId'];
		$riderSelection = new \App\Record\GaRiderSelection();
		$riderSelection->setFrom($row);
		$riderSelection->insertOrIgnore();
		}
	}
