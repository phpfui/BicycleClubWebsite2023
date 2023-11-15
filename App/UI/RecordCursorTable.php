<?php

namespace App\UI;

class RecordCursorTable extends \PHPFUI\Table
	{
	/**
	 * @var array<string, array<mixed>>
	 */
	private array $customColumns = [];

	public function __construct(private readonly \PHPFUI\ORM\RecordCursor $cursor)
		{
		parent::__construct();
		}

	/**
	 * @param array<string,string> $additionalData
	 */
	public function addCustomColumn(string $field, callable $callback, array $additionalData = []) : static
		{
		$this->customColumns[$field] = [$callback, $additionalData];

		return $this;
		}

	protected function getStart() : string
		{
		foreach ($this->cursor as $record)
			{
			$displayRow = $record->toArray();

			foreach ($this->customColumns as $field => $callbackInfo)
				{
				$displayRow[$field] = $callbackInfo[0]($displayRow, $callbackInfo[1]);
				}
			$this->addRow($displayRow);
			}

		return parent::getStart();
		}
	}
