<?php

namespace App\View;

class Strava
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function editSettings(string $authorize) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$settingsSaver = new \App\Model\SettingsSaver();
		$form = new \PHPFUI\Form($this->page, $submit);
		$settingTable = new \App\Table\Setting();

		$clientId = $settingTable->value('StravaClientId');
		$secret = $settingTable->value('StravaSecret');
		$token = $settingTable->value('StravaToken');
		$refreshToken = $settingTable->value('StravaRefreshToken');
		$client = null;
		$error = 0 == \strlen($token);
		$errorString = '';

		if ($clientId && $secret)
			{
			$client = new \PHPFUI\Strava\Client($clientId, $secret, $settingTable->value('homePage') . '/Strava/settings/token');
			$client->setScopes(['read', 'activity:read']);
			$client->accessToken = $token;
			$client->refreshToken = $refreshToken;
			}

		if ('deauth' == $authorize)
			{
			if ($client)
				{
				$client->deauthorize();
				}
			$settingTable->save('StravaToken', '');
			$settingTable->save('StravaRefreshToken', '');
			$this->page->redirect('/Strava/settings');

			return $form;
			}
		elseif ($client)
			{
			if ('token' == $authorize)
				{
				$error = ! $client->acquireAccessToken($_GET);

				if ($error)
					{
					\App\Model\Session::setFlash('alert', 'Unknown Error');

					if ($client->getLastError())
						{
						\App\Model\Session::setFlash('alert', $client->getLastError());
						}
					}
				else
					{
					$settingTable->save('StravaToken', $client->accessToken);
					$settingTable->save('StravaRefreshToken', $client->refreshToken);
					}
				$this->page->redirect('/Strava/settings');

				return $form;
				}
			elseif ('auth' == $authorize)
				{
				$this->page->redirect($client->getAuthorizationURL());

				return $form;
				}
			}

		if ($error)
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add('Strava API is not authorized.');
			}
		else
			{
			$callout = new \PHPFUI\Callout('success');
			$callout->add('Strava API is authorized');
			}
		$form->add($callout);

		$fieldSet = new \PHPFUI\FieldSet('Strava Settings');
		$link = new \PHPFUI\Link('https://www.strava.com/settings/api', 'Strava API Application');
		$fieldSet->add("The {$link} allows you to log in to get keys for their API. Here is what needs to be done to set it up:<br><br>");
		$ol = new \PHPFUI\OrderedList();
		$ol->addItem(new \PHPFUI\ListItem("Go here: {$link} It should show <b>My API Application</b>"));
		$ol->addItem(new \PHPFUI\ListItem('Set the following fields:'));
		$homePage = $settingTable->value('homePage');
		$domain = $_SERVER['HTTP_HOST'];
		$ul = new \PHPFUI\UnorderedList();
		$ul->addItem(new \PHPFUI\ListItem('Application Name = <i>Your Club Name Website</i>'));
		$ul->addItem(new \PHPFUI\ListItem('Category = <b>Charity</b>'));
		$ul->addItem(new \PHPFUI\ListItem('Club = <i>Your club name</i>'));
		$ul->addItem(new \PHPFUI\ListItem("Website = <b>{$homePage}</b>"));
		$ul->addItem(new \PHPFUI\ListItem('Application Description = Something like <b>Allow users to tag rides with the Strava route.</b>'));
		$ul->addItem(new \PHPFUI\ListItem("Authorize Callback Domain = <b>{$domain}</b>"));
		$ol->add($ul);
		$ol->addItem(new \PHPFUI\ListItem('Save'));
		$ol->addItem(new \PHPFUI\ListItem('Upload your club logo'));
		$ol->addItem(new \PHPFUI\ListItem('Enter Your Client ID and Secret below'));
		$ol->addItem(new \PHPFUI\ListItem('Hit the Save then Authorize button'));

		$fieldSet->add($ol);

		$versionFieldSet = new \PHPFUI\FieldSet('API Information');

		$fields = [];
		$fields['ClientId'] = 'Client Id (leave blank to turn off)';
		$fields['Secret'] = 'Secret';

		foreach ($fields as $name => $text)
			{
			$versionFieldSet->add($settingsSaver->generateField('Strava' . $name, $text, 'text', false));
			}
		$fieldSet->add($versionFieldSet);
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$settingsSaver->save();
			$this->page->setResponse('Saved');
			}
		else
			{
			if ($error)
				{
				$authorize = new \PHPFUI\Button('Authorize', '/Strava/settings/auth');
				$authorize->addClass('warning');
				$form->add(new \App\UI\CancelButtonGroup($submit, $authorize));
				}
			else
				{
				$deauthorize = new \PHPFUI\Button('Deauthorize', '/Strava/settings/deauth');
				$deauthorize->addClass('alert');
				$form->add(new \App\UI\CancelButtonGroup($submit, $deauthorize));
				}
			}

		return $form;
		}
	}
