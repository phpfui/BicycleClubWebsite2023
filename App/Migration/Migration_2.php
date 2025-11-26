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
		$this->alterColumn('story', 'editorId', 'int default null', );
		$this->renameColumn('story', 'editorId', 'memberIdEditor');
		$this->alterColumn('pointHistory', 'editorId', 'int default null');
		$this->renameColumn('pointHistory', 'editorId', 'memberIdEditor');
		$this->alterColumn('forumMessage', 'lastEditorId', 'int default null');

		return $this->renameColumn('forumMessage', 'lastEditorId', 'lastEditor');
		}

	public function up() : bool
		{
		$this->alterColumn('story', 'memberIdEditor', 'int default null');
		$this->renameColumn('story', 'memberIdEditor', 'editorId');
		$this->alterColumn('pointHistory', 'memberIdEditor', 'int default null');
		$this->renameColumn('pointHistory', 'memberIdEditor', 'editorId');
		$this->alterColumn('forumMessage', 'lastEditor', 'int default null');

		return $this->renameColumn('forumMessage', 'lastEditor', 'lastEditorId');
		}
	}
