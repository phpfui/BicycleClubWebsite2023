<?php

namespace App\UI;

class ErrorCallout extends \PHPFUI\Callout
	{
	private \PHPFUI\UnorderedList $ul;

	public function __construct(array $errors = [])
		{
		parent::__construct('alert');
		$this->ul = new \PHPFUI\UnorderedList();
		$this->addValidationErrors($errors);
		$this->add($this->ul);
		}

	public function addValidationErrors(array $validationErrors) : static
		{
		foreach ($validationErrors as $field => $errors)
			{
			foreach ($errors as $error)
				{
				$this->addError("Field <b>{$field}</b>: {$error}");
				}
			}

		return $this;
		}

	public function addError(string $error) : static
		{
		$this->ul->addItem(new \PHPFUI\ListItem($error));

		return $this;
		}
	}
