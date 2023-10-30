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
		$contactClient = new \PHPFUI\ConstantContact\V3\Contact($client);

		// Get all current Constant Contact users
		$syncList = $settingTable->value('ConstantContactSyncList');
		$list = $contactsClient->get(status: 'all', limit: 500);

		do
			{
			foreach ($list['contacts'] as $contact)
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

				if (isset($subscribed[$email]))
					{
					// If they are not on the list provided, then add them to the list provided
					$synced = false;

					foreach ($contact['list_memberships'] ?? [] as $contactList)
						{
						if ($contactList == $syncList)
							{
							$synced = true;
							}
						}

					if (! $synced)
						{
						$contact['list_memberships'][] = $syncList;
						$email_address = new \PHPFUI\ConstantContact\Definition\EmailAddressPut($contact['email_address']);
//						$now = new \PHPFUI\ConstantContact\DateTime();
//						$email_address->created_at = clone $now;
						$email_address->permission_to_send = 'explicit';
						$contact['email_address'] = $email_address;
						$contactBody = new \PHPFUI\ConstantContact\Definition\ContactPutRequest($contact);
						$contactBody->update_source = 'Account';
						$member = $subscribed[$email];
						$contactBody->street_addresses = [new \PHPFUI\ConstantContact\Definition\StreetAddressPut([
							'kind' => 'home',
							'street' => $member['address'],
							'city' => $member['town'],
							'state' => $member['state'],
							'postal_code' => $member['zip'],
							'country' => 'USA', ])];

						$contactClient->put($contact['contact_id'], $contactBody);

						if (! $contactClient->success())
							{
							\App\Tools\Logger::get()->debug($contactClient->getStatusCode(), $contactClient->getLastError());
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
				\App\Tools\Logger::get()->debug($contactsClient->getStatusCode(), $contactsClient->getLastError());
				\App\Tools\Logger::get()->debug($contactBody->getData());
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(2, 55);
		}
	}
