<?php

namespace App\View\System;

class ConstantContact
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function edit(string $authorize) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$settingsSaver = new \App\Model\SettingsSaver();
		$form = new \PHPFUI\Form($this->page, $submit);
		$settingTable = new \App\Table\Setting();

		$apiKey = $settingTable->value('ConstantContactAPIKey');
		$secret = $settingTable->value('ConstantContactSecret');
		$token = $settingTable->value('ConstantContactToken');
		$refreshToken = $settingTable->value('ConstantContactRefreshToken');
		$client = null;
		$error = 0 == \strlen($token);
		$errorString = '';

		if ($apiKey && $secret)
			{
			$client = new \PHPFUI\ConstantContact\Client($apiKey, $secret, $settingTable->value('homePage') . '/System/Settings/constantContact/token');
			$client->accessToken = $token;
			$client->refreshToken = $refreshToken;
			}

		$name = 'ConstantContactSyncList';
		$currentList = $settingTable->value($name);

		if ('download' == $authorize)
			{
			$contactsAPI = new \PHPFUI\ConstantContact\V3\Contacts($client);
			$response = $contactsAPI->get(lists:$currentList, limit:500);

			if (! $contactsAPI->success())
				{
				\App\Tools\Logger::get()->debug($contactsAPI->getStatusCode(), $contactsAPI->getLastError());
				}

			$csvWriter = new \App\Tools\CSV\FileWriter('ConstantContactAddresses.csv');
			$dups = [];

			do
				{
				foreach ($response['contacts'] ?? [] as $row)
					{
					$contactId = $row['contact_id'];

					// remove dups, in case of API issues
					if (\array_key_exists($contactId, $dups))
						{
						continue;
						}
					$dups[$contactId] = true;
					// flatten address
					$row = \array_merge($row, $row['email_address']);
					$row['email_address'] = $row['email_address']['address'];
					$row = $this->flatten($row, 'phone_numbers');
					$row = $this->flatten($row, 'street_addresses');

					// remove any arrays that may be there
					foreach ($row as $key => $value)
						{
						if (\is_array($value))
							{
							unset($row[$key]);
							}
						}
					$csvWriter->outputRow($row);
					}
				$response = $contactsAPI->next();
				}
			while ($response);
			$this->page->done();

			return $form;
			}
		elseif ('deauth' == $authorize)
			{
			$settingTable->save('ConstantContactToken', '');
			$settingTable->save('ConstantContactRefreshToken', '');
			$this->page->redirect('/System/Settings/constantContact');

			return $form;
			}
		elseif ($client)
			{
			if ('token' === $authorize) // @mago-expect lint:no-insecure-comparison
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
					$settingTable->save('ConstantContactToken', $client->accessToken);
					$settingTable->save('ConstantContactRefreshToken', $client->refreshToken);
					}
				$this->page->redirect('/System/Settings/constantContact');

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
			$callout->add('Constant Contact is not authorized.');
			}
		else
			{
			$callout = new \PHPFUI\Callout('success');
			$callout->add('Constant Contact is authorized');
			}
		$form->add($callout);

		$fieldSet = new \PHPFUI\FieldSet('Constant Contact Settings');
		$link = new \PHPFUI\Link('https://app.constantcontact.com/pages/dma/portal/', 'Constant Contact Developer Portal');
		$fieldSet->add("The {$link} allows you to log in to get keys for their API. Here is what needs to be done to set it up:<br><br>");
		$ol = new \PHPFUI\OrderedList();
		$logIn = new \PHPFUI\Link('https://login.constantcontact.com/login', 'Constant Contact');
		$ol->addItem(new \PHPFUI\ListItem('Sign into ' . $logIn));
		$ol->addItem(new \PHPFUI\ListItem("Then go here: {$link} It should show <b>My Applications</b>"));
		$ol->addItem(new \PHPFUI\ListItem('Hit the yellow New Application button and give it a name. You can call it <b>Membership Website</b> or something similar.'));
		$ol->addItem(new \PHPFUI\ListItem('On the next page, get the <b>API Key</b> and <b>Secret</b> and save it below.'));
		$homePage = $settingTable->value('homePage');
		$ol->addItem(new \PHPFUI\ListItem("Add a <b>Redirect URI</b> to <b>{$homePage}/System/Settings/constantContact/token</b>"));
		$ol->addItem(new \PHPFUI\ListItem('Set the <b>App Logo URL</b> to an image you uploaded to this site in the Photos section.'));
		$ol->addItem(new \PHPFUI\ListItem('Set the app description and the name to something like <b>"Membership website automatic sync"</b> These are really comment fields so you know what this app is used for.'));
		$ol->addItem(new \PHPFUI\ListItem('You may be asked to log into Constant Contact.  Then accept the requested permissions.'));
		$ol->addItem(new \PHPFUI\ListItem('You should now see a big light green box at the top of the page saying <span class="success">"Constant Contact is authorized"</span>.'));
		$ol->addItem(new \PHPFUI\ListItem('Select the Constant Contact list you want to sync and hit <i>Save</i>.'));
		$fieldSet->add($ol);

		$versionFieldSet = new \PHPFUI\FieldSet('API Information');

		$fields = [];
		$fields['APIKey'] = 'Client Id / API Key (leave blank to turn off)';
		$fields['Secret'] = 'Secret';

		foreach ($fields as $field => $text)
			{
			$versionFieldSet->add($settingsSaver->generateField('ConstantContact' . $field, $text, 'text', false));
			}
		$fieldSet->add($versionFieldSet);
		$form->add($fieldSet);

		if (! $error)
			{
			$callout = null;

			$listFieldSet = new \PHPFUI\FieldSet('Membership Sync List');
			$listFieldSet->add('This list will be synced with member\'s email preferences set in the web site.');
			$listSelect = new \PHPFUI\Input\Select($name, 'Constant Contact Mailing List');

			try
				{
				$contactListsAPI = new \PHPFUI\ConstantContact\V3\ContactLists($client);
				$response = $contactListsAPI->get();

				if (! $contactListsAPI->success())
					{
					$callout = new \PHPFUI\Callout('alert');
					$callout->add(new \PHPFUI\Header('Constant Contact Error - Please wait', 5));
					$callout->add('<pre>' . \print_r($contactListsAPI->getLastError(), true) . '</pre>');
					$listFieldSet->add($callout);
					}

				do
					{
					foreach ($response['lists'] ?? [] as $row)
						{
						$listSelect->addOption($row['name'], $row['list_id'], $row['list_id'] == $currentList);
						}
					$response = $contactListsAPI->next();
					}
				while ($response);
				}
			catch (\Throwable $e)
				{
				\App\Tools\Logger::get()->debug($e);
				}

			if ($callout)
				{
				$form->add($callout);
				}
			else
				{
				$listFieldSet->add($settingsSaver->generateField($name, '', $listSelect, false));
				$form->add($listFieldSet);
				}
			}

		if ($form->isMyCallback())
			{
			$settingsSaver->save($_POST);
			$this->page->setResponse('Saved');
			}
		else
			{
			if ($error)
				{
				$authorize = new \PHPFUI\Button('Authorize', '/System/Settings/constantContact/auth');
				$authorize->addClass('warning');
				$form->add(new \App\UI\CancelButtonGroup($submit, $authorize));
				}
			else
				{
				$buttonGroup = new \App\UI\CancelButtonGroup($submit);
				$download = new \PHPFUI\Button('Download Contacts', '/System/Settings/constantContact/download');
				$download->addClass('success');
				$buttonGroup->addButton($download);

				$deauthorize = new \PHPFUI\Button('Deauthorize', '/System/Settings/constantContact/deauth');
				$deauthorize->addClass('alert');
				$buttonGroup->addButton($deauthorize);
				$form->add($buttonGroup);
				}
			}

		return $form;
		}

	/**
	 * @param array<string, string|array<string,string>> $input
	 *
	 * @return array<string, string|array<string,string>>
	 */
	private function flatten(array $input, string $index) : array
		{
		$suffixes = ['', '2', '3', ];
		$data = $input;

		foreach ($input[$index] ?? [] as $row)
			{
			if (! \is_string($row)) // @phpstan-ignore-line
				{
				unset($row['created_at'], $row['updated_at']);
				$suffix = \array_shift($suffixes);

				foreach ($row as $key => $value)
					{
					$data[$key . $suffix] = $value;
					}
				}
			}

		return $data;
		}
	}
