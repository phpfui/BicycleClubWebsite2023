<?php

namespace App\Migration;

class Migration_79 extends \PHPFUI\ORM\Migration
	{
	/** @var array<string> */
	private array $fields = ['extendMembership', 'newMembersOnly', 'renewMembership', ];

	public function description() : string
		{
		return 'More GaEvent membership options';
		}

	public function down() : bool
		{
		$this->addColumn('gaEvent', 'membershipExpires', 'date');

		foreach ($this->fields as $field)
			{
			$this->addColumn('gaEvent', $field, 'int NOT NULL DEFAULT 0');
			}
		$this->executeAlters();

		foreach ($this->fields as $field)
			{
			$this->runSQL("update gaEvent set {$field}=1,membershipExpires={$field}Date where {$field}Date is not null");
			$this->dropColumn('gaEvent', $field . 'Date');
			}

		return true;
		}

	public function up() : bool
		{
		$this->runSQL("update gaEvent set membershipExpires=null where membershipExpires='0000-00-00'");

		foreach ($this->fields as $field)
			{
			$this->addColumn('gaEvent', $field . 'Date', 'date');
			}
		$this->executeAlters();

		foreach ($this->fields as $field)
			{
			$this->runSQL("update gaEvent set {$field}Date=membershipExpires where {$field}>=1");
			$this->dropColumn('gaEvent', $field);
			}
		$this->dropColumn('gaEvent', 'membershipExpires');

		return true;
		}
	}
