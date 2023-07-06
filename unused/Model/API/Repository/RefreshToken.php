<?php

namespace App\Model\API\Repository;

class RefreshToken implements \League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface
	{
	/**
	 * {@inheritDoc}
	 */
	public function getNewRefreshToken()
		{
		\App\Tools\Logger::get()->debug(__METHOD__);

		return new \App\Model\API\Entity\RefreshToken();
		}

	/**
	 * {@inheritDoc}
	 */
	public function isRefreshTokenRevoked($tokenId) : bool
		{
		\App\Tools\Logger::get()->debug(__METHOD__);
		$oauthToken = new \App\Record\OauthToken();
		$oauthToken->read(['token' => $tokenId]);

		return ! $oauthToken->loaded();
		}

	/**
	 * {@inheritDoc}
	 */
	public function persistNewRefreshToken(\League\OAuth2\Server\Entities\RefreshTokenEntityInterface $refreshToken) : void
		{
		\App\Tools\Logger::get()->debug(__METHOD__);
		// Some logic to persist the refresh token in a database
		$oauthToken = new \App\Record\OauthToken();

		$oauthToken->tokenExpires = $refreshToken->getExpiryDateTime()->format('Y-m-d H:i:s');
		$oauthToken->token = $refreshToken->getIdentifier();
		$oauthToken->insert();
		}

	/**
	 * {@inheritDoc}
	 */
	public function revokeRefreshToken($tokenId) : void
		{
		\App\Tools\Logger::get()->debug(__METHOD__);
		// Some logic to revoke the refresh token in a database
		$oauthToken = new \App\Record\OauthToken();
		$oauthToken->read(['token' => $tokenId]);

		if ($oauthToken->loaded())
			{
			$oauthToken->delete();
			}
		}
	}
