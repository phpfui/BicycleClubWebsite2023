<?php

namespace App\Migration;

class Migration_60 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add BlogItem properties';
		}

	public function down() : bool
		{
		$this->dropColumn('blogItem', 'membersOnly');
		$this->dropColumn('blogItem', 'noTitle');
		$this->dropColumn('blogItem', 'onTop');
		$this->dropColumn('blogItem', 'showFull');

		return true;
		}

	public function up() : bool
		{
		$this->runSQL('delete from blogItem where storyId not in (select storyId from story);');
		$this->runSQL('delete from blogItem where blogId not in (select blogId from blog);');
		$this->runSQL('update file set uploaded = CURRENT_TIMESTAMP where uploaded is null;');
		$this->runSQL('update journalItem set timeSent = CURRENT_TIMESTAMP where timeSent is null;');

		$this->addColumn('blogItem', 'membersOnly', 'int');
		$this->addColumn('blogItem', 'noTitle', 'int');
		$this->addColumn('blogItem', 'onTop', 'int');
		$this->addColumn('blogItem', 'showFull', 'int');
		$this->executeAlters();

		$this->runSQL('update blogItem bi left join story s on s.storyId=bi.storyId set bi.membersOnly=s.membersOnly, bi.noTitle=s.noTitle, bi.onTop=s.onTop, bi.showFull=s.showFull where bi.storyId=s.storyId');

		return true;
		}
	}
