<?php

namespace App\Migration;

class Migration_77 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Convert General Admission includeMembership to boolean fields';
		}

	public function down() : bool
		{
		$this->addColumn('gaEvent', 'includeMembership', 'int not null default 0');
		$this->executeAlters();
		$this->runSQL('update gaEvent set includeMembership=2 where extendMembership=1');
		$this->runSQL('update gaEvent set includeMembership=1 where newMembersOnly=1');
		$this->runSQL('update gaEvent set includeMembership=3 where renewMembership=1');
		$this->dropColumn('gaEvent', 'extendMembership');
		$this->dropColumn('gaEvent', 'newMembersOnly');
		$this->dropColumn('gaEvent', 'renewMembership');

		return true;
		}

	public function up() : bool
		{
		$this->addColumn('gaEvent', 'extendMembership', 'int not null default 0');
		$this->addColumn('gaEvent', 'newMembersOnly', 'int not null default 0');
		$this->addColumn('gaEvent', 'renewMembership', 'int not null default 0');
		$this->executeAlters();
		$this->runSQL('update gaEvent set extendMembership=1 where includeMembership=2');
		$this->runSQL('update gaEvent set newMembersOnly=1 where includeMembership=1');
		$this->runSQL('update gaEvent set renewMembership=1 where includeMembership=3');
		$this->dropColumn('gaEvent', 'includeMembership');

		return true;
		}
	}
