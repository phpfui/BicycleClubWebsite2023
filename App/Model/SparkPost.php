<?php

namespace App\Model;

class SparkPost
	{
	private ?\SparkPost\SparkPost $sparkPost = null;

	public function __construct()
		{
		$settingTable = new \App\Table\Setting();
		$apiKey = $settingTable->value('SparkPostAPIKey');

		if ($apiKey)
			{
			$httpClient = new \Http\Adapter\Guzzle7\Client(new \GuzzleHttp\Client());
			$this->sparkPost = new \SparkPost\SparkPost($httpClient, ['key' => $apiKey, 'async' => false, ]);
			}
		}

	public function active() : bool
		{
		return null !== $this->sparkPost;
		}

	/**
	 * @param array<string> $emailAddresses
	 *
	 * @return array<string,int>
	 */
	public function deleteSuppressions(array $emailAddresses) : array
		{
		$results = [];

		if (! $this->active())
			{
			return $results;
			}

		foreach ($emailAddresses as $email)
			{
			$parameters = [];
			$parameters['recipient'] = $email;

			try
				{
				$response = $this->sparkPost->request('DELETE', 'suppression-list', $parameters);
				$results[$email] = $response->getStatusCode();
				}
			catch (\Exception $e)
				{
				$results[$email] = $e->getCode();
				}
			}

		return $results;
		}

	/**
	 * @return array<array<string,string>>
	 */
	public function getSuppressionList() : array
		{
		$results = [];

		if (! $this->active())
			{
			return $results;
			}

		try
			{
			$parameters = [];
			$parameters['from'] = \App\Tools\Date::todayString(-400) . 'T00:00:00Z';

			$response = $this->sparkPost->request('GET', 'suppression-list', $parameters);

			if (200 == $response->getStatusCode())
				{
				$results = $response->getBody()['results'];
				}
			}
		catch (\Exception $e)
			{
			\App\Tools\Logger::get()->debug($e);
			}

		return $results;
		}
	}
