<?php

namespace App\Migration;

class Migration_38 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add waiver text to General Admission events';
		}

	public function down() : bool
		{
		$this->dropColumn('gaRider', 'agreedToWaiver');

		return $this->dropColumn('gaEvent', 'waiver');
		}

	public function up() : bool
		{
		$this->addColumn('gaRider', 'agreedToWaiver', 'tinyint');

		return $this->addColumn('gaEvent', 'waiver', 'mediumtext');
		}
	}
