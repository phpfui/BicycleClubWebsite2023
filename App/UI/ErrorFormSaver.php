<?php

namespace App\UI;

class ErrorFormSaver extends \App\UI\ErrorForm
	{
	public function __construct(\PHPFUI\Interfaces\Page $page, private \PHPFUI\ORM\Record $record, ?\PHPFUI\Submit $submit = null)
		{
		parent::__construct($page, $submit);
		}

	public function save() : bool
		{
		if (! $this->isMyCallback())
			{
			return false;
			}

		$post = $_POST;

		foreach ($this->record->getPrimaryKeys() as $key)
			{
			unset($post[$key]);
			}

		$this->record->setFrom($post);
		$errors = $this->record->validate();

		if ($errors)
			{
			$this->page->setRawResponse($this->returnErrors($errors));

			return false;
			}
		$this->record->update();
		$this->page->setResponse('Saved');

		return true;
		}
}
