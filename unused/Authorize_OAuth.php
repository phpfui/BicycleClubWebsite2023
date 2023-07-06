<?php

namespace App\API;

class Authorize_OAuth extends \App\View\API\Base implements \PHPFUI\Interfaces\NanoClass
	{
	private \Psr\Http\Message\ServerRequestInterface $request;

	private \League\OAuth2\Server\AuthorizationServer $server;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		\App\Tools\Logger::get()->debug($controller->getUri());
		// make a request for later since we have the controller here now
		$this->request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

		// Init our repositories
		$clientRepository = new \App\Model\API\Repository\Client(); // instance of ClientRepositoryInterface
		$scopeRepository = new \App\Model\API\Repository\Scope(); // instance of ScopeRepositoryInterface
		$accessTokenRepository = new \App\Model\API\Repository\AccessToken(); // instance of AccessTokenRepositoryInterface

		$settingTable = new \App\Table\Setting();
		// Setup the authorization server
		$this->server = new \League\OAuth2\Server\AuthorizationServer(
			$clientRepository,
			$accessTokenRepository,
			$scopeRepository,
			\file_get_contents(\App\Model\API::getPrivateKeyPath()),
			\Defuse\Crypto\Key::loadFromAsciiSafeString($settingTable->value('APIEncryptionKey'))
		);
		}

	public function password() : void
		{
		\App\Tools\Logger::get()->debug(__METHOD__);
		\App\Tools\Logger::get()->debug($_GET);
		\App\Tools\Logger::get()->debug($_POST);
		$grant = new \League\OAuth2\Server\Grant\PasswordGrant(
			new \App\Model\API\Repository\User(),           // instance of UserRepositoryInterface
				new \App\Model\API\Repository\RefreshToken()    // instance of RefreshTokenRepositoryInterface
		);
		$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

		// Enable the password grant on the server with a token TTL of 1 hour
		$this->server->enableGrantType($grant, new \DateInterval('PT1H')); // access tokens will expire after 1 hour

		$this->processRequest();
		}

	public function refreshToken() : void
		{
		\App\Tools\Logger::get()->debug(__METHOD__);
		\App\Tools\Logger::get()->debug($_GET);
		\App\Tools\Logger::get()->debug($_POST);
		// Enable the refresh token grant on the server
		$grant = new \League\OAuth2\Server\Grant\RefreshTokenGrant(new \App\Model\API\Repository\RefreshToken());
		$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // The refresh token will expire in 1 month

		$this->server->enableGrantType($grant, new \DateInterval('PT1H')); // The new access token will expire after 1 hour

		$this->processRequest();
		}

	private function processRequest() : void
		{
		try
			{
			// Try to respond to the request
			$this->setResponse($this->server->respondToAccessTokenRequest($this->request, new \GuzzleHttp\Psr7\Response()));
			}
		catch (\League\OAuth2\Server\Exception\OAuthServerException $exception)
			{
			\App\Tools\Logger::get()->debug($exception);
			// All instances of OAuthServerException can be formatted into a HTTP response
			$this->setResponse($exception->generateHttpResponse(new \GuzzleHttp\Psr7\Response()));
			}
		catch (\Exception $exception)
			{
			// Unknown exception
			$this->logError($exception->getMessage(), 500);
			}
		}
	}
