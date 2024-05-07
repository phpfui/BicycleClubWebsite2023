<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';
$doit = isset($argv[2]);

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$memberTable = new \App\Table\Member();
$memberTable->addOrderBy('email');
$memberTable->addOrderBy('lastLogin', 'desc');
$memberTable->addOrderBy('memberId');

$emails = [];

foreach ($memberTable->getRecordCursor() as $member)
	{
	$email = $member->email;

	if (! \filter_var($email, FILTER_VALIDATE_EMAIL))
		{
		continue;
		}

	if (! isset($emails[$email]))
		{
		$emails[$email] = [];
		}

	if (! \in_array($member->membershipId, $emails[$email]))
		{
		$emails[$email][] = $member->membershipId;
		}
	else // more than one email address in a membership, remove the older login
		{
		echo "{$email} ({$member->fullName()}) has duplicates in membership {$member->membershipId}\n";

		if ($doit)
			{
			$member->delete();
			}
		}
	}

$count = 0;

foreach ($emails as $email => $memberships)
	{
	if (\count($memberships) > 1)
		{
		++$count;
		echo "\n{$email}\n";
		\sort($memberships);

		foreach ($memberships as $email => $membershipId)
			{
			$memberships[$email] = new \App\Record\Membership($membershipId);
			}
		$minMembership = $maxMembership = null;

		foreach ($memberships as $email => $membership)
			{
			if (null == $minMembership || $minMembership->expires >= $membership->expires)
				{
				$minMembership = $membership;
				}

			if (null == $maxMembership || $maxMembership->expires < $membership->expires)
				{
				$maxMembership = $membership;
				}
			}

		if ($minMembership->joined && $maxMembership->joined > $minMembership->joined)
			{
			echo "Membership updated from latest {$maxMembership->joined} to older {$minMembership->joined}\n";
			$maxMembership->joined = $minMembership->joined;

			if ($doit)
				{
				$maxMembership->update();
				}
			}

		if ($maxMembership->membershipId != $minMembership->membershipId)
			{
			echo "Delete Membership {$minMembership->joined} to {$minMembership->expires}\n";

			if (\abs($minMembership->membershipId - $maxMembership->membershipId) < 10)
				{
				echo "Delete duplicate added Membership {$minMembership->membershipId} count={$minMembership->MemberChildren->count()} keep: {$maxMembership->membershipId} count={$maxMembership->MemberChildren->count()} \n";
				}

			if (! $doit)
				{
				continue;
				}

			foreach ($minMembership->MemberChildren as $member)
				{
				$oldMember = new \App\Record\Member(['membershipId' => $minMembership->membershipId, 'email' => $email]);
				$goodMember = new \App\Record\Member(['membershipId' => $maxMembership->membershipId, 'email' => $email]);

				if ($oldMember->memberId != $goodMember)
					{
					\App\Model\Member::replace($oldMember, $goodMember);
					$oldMember->delete();
					}
				}
			$minMembership->delete();
			}
		}
	}

echo $count . ' duplicate email addresses';
