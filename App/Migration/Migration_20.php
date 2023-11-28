<?php

namespace App\Migration;

class Migration_20 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add headerContent table';
		}

	public function down() : bool
		{
		return $this->dropTable('headerContent');
		}

	public function up() : bool
		{
		return $this->runSQL('create table headerContent (
			headerContentId int not null primary key auto_increment,
			urlPath varchar(255),
			content mediumtext,
			css text,
			javaScript text,
			startDate date,
			endDate date,
			active int not null default 0,
			showMonth int,
			showDay int
			);');
		}
	}
