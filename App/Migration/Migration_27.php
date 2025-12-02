<?php

namespace App\Migration;

class Migration_27 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Depreciated (Add summary flag to Member Notices)';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		return true;
		}
	}
