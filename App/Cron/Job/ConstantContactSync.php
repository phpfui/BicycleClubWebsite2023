<?php

namespace App\Cron\Job;

class ConstantContactSync extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Sync the member email preferences with Constant Contact.';
		}

	/*
	 * Syncing to Constant Contact
	 *
	 * Get all members who wish to be subscribed from database
	 * Get all current Constant Contact users
	 * Set any members that unsubscribed from Constant Contact, but not us (in above list) set to unsubscribed in database
	 * If they are not on the list provided, then add them to the list provided
	 * Remove them from in memory list
	 * Add remaining members to Constant Contact and add them to the list provided.
	 */
	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$client = new \App\Model\ConstantContact();

		if (! $client->isAuthorized())
			{
			return;
			}
		$settingTable = new \App\Table\Setting();

		// Get all members who wish to be subscribed from database
		$newsletterMembers = \App\Table\Member::getNewsletterMembers(\App\Tools\Date::todayString());

		$subscribed = [];

		foreach ($newsletterMembers as $member)
			{
			$email = \trim(\strtolower((string)$member['email']));

			if (\filter_var($email, FILTER_VALIDATE_EMAIL))
				{
				$subscribed[$email] = $member;
				}
			}

		$contactsClient = new \PHPFUI\ConstantContact\V3\Contacts($client);

		// Get all current Constant Contact users
		$syncList = $settingTable->value('ConstantContactSyncList');
		$list = $contactsClient->get(status: 'all', limit: 500, lists:$syncList);

		do
			{
			foreach ($list['contacts'] ?? [] as $contact)
				{
				$email = \trim(\strtolower($contact['email_address']['address'] ?? ''));

				if (isset($contact['email_address']['permission_to_send']))
					{
					// Set any members that unsubscribed from Constant Contact, but not us (in above list) to unsubscribed in database
					if ('unsubscribed' == $contact['email_address']['permission_to_send'])
						{
						if (isset($subscribed[$email]))
							{
							$member = new \App\Record\Member(['email' => $email]);
							$member->emailNewsletter = 0;
							$member->update();
							unset($subscribed[$email]);
							}
						}
					}

				if ($email)
					{
					// Remove them from in memory list
					unset($subscribed[$email]);
					}
				}
			$list = $contactsClient->next();
			}
		while ($list);

		// Add remaining members to Constant Contact and add them to the list provided.
		foreach ($subscribed as $email => $member)
			{
			$contactBody = new \PHPFUI\ConstantContact\Definition\ContactPostRequest();
			$contactBody->first_name = $member['firstName'];
			$contactBody->last_name = $member['lastName'];
			$contactBody->create_source = 'Account';
			$contactBody->list_memberships = [new \PHPFUI\ConstantContact\UUID($syncList)];
			$contactBody->street_addresses = [new \PHPFUI\ConstantContact\Definition\StreetAddressPut([
				'kind' => 'home',
				'street' => $member['address'],
				'city' => $member['town'],
				'state' => $member['state'],
				'postal_code' => $member['zip'],
				'country' => 'USA', ])];
			$email = new \PHPFUI\ConstantContact\Definition\EmailAddressPost();
			$email->address = $member['email'];
			$email->permission_to_send = 'explicit';
			$contactBody->email_address = $email;

			$numbers = [];

			if ($member['phone'])
				{
				$numbers[] = new \PHPFUI\ConstantContact\Definition\PhoneNumberPut(['phone_number' => $member['phone'], 'kind' => 'home']);
				}

			if ($member['cellPhone'])
				{
				$numbers[] = new \PHPFUI\ConstantContact\Definition\PhoneNumberPut(['phone_number' => $member['cellPhone'], 'kind' => 'mobile']);
				}

			if ($numbers)
				{
				$contactBody->phone_numbers = $numbers;
				}
			$contactsClient->post($contactBody);

			if (! $contactsClient->success())
				{
				if (409 == $contactsClient->getStatusCode())
					{
					$updateClient = new \PHPFUI\ConstantContact\V3\Contacts\SignUpForm($client);
					$contactBody = new \PHPFUI\ConstantContact\Definition\ContactCreateOrUpdateInput();
					$contactBody->email_address = $member['email'];
					$contactBody->first_name = $member['firstName'];
					$contactBody->last_name = $member['lastName'];
					$contactBody->phone_number = $member['cellPhone'];
					$contactBody->list_memberships = [new \PHPFUI\ConstantContact\UUID($syncList)];
					$street_address = new \PHPFUI\ConstantContact\Definition\StreetAddress();
					$street_address->kind = 'home';
					$street_address->street = $member['address'];
					$street_address->city = $member['town'];
					$street_address->state = $member['state'];
					$street_address->postal_code = $member['zip'];
					$street_address->country = 'USA';
					$contactBody->street_address = $street_address;
					$updateClient->post($contactBody);
					}
				else
					{
					\App\Tools\Logger::get()->debug($contactsClient->getStatusCode(), $contactsClient->getLastError());
					\App\Tools\Logger::get()->debug($contactBody->getData());
					}
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(2, 55);
		}
	}
