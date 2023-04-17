<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class SystemEmail extends \App\Record\Definition\SystemEmail
	{
	public function clean() : static
		{

		$this->mailbox = \preg_replace('/[^a-z0-9\._\-@!#\$%&\'\*\+=\?\^`\{\|\}~]/', '', \strtolower($this->mailbox ?? ''));
		$this->cleanEmail('email');
		$this->cleanProperName('name');

		return $this;
		}
	}
