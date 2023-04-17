<?php

namespace App\API\V1;

class Authorize extends \App\View\API\Base implements \PHPFUI\Interfaces\NanoClass
	{
	public function passwordPost() : void
		{
		$userName = $_POST['userName'] ?? '';
		$password = $_POST['password'] ?? '';

		if (! $userName)
			{
			$this->logError('Missing userName', 400);

			return;
			}

		if (! $password)
			{
			$this->logError('Missing password', 400);

			return;
			}

		$user = new \App\Record\OauthUser();

		if (! $user->authenticateUser($userName, $password))
			{
			$this->logError('Not Authorized', 401);

			return;
			}

		$token = new \App\Record\OauthToken();
		$token->generate($user, \time() + 3600);
		$this->log($token->token, 'bearer_token');

		$refreshToken = new \App\Record\OauthToken();
		$refreshToken->generate($user, \time() + 3600 * 24 * 30);
		$this->log($refreshToken->token, 'refresh_token');
		}

	public function refreshTokenPost() : void
		{
		$refreshTokenId = $_POST['refresh_token'] ?? '';

		if (! $refreshTokenId)
			{
			$this->logError('Token Not Found', 404);

			return;
			}
		$refreshToken = new \App\Record\OauthToken();
		$refreshToken->read(['token' => $refreshTokenId]);

		if (! $refreshToken->loaded())
			{
			$this->logError('Token Not Found', 404);

			return;
			}

		if ($refreshToken->expires < \date('Y-m-d H:i:s'))
			{
			$this->logError('Token Expired', 401);

			return;
			}

		$token = new \App\Record\OauthToken();
		$token->generate($refreshToken->oauthUser, \time() + 3600);
		$token->scopes = $refreshToken->scopes;
		$this->log($token->token, 'bearer_token');

		$newRefreshToken = new \App\Record\OauthToken();
		$newRefreshToken->generate($refreshToken->oauthUser, \time() + 3600 * 24 * 30);
		$newRefreshToken->scopes = $token->scopes;
		$this->log($newRefreshToken->token, 'refresh_token');

		$refreshToken->delete();
		}
	}
