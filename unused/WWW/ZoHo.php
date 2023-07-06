<?php

namespace App\WWW;

class ZoHo extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private \Asad\OAuth2\Client\Provider\Zoho $provider;

	private \App\Table\Setting $settingTable;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);

		$this->settingTable = new \App\Table\Setting();
		$zoho = new \App\Settings\ZoHo();

		$this->provider = new \Asad\OAuth2\Client\Provider\Zoho([
			'clientId' => $zoho->clientId,    // The client ID assigned to you by the provider
			'clientSecret' => $zoho->clientSecret,    // The client password assigned to you by the provider
			'redirectUri' => $zoho->redirectUri]);
		}

	public function getToken() : void
		{
		if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state']))
			{
			unset($_SESSION['oauth2state']);
			$this->page->addPageContent(new \PHPFUI\SubHeader('Invalid state'));

			return;
			}

		// Try to get an access token (using the authorization code grant)
		$token = $this->provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
		$this->saveToken($token);
		}

	public function refreshToken() : void
		{
		$grant = new \League\OAuth2\Client\Grant\RefreshToken();
		$refreshToken = $this->settingTable->value('zohoRefreshToken');
		$token = $this->provider->getAccessToken($grant, ['refresh_token' => $refreshToken]);
		$this->page->addPageContent(new \PHPFUI\Debug($token));
		$this->saveToken($token);
		}

	public function requestToken() : void
		{
		// If we don't have an authorization code then get one
		$authUrl = $this->provider->getAuthorizationUrl([
			'scope' => [
				'ZohoCreator.report.READ', //Important: Define your data accessability scope here
				// ZohoCreator.meta.application.READ
			],
			'access_type' => 'offline', //Important: If you want to generate the refresh token, set this value as offline
			'prompt' => 'consent',
		]);

		$_SESSION['oauth2state'] = $this->provider->getState();
		\header('Location: ' . $authUrl);

		exit;
		}

	public function status() : void
		{
		$this->page->addPageContent(new \PHPFUI\SubHeader('Token'));
		$this->page->addPageContent($this->settingTable->value('zohoToken'));
		$this->page->addPageContent(new \PHPFUI\SubHeader('Refresh Token'));
		$this->page->addPageContent($this->settingTable->value('zohoRefreshToken'));
	}

	private function saveToken(\League\OAuth2\Client\Token\AccessTokenInterface $token) : void
		{
		$this->page->addPageContent(new \PHPFUI\Debug($_GET));

		if ($token->getToken())
			{
			$this->settingTable->save('zohoToken', $token->getToken());
			$this->page->addPageContent(new \PHPFUI\SubHeader('Token'));
			$this->page->addPageContent($token->getToken());
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Token is null'));
			}

		if ($token->getRefreshToken())
			{
			$this->settingTable->save('zohoRefreshToken', $token->getRefreshToken());
			$this->page->addPageContent(new \PHPFUI\SubHeader('Refresh Token'));
			$this->page->addPageContent($token->getRefreshToken());
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Refresh Token is null'));
			}
		}
	}
