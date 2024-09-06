<?php

namespace App\Migration;

class Migration_54 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'No required fields';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('blogItem', 'ranking', 'int default "0"');

		$this->addColumn('event', 'requireComment', 'int default 0');

		$this->alterColumn('event', 'checks', 'int default "0"');
		$this->alterColumn('event', 'door', 'int default "0"');
		$this->alterColumn('event', 'maxReservations', 'int default "4"');
		$this->alterColumn('event', 'membersOnly', 'int default "1"');
		$this->alterColumn('event', 'paypal', 'int default "1"');

		$this->alterColumn('invoice', 'paidByCheck', 'int default "0"');
		$this->alterColumn('invoice', 'paypaltx', 'varchar(50) default ""');

		$this->alterColumn('invoiceItem', 'type', 'int default "0"');

		$this->alterColumn('journalItem', 'timeSent', 'datetime DEFAULT CURRENT_TIMESTAMP');

		return true;
		}
	}
