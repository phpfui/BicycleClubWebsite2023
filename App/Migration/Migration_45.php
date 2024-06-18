<?php

namespace App\Migration;

class Migration_45 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Enums can not be null';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('cartItem', 'type', 'int not null default 0');
		$this->alterColumn('discountCode', 'type', 'int not null default 0');
		$this->alterColumn('forumMember', 'emailType', 'int not null default 0');
		$this->alterColumn('gaEvent', 'includeMembership', 'int not null default 0');
		$this->alterColumn('invoiceItem', 'type', 'int not null default 0');
		$this->alterColumn('publicPage', 'hidden', 'int not null default 0');
		$this->alterColumn('ride', 'commentsDisabled', 'int not null default 0');

		$this->runSQL('update cartItem set type = 0 where type is null');
		$this->runSQL('update discountCode set type = 0 where type is null');
		$this->runSQL('update forumMember set emailType = 0 where emailType is null');
		$this->runSQL('update gaEvent set includeMembership = 0 where includeMembership is null');
		$this->runSQL('update invoiceItem set type = 0 where type is null');
		$this->runSQL('update publicPage set hidden = 0 where hidden is null');
		$this->runSQL('update ride set commentsDisabled = 0 where commentsDisabled is null');

		return true;
		}
	}
