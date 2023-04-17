<?php

namespace App\Model\API\Entity;

class User implements \League\OAuth2\Server\Entities\UserEntityInterface
	{
	public function __construct(private \App\Record\OauthUser $oauthUser)
		{
		}

	/**
	 * Return the user's identifier.
	 *
	 */
	public function getIdentifier()
		{
		return $this->oauthUser->oauthUser->oauthUserId;
		}
	}
