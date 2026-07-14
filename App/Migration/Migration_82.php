<?php

namespace App\Migration;

class Migration_82 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Date stamp leader applications';
		}

	public function down() : bool
		{
		$this->alterColumn('member', 'pendingLeader', 'int default "0"');
		$this->executeAlters();
		$memberTable = new \App\Table\Member();
		$memberTable->setWhere(new \PHPFUI\ORM\Condition('pendingLeader', 0, new \PHPFUI\ORM\Operator\GreaterThan()));
		$memberTable->update(['pendingLeader' => 1]);

		return true;
		}

	public function up() : bool
		{
		$memberTable = new \App\Table\Member();
		$this->alterColumn('member', 'pendingLeader', 'int');
		$this->executeAlters();
		$memberTable->setWhere(new \PHPFUI\ORM\Condition('pendingLeader', 0));
		$memberTable->update(['pendingLeader' => null]);
		$this->alterColumn('member', 'pendingLeader', 'DATE null');
		$this->executeAlters();
		$memberTable->setWhere(new \PHPFUI\ORM\Condition('pendingLeader', '0000-00-00', new \PHPFUI\ORM\Operator\GreaterThanEqual()));
		$memberTable->update(['pendingLeader' => \App\Tools\Date::todayString()]);

		return true;
		}
	}
