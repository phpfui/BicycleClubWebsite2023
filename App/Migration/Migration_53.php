<?php

namespace App\Migration;

class Migration_53 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Tweak to new schema';
		}

	public function down() : bool
		{
		$this->alterColumn('blogItem', 'ranking', 'int not null default "0"');

		$this->dropColumn('event', 'requireComment');

		$this->alterColumn('event', 'checks', 'int');
		$this->alterColumn('event', 'door', 'int');
		$this->alterColumn('event', 'maxReservations', 'int');
		$this->alterColumn('event', 'membersOnly', 'int');
		$this->alterColumn('event', 'paypal', 'int');

		$this->alterColumn('invoice', 'paidByCheck', 'int');
		$this->alterColumn('invoice', 'paypaltx', 'varchar(20)');

		$this->alterColumn('invoiceItem', 'type', 'int');

		$this->alterColumn('mailAttachment', 'prettyName', 'varchar(255)');

		$this->alterColumn('mailItem', 'fromEmail', 'varchar(100)');
		$this->alterColumn('mailItem', 'fromName', 'varchar(100)');


		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('blogItem', 'ranking', 'int not null default "0"');

		$this->addColumn('event', 'requireComment', 'int not null default 0');

		$this->alterColumn('event', 'checks', 'int not null default "0"');
		$this->alterColumn('event', 'door', 'int not null default "0"');
		$this->alterColumn('event', 'maxReservations', 'int not null default "4"');
		$this->alterColumn('event', 'membersOnly', 'int not null default "1"');
		$this->alterColumn('event', 'paypal', 'int not null default "1"');

		$this->alterColumn('invoice', 'paidByCheck', 'int not null default "0"');
		$this->alterColumn('invoice', 'paypaltx', 'varchar(50) not null default ""');

		$this->alterColumn('invoiceItem', 'type', 'int not null default "0"');

		$this->alterColumn('mailAttachment', 'prettyName', 'varchar(255) default ""');

		$this->alterColumn('mailItem', 'fromEmail', 'varchar(255) default ""');
		$this->alterColumn('mailItem', 'fromName', 'varchar(255) default ""');

		$this->alterColumn('mailPiece', 'email', 'varchar(255) not null');

		$this->alterColumn('publicPage', 'hidden', "int DEFAULT '0'");
		$this->alterColumn('publicPage', 'sequence', "int DEFAULT '0'");
		$this->alterColumn('publicPage', 'header', "int DEFAULT '0'");
		$this->alterColumn('publicPage', 'blog', "int DEFAULT '0'");
		$this->alterColumn('publicPage', 'banner', "int DEFAULT '0'");

		$this->alterColumn('volunteerPoll', 'jobEventId', 'int not null');

		$this->alterColumn('volunteerPollAnswer', 'volunteerPollId', 'int not null');
		$this->alterColumn('volunteerPollAnswer', 'answer', "varchar(100) default ''");

		return true;
		}
	}
