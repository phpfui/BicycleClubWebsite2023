<?php

namespace App\Cron\Job;

class ZoHo extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'ZoHo Report Download.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$settingTable = new \App\Table\Setting();

		$refreshToken = $settingTable->value('zohoRefreshToken');

		if (! $refreshToken)
			{
			echo "No Refresh Token\n";

			return;
			}

//		$tokens = \file_get_contents('https://scbc.edmundjryan.com/ZoHo/refreshToken');

		$filePath = PROJECT_ROOT . '/conversions/soundCyclistsCT/zoho/files_for_ridelibrary/';
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
		$files = [];

		foreach($iterator as $path)
			{
			if ($path->isFile())
				{
				$files[$path->getFilename()] = true;
				}
			}
		\print_r($files);
		echo "\n\n";

//		$fileName = PROJECT_ROOT . '/conversions/soundCyclistsCT/zoho/tables/Ride Library.csv';
//		$reader = new \App\Tools\CSVReader($fileName);

		$token = $settingTable->value('zohoToken');
		$recordId = '218933000012623003';
		$uri = 'https://creator.zoho.com/api/v2/soundcyclists/scbc-ride-list/report/Ride_Library/' . $recordId;
		\print_r($uri);
		echo "\n\n";

		$headers = [
			'Cache-Control' => 'no-cache',
			'Authorization' => 'Zoho-oauthtoken ' . $token,
			'Content-Type' => 'application/json',
			'Accept' => 'application/json', ];

		echo "Headers\n";
		\print_r($headers);
		echo "\n\n";
		$guzzle = new \GuzzleHttp\Client(['headers' => $headers, ]); // 'handler' => $this->guzzleHandler, ]);

		try
			{
			$response = $guzzle->request('GET', $uri);
			echo "Response\n";

			\print_r($response);
			}
		catch (\Throwable $e)
			{
			echo "ERROR\n";
			echo $e->getMessage() . "\n";
			}
		}

	public function willRun() : bool
		{
		return false;
		}
	}
