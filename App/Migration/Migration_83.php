<?php

namespace App\Migration;

class Migration_83 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Clean tables and floats to decimal';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('discountCode', 'discount', 'decimal(10,2) NOT NULL DEFAULT "0.00"');
		$this->alterColumn('invoice', 'discount', 'decimal(10,2) NOT NULL DEFAULT "0.00"');

		$this->cleanTable(new \App\Table\AdditionalEmail());
		$this->cleanTable(new \App\Table\Banner());
		$this->cleanTable(new \App\Table\BikeShop());
		$this->cleanTable(new \App\Table\BoardMember());
		$this->cleanTable(new \App\Table\Calendar());
		$this->cleanTable(new \App\Table\CueSheet());
		$this->cleanTable(new \App\Table\CueSheetVersion());
		$this->cleanTable(new \App\Table\Customer());
		$this->cleanTable(new \App\Table\Event());
		$this->cleanTable(new \App\Table\Folder());
		$this->cleanTable(new \App\Table\Forum());
		$this->cleanTable(new \App\Table\ForumMessage());
		$this->cleanTable(new \App\Table\GaEvent());
		$this->cleanTable(new \App\Table\GaOption());
		$this->cleanTable(new \App\Table\GaRider());
		$this->cleanTable(new \App\Table\GaSelection());
		$this->cleanTable(new \App\Table\HeaderContent());
		$this->cleanTable(new \App\Table\Job());
		$this->cleanTable(new \App\Table\JobEvent());
		$this->cleanTable(new \App\Table\Member());
		$this->cleanTable(new \App\Table\MemberOfMonth());
		$this->cleanTable(new \App\Table\Membership());
		$this->cleanTable(new \App\Table\PollAnswer());
		$this->cleanTable(new \App\Table\PublicPage());
		$this->cleanTable(new \App\Table\Reservation());
		$this->cleanTable(new \App\Table\ReservationPerson());
		$this->cleanTable(new \App\Table\Ride());
		$this->cleanTable(new \App\Table\RWGPS());
		$this->cleanTable(new \App\Table\StartLocation());
		$this->cleanTable(new \App\Table\StoreItem());
		$this->cleanTable(new \App\Table\Story());
		$this->cleanTable(new \App\Table\SurveyQuestion());
		$this->cleanTable(new \App\Table\SystemEmail());
		$this->cleanTable(new \App\Table\VolunteerPollAnswer());

		return true;
		}

	private function cleanTable(\PHPFUI\ORM\Table $table) : void
		{
		foreach ($table as $record)
			{
			$record->update();
			}
		}
	}
