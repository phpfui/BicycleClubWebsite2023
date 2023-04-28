<?php

namespace App\Model;

class DeleteRecord
	{
	private $conditionalCallBack = null;

	private readonly \PHPFUI\AJAX $delete;

	private $deleteCallBack = null;

	private readonly string $primaryKey;

	public function __construct(\PHPFUI\Interfaces\Page $page, \PHPFUI\Table $table, \PHPFUI\ORM\Table $dbTable, string $message = 'Are you sure you want to delete this row?')
		{
		$this->primaryKey = \key($dbTable->getPrimaryKeys());
		$functionName = 'delete' . \ucfirst($this->primaryKey);

		if (\PHPFUI\Session::checkCSRF() && ($_POST['action'] ?? '') == $functionName)
			{
			$record = $dbTable->getRecord();
			$record->{$this->primaryKey} = (int)$_POST[$this->primaryKey];

			if ($this->deleteCallBack)
				{
				$record->reload();
				$cb = $this->deleteCallBack;
				$cb($record);
				}
			$record->delete();
			$page->setResponse($_POST[$this->primaryKey]);

			return;
			}

		$table->setRecordId($this->primaryKey);
		$this->delete = new \PHPFUI\AJAX($functionName, $message);
		$this->delete->addFunction('success', '$("#' . $this->primaryKey . '-"+data.response).css("background-color","red").hide("fast")');
		$page->addJavaScript($this->delete->getPageJS());
		}

	public function columnCallback(array $row)
		{
		if ($this->conditional($row))
			{
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $this->delete->execute([$this->primaryKey => $row[$this->primaryKey]]));

			return $icon;
			}

		return '';
		}

	public function setConditionalCallback(callable $callback) : static
		{
		$this->conditionalCallBack = $callback;

		return $this;
		}

	public function setDeleteCallback(callable $callback) : static
		{
		$this->deleteCallBack = $callback;

		return $this;
		}

	private function conditional(array $row) : bool
		{
		$cb = $this->conditionalCallBack;

		return $cb ? $cb($row) : true;
		}
	}
