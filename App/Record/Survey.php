<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\SurveyQuestion> $QuestionChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\SurveyCrossTab> $CrossTabChildren
 */
class Survey extends \App\Record\Definition\Survey
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'QuestionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\SurveyQuestion::class],
		'CrossTabChildren' => [\PHPFUI\ORM\Children::class, \App\Table\SurveyCrossTab::class, 'ordering'],
	];

	public function delete() : bool
		{
		if ($this->surveyId)
			{
			$fileModel = new \App\Model\SurveyFile();
			$fileModel->delete($this->surveyId);
			}

		return parent::delete();
		}
	}
