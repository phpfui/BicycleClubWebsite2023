<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Pace> $PaceChildren
 * @property \App\Record\Member $coordinator
 */
class Category extends \App\Record\Definition\Category
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'PaceChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Pace::class],
		'coordinator' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\Member::class],
	];

	public function label() : string
		{
		$label = $this->category ?? 'All';

		if ((int)($this->minSpeed) && (int)($this->maxSpeed))
			{
			$label .= " ({$this->minSpeed}-{$this->maxSpeed})";
			}
		elseif ($this->minSpeed)
			{
			$label .= " ({$this->minSpeed}+)";
			}
		elseif ($this->maxSpeed)
			{
			$label .= " (<{$this->maxSpeed})";
			}

		return $label;
		}
	}
