<?php

namespace App\Model\API\Repository;

class AccessToken implements \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface
	{
	/**
	 * {@inheritDoc}
	 */
	public function persistNewAccessToken(\League\OAuth2\Server\Entities\AccessTokenEntityInterface $accessToken) : void
		{
		// Some logic here to save the access token to a database
		$oauthToken = new \App\Record\OauthToken();

		$oauthToken->client = $accessToken->getClient()->getName();
		$oauthToken->tokenExpires = $accessToken->getExpiryDateTime()->format('Y-m-d H:i:s');
		$oauthToken->token = $accessToken->getIdentifier();
		$oauthToken->scopes = \json_encode($accessToken->getScopes(), JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
		$oauthToken->oauthUserId = $accessToken->getUserIdentifier();
		$oauthToken->insert();
		}

	/**
	 * {@inheritDoc}
	 */
	public function revokeAccessToken($tokenId) : void
		{
		// Some logic here to revoke the access token
		$oauthToken = new \App\Record\OauthToken();
		$oauthToken->read(['token' => $tokenId]);

		if ($oauthToken->loaded())
			{
			$oauthToken->delete();
			}
		}

	/**
	 * {@inheritDoc}
	 */
	public function isAccessTokenRevoked($tokenId) : bool
		{
		$oauthToken = new \App\Record\OauthToken();
		$oauthToken->read(['token' => $tokenId]);

		return ! $oauthToken->loaded();
		}

	/**
	 * {@inheritDoc}
	 */
	public function getNewToken(\League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
		{
		$accessToken = new \App\Model\API\Entity\AccessToken();
		$accessToken->setClient($clientEntity);

		foreach ($scopes as $scope)
			{
			$accessToken->addScope($scope);
			}
		$accessToken->setUserIdentifier($userIdentifier);

		return $accessToken;
		}
	}
