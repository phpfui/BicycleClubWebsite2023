<?php

namespace App\UI;

class AssistantLeaderTypeSelect extends \PHPFUI\Input\Select
	{
	public function __construct(string $name = 'assistantLeaderTypeId', int $value = 0, string $label = 'Assistant Leader Type')
		{
		parent::__construct($name, $label);

		$assistantLeaderTypeTable = new \App\Table\AssistantLeaderType();
		$assistantLeaderTypeTable->addOrderBy('name');

		foreach ($assistantLeaderTypeTable->getRecordCursor() as $type)
			{
			$this->addOption($type->name, $type->assistantLeaderTypeId, $type->assistantLeaderTypeId == $value);
			}
		}
	}
