<?php

namespace PHPFUI;

class DefaultSessionHandler implements \PHPFUI\SessionHandler
	{
	private string $csrfValue = '';

	public function checkCSRF(string $request = '') : bool
		{
		if (empty($request) && isset($_REQUEST[$this->csrfField()]))
			{
			$request = $_REQUEST[$this->csrfField()];
			}

		return ! empty($request) && ! empty($_SESSION[$this->csrfField()]) && $request == $_SESSION[$this->csrfField()];
		}

	public function csrf(string $quote = '') : string
		{
		if (! isset($_SESSION[$this->csrfField()]))
			{
			if (empty($this->csrfValue))
				{
				$this->csrfValue = \sha1((string)\random_int(0, \mt_getrandmax()));
				}
			$_SESSION[$this->csrfField()] = $this->csrfValue;
			}

		return $quote . $_SESSION[$this->csrfField()] . $quote;
		}

	public function csrfField() : string
		{
		return 'csrf';
		}
	}
