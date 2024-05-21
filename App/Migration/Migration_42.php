<?php

namespace App\Migration;

class Migration_42 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Event comments per person';
		}

	public function down() : bool
		{
		$this->addColumn('reservation', 'comments', 'varchar(50)');
		$this->dropColumn('event', 'showComments');
		$this->dropColumn('event', 'commentTitle');
		$this->dropColumn('event', 'showRegistered');

		return $this->dropColumn('reservationPerson', 'comments');
		}

	public function up() : bool
		{
		$this->dropColumn('reservation', 'comments');
		$this->addColumn('reservationPerson', 'comments', 'varchar(255) not null default ""');
		$this->addColumn('event', 'commentTitle', 'varchar(255) not null default ""');
		$this->addColumn('event', 'showRegistered', 'tinyint(1) not null default 1');

		return $this->addColumn('event', 'showComments', 'tinyint(1) not null default 0');
		}
	}
