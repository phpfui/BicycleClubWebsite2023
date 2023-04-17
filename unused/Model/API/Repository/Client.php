<?php

namespace App\Model\API\Repository;

class Client implements \League\OAuth2\Server\Repositories\ClientRepositoryInterface
	{
	/**
	 * {@inheritDoc}
	 */
	public function getClientEntity($clientIdentifier)
		{
		$client = new \App\Model\API\Entity\Client();

		$client->setIdentifier($clientIdentifier);
		$client->setName('Unused');
		$client->setRedirectUri('/');
		$client->setConfidential();

		return $client;
		}

	/**
	 * {@inheritDoc}
	 */
	public function validateClient($clientIdentifier, $clientSecret, $grantType)
		{
		return true;
		}
	}
