<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$memberTable = new \App\Table\Member();
$memberTable->addOrderBy('email');
$memberTable->addOrderBy('password', 'desc');

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
	else // more than one email address in a membership, remove the shortest password (old or not set)
		{
		$member->delete();
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
			if (null == $minMembership || $minMembership->expires > $membership->expires)
				{
				$minMembership = $membership;
				}

			if (null == $maxMembership || $maxMembership->expires < $membership->expires)
				{
				$maxMembership = $membership;
				}
			}

		if ($minMembership->joined)
			{
			$maxMembership->joined = $minMembership->joined;
			$maxMembership->update();
			}

		if ($maxMembership->membershipId != $minMembership->membershipId)
			{
			foreach ($minMembership->MemberChildren as $member)
				{
				$member->email = '';
				$member->update();
				}
			echo "Max Id: {$maxMembership->membershipId} Max Expires: {$maxMembership->expires}, Joined: {$maxMembership->joined} Members on membership: {$maxMembership->MemberChildren->count()}\n";
			echo "Min Id: {$minMembership->membershipId} Min Expires: {$minMembership->expires}, Joined: {$minMembership->joined} Members on membership: {$minMembership->MemberChildren->count()}\n";
			}
		else
			{
			echo "membershipId: {$membership->membershipId} Expires: {$membership->expires}, Joined: {$membership->joined} Members on membership: {$membership->MemberChildren->count()}\n";

			foreach ($membership->MemberChildren as $member)
				{
				\print_r($member->toArray());
				}
			}
		}
	}

echo $count . ' duplicate email addresses';
