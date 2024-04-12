<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaRider> $GaRiderChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaPriceDate> $GaPriceDateChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaSelection> $GaSelectionChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaOption> $GaOptionChildren
 * @property \App\Enum\GeneralAdmission\IncludeMembership $includeMembership
 */
class GaEvent extends \App\Record\Definition\GaEvent
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'GaOptionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaOption::class, 'ordering'],
		'GaPriceDateChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaPriceDate::class, 'date'],
		'GaRiderChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaRider::class],
		'GaSelectionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaSelection::class, 'ordering'],
		'includeMembership' => [\PHPFUI\ORM\Enum::class, \App\Enum\GeneralAdmission\IncludeMembership::class],
	];

	public function clean() : static
		{
		$this->description = \App\Tools\TextHelper::cleanUserHtml($this->description);
		$this->signupMessage = \App\Tools\TextHelper::cleanUserHtml($this->signupMessage);
		$this->cleanProperName('incentiveName');
		$this->cleanProperName('location');
		$this->cleanProperName('registrar');
		$this->cleanProperName('title');
		$this->signupMessage = \App\Tools\TextHelper::cleanUserHtml($this->signupMessage);

		return $this;
		}
	}
