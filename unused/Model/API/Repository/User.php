<?php

namespace App\Model\API\Repository;

class User implements \League\OAuth2\Server\Repositories\UserRepositoryInterface
	{
	/**
	 * {@inheritDoc}
	 */
	public function getUserEntityByUserCredentials($username, $password, $grantType, \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity) : ?\League\OAuth2\Server\Entities\UserEntityInterface
		{
		$oauthUser = new \App\Record\OauthUser();

		if ($oauthUser->authenticateUser($username, $password))
		  {
		  return new \App\Model\API\Entity\User($oauthUser);
		  }

		return null;
		}
	}
