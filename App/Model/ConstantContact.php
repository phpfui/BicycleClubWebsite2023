<?php

namespace App\Model;

class ConstantContact extends \PHPFUI\ConstantContact\Client
	{
	private bool $authorized = false;

	public function __construct()
		{
		$settingTable = new \App\Table\Setting();

		$apiKey = $settingTable->value('ConstantContactAPIKey');
		$secret = $settingTable->value('ConstantContactSecret');

		if ($apiKey && $secret)
			{
			parent::__construct($apiKey, $secret, $settingTable->value('homePage') . '/System/Settings/constantContact/token');
			$this->accessToken = $settingTable->value('ConstantContactToken');
			$this->refreshToken = $settingTable->value('ConstantContactRefreshToken');

			try
				{
				$this->refreshToken();

				if (! $this->getLastError())
					{
					$settingTable->save('ConstantContactToken', $this->accessToken);
					$settingTable->save('ConstantContactRefreshToken', $this->refreshToken);
					$this->authorized = true;
					}
				}
			catch (\Throwable $e)
				{
				\App\Tools\Logger::get()->debug($e);
				}
			}
		}

	public function isAuthorized() : bool
		{
		return $this->authorized;
		}
	}
