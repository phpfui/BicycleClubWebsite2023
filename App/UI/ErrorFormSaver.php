<?php

namespace App\UI;

class ErrorFormSaver extends \App\UI\ErrorForm
	{
	/**
	 * @var ?array<string> $callback
	 */
	private ?array $callback = null;

	public function __construct(\PHPFUI\Interfaces\Page $page, private \PHPFUI\ORM\Record $record, ?\PHPFUI\Submit $submit = null)
		{
		parent::__construct($page, $submit);
		}

	public function save(string $redirectOnSuccess = '') : bool
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

		if ($this->callback)
			{
			$response = \call_user_func($this->callback, $this->record);
			}
		else
			{
			$this->record->insertOrUpdate();
			$response = ['response' => 'Saved', 'color' => 'lime', 'record' => $this->record->toArray(), ];

			if ($redirectOnSuccess)
				{
				$response['redirect'] = $redirectOnSuccess;
				}
			}
		$this->page->setRawResponse(\json_encode($response));

		return true;
		}

	/**
	 * callback takes a \PHPFUI\ORM\Record parameter and returns an array of responses
	 *
	 * example: return ['response' => 'Saved', 'color' => 'lime', 'record' => $record->toArray(), 'redirect' => $redirect];
	 *
	 * @param array<string> $callback
	 */
	public function setSaveRecordCallback(array $callback) : static
		{
		$this->callback = $callback;

		return $this;
		}
	}
