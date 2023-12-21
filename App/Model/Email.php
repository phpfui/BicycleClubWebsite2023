<?php

namespace App\Model;

class Email extends \App\Tools\EMail
	{
	public function __construct(string $type, \App\DB\Interface\EmailData $data)
		{
		parent::__construct();
		$settingTable = new \App\Table\Setting();
		$this->setBody(\App\Tools\TextHelper::processText($settingTable->value($type), $data->toArray()));
		$this->setSubject($settingTable->value($type . 'Title'));
		$this->setHtml();
		}
	}
