<?php

namespace App\Model;

class EditIcon
	{
	private readonly string $primaryKey;

	public function __construct(\App\UI\ContinuousScrollTable $table, \PHPFUI\ORM\Table $dbTable, private string $url)
		{
		$this->url = \rtrim($this->url, '/');
		$this->primaryKey = $dbTable->getPrimaryKeys()[0];
		$table->addCustomColumn('edit', $this->columnCallback(...));
		}

	private function columnCallback(array $row)
		{
		return new \PHPFUI\FAIcon('far', 'edit', $this->url . '/' . $row[$this->primaryKey]);
		}
	}
