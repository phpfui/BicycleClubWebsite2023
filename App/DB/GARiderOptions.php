<?php

namespace App\DB;

class GARiderOptions extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<mixed> $parameters
	 */
	public function getValue(array $parameters) : \PHPFUI\ORM\DataObjectCursor
		{
		$table = new \App\Table\GaRiderSelection();
		// @phpstan-ignore-next-line
		$condition = new \PHPFUI\ORM\Condition('GaRiderId', $this->currentRecord->gaRiderId);
		$table->setWhere($condition);
		$table->addJoin('gaOption');
		$table->addJoin('gaSelection');

		return $table->getDataObjectCursor();
		}
	}
