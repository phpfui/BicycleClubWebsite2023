<?php

namespace App\Migration;

class Migration_78 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add Survey tables';
		}

	public function down() : bool
		{
		$this->dropTable('surveyCrossTab');
		$this->dropTable('survey');
		$this->dropTable('surveyQuestion');

		return true;
		}

	public function up() : bool
		{
		$this->down();

		$memberTable = new \App\Table\Member();
		$memberTable->setWhere(new \PHPFUI\ORM\Condition('email', '%+%@gmail.com', new \PHPFUI\ORM\Operator\Like()));

		foreach ($memberTable->getRecordCursor() as $member)
			{
			$member->update();	// will automagically clean the email address with + gmail addresses and save them without the plus
			}

		$this->runSQL('
			CREATE TABLE `surveyCrossTab` (
				`surveyCrossTabId` int NOT NULL AUTO_INCREMENT,
				`surveyId` int not null,
				`rowSurveyQuestionId` int not null,
				`rowName` varchar(255),
				`columnSurveyQuestionId` int,
				`columnName` varchar(255),
				`percent` int,
				`ordering` int,
				`name` varchar(255),
				`description` text,
				PRIMARY KEY (`surveyCrossTabId`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

			CREATE TABLE `survey` (
				`surveyId` int NOT NULL AUTO_INCREMENT,
				`name` varchar(255) not null,
				`uploaded` date,
				`description` text,
				PRIMARY KEY (`surveyId`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

			CREATE TABLE `surveyQuestion` (
				`surveyQuestionId` int NOT NULL AUTO_INCREMENT,
				`surveyId` int NOT NULL,
				`columnName` varchar(255) not null,
				`separator` char(1),
				`displayName` varchar(255),
				PRIMARY KEY (`surveyQuestionId`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');

		return true;
		}
	}
