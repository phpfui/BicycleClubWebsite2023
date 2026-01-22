<?php

namespace App\Model;

class Settings
	{
	private readonly \App\Table\Setting $settingTable;

	public function __construct()
		{
		$this->settingTable = new \App\Table\Setting();
		}

	/**
	 * @param array<string,string> $toMember
	 */
	public function sendSettingEmail(string $setting, array $toMember, string $subject) : void
		{
		$callback = function(string $key) use ($toMember) : string
			{
			if (isset($toMember[$key]))
				{
				return $toMember[$key];
				}

			return $this->settingTable->value($key);
			};
		$email = new \App\Tools\EMail();
		$email->setFromMember(\App\Model\Session::getSignedInMember());
		$email->setBody(\App\Tools\TextHelper::replace($this->settingTable->value($setting), $callback));
		$email->setHtml();
		$email->setSubject(\App\Tools\TextHelper::replace($subject, $callback));
		$email->addToMember($toMember);
		$email->send();
		}
	}
