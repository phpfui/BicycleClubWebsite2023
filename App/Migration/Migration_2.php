<?php

namespace App\Migration;

class Migration_2 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Normalize member ids';
		}

	public function down() : bool
		{
		$this->alterColumn('story', 'editorId', 'int default null', 'memberIdEditor');
		$this->alterColumn('pointHistory', 'editorId', 'int default null', 'memberIdEditor');

		return $this->alterColumn('forumMessage', 'lastEditorId', 'int default null', 'lastEditor');
		}

	public function up() : bool
		{
		$this->alterColumn('story', 'memberIdEditor', 'int default null', 'editorId');
		$this->alterColumn('pointHistory', 'memberIdEditor', 'int default null', 'editorId');

		return $this->alterColumn('forumMessage', 'lastEditor', 'int default null', 'lastEditorId');
		}
	}
