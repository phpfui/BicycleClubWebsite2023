<?php

namespace App\Record;

/**
 * @property \PHPFUI\ORM\DataObjectCursor $optionsSelected
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaRiderSelection> $GaRiderSelectionChildren
 */
class GaRider extends \App\Record\Definition\GaRider
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'optionsSelected' => [\App\DB\GARiderOptions::class],
		'GaRiderSelectionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaRiderSelection::class],
	];

	public function clean() : static
		{
		$this->cleanEmail('email');
		$this->email = \App\Model\Member::cleanEmail($this->email);
		$this->cleanProperName('lastName');
		$this->cleanProperName('firstName');
		$this->cleanProperName('address');
		$this->cleanProperName('town');
		$this->cleanUpperCase('state');
		$this->cleanProperName('contact');
		$this->cleanPhone('contactPhone');
		$this->cleanPhone('zip', '\\-');

		return $this;
		}
	}
