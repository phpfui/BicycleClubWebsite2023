<?php

namespace App\Model;

class Membership
	{
	private readonly \App\Table\Membership $membershipTable;

	private readonly \App\Table\Member $memberTable;

	public function __construct()
		{
		$this->memberTable = new \App\Table\Member();
		$this->membershipTable = new \App\Table\Membership();
		}

	/**
	 * @return int number of members exported
	 */
	public function export(\App\Tools\CSVWriter $csvWriter, string $startDate = '1980-1-1', string $endDate = '2222-2-2', string $type = 'full') : int
		{
		$members = $this->memberTable->getAllMembers($startDate, $endDate);

		if ($members->valid())
			{
			$keys = $members->current();
			}
		else
			{
			$keys = [];
			}

		foreach (['password', 'profileX', 'profileY', 'profileWidth', 'profileHeight', 'loginAttempts'] as $key)
			{
			unset($keys[$key]);
			}
		$keys['category'] = 1;
		$newsletter = false;
		$announcements = false;
		$email = false;

		switch ($type)
			{
			case 'newsletter':

				$newsletter = $email = true;
				$columns = ['firstName', 'lastName', 'email'];


				// Intentionally fall through
			case 'annoucements':

				$announcements = $email = true;
				$columns = ['firstName', 'lastName', 'email'];

				break;

			default:

				$newsletter = $announcements = false;
				$columns = \array_keys($keys);

				break;
			}

		$csvWriter->outputRow($columns);
		$columns = \array_flip($columns);
		$count = 0;

		foreach ($members as $member)
			{
			if ($email && ! \filter_var($member['email'], FILTER_VALIDATE_EMAIL))
				{
				continue;
				}

			if ($newsletter && ! $member['emailNewsletter'])
				{
				continue;
				}

			if ($announcements && ! $member['emailAnnouncements'])
				{
				continue;
				}
			$member['category'] = \App\Table\MemberCategory::getRideCategoryStringForMember($member['memberId']);
			$csvWriter->outputRow(\array_intersect_key($member, $columns));
			++$count;
			}

		return $count;
		}

	/**
	 * @param array $mapping array indexed by db field name containing the field to move over from the import file
	 * @param bool $singleMembership true if each member has thier own membership record, false combine memberships of subsquent lines with same address and town
	 *
	 * @return int number of members imported
	 */
	public function import(\App\Tools\CSVReader $csvReader, array $mapping, bool $singleMembership = false) : int
		{
		$fields = [...\array_keys($this->memberTable->getFields()), ...\array_keys($this->membershipTable->getFields())];

		$dateFields = [
			'expires',
			'renews',
			'joined',
			'lastRenewed',
		];

		$timeFields = [
			'lastLogin',
			'acceptedWaiver',
		];

		$lastRecord = [];
		$lastMembershipId = 0;
		$count = 0;

		foreach ($csvReader as $row)
			{
			$record = [];

			foreach ($fields as $field)
				{
				if (! isset($mapping[$field]))
					{
					continue;
					}

				if (\strlen((string)$mapping[$field . 'Override']))
					{
					$record[$field] = $mapping[$field . 'Override'] ?? '';
					}
				elseif (\strlen((string)$mapping[$field]))
					{
					$record[$field] = $row[$mapping[$field]] ?? '';
					}
				else
					{
					$record[$field] = '';
					}
				}

			foreach ($dateFields as $field)
				{
				$date = $record[$field] ?? '';

				if (! empty($date))
					{
					$record[$field] = \date('Y-m-d', \strtotime((string)$date));
					}
				}

			foreach ($timeFields as $field)
				{
				$date = $record[$field] ?? '';

				if (! empty($date))
					{
					$record[$field] = \date('Y-m-d H:i:s', \strtotime((string)$date));
					}
				}

			if ($singleMembership || ($lastRecord['address'] ?? '') != ($record['address'] ?? '') || ($lastRecord['town'] ?? '') != ($record['town'] ?? ''))
				{
				$membership = new \App\Record\Membership();
				$membership->setFrom($record);
				$lastMembershipId = $membership->insert();
				}
			$member = new \App\Record\Member();
			$member->setFrom($record);
			$member->membershipId = $lastMembershipId;
			$member->verifiedEmail = 9;
			$permission = new \App\Record\UserPermission();
			$permission->member = $member;
			$permission->permissionGroup = 6;
			$permission->insertOrUpdate();

			$lastRecord = $record;
			++$count;
			}

		return $count;
		}
	}
