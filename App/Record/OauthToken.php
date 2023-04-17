<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class OauthToken extends \App\Record\Definition\OauthToken
	{
	public function generate(\App\Record\OauthUser $user, int $expiresAtUnix) : int
		{
		$this->client = $_SERVER['HTTP_USER_AGENT'];
		$this->token = \bin2hex(\random_bytes(126));
		$this->oauthUserId = $user->oauthUserId;
		$this->expires = \date('Y-m-d H:i:s', $expiresAtUnix);
		$this->scopes = $user->permissions;

		return $this->insert();
		}

	public function getPermissions() : array
		{
		return \json_decode($this->scopes ?: '[]', true);
		}
	}
