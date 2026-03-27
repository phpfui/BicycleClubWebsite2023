<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \App\Record\SurveyQuestion $rowSurveyQuestion
 * @property \App\Record\SurveyQuestion $columnSurveyQuestion
 */
class SurveyCrossTab extends \App\Record\Definition\SurveyCrossTab
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'rowSurveyQuestion' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\SurveyQuestion::class],
		'columnSurveyQuestion' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\SurveyQuestion::class],
	];
	}
