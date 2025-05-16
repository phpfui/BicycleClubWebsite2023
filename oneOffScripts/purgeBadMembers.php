<?php

include __DIR__ . '/../common.php';

$memberTable = new \App\Table\Member();

foreach ($memberTable->getRecordCursor() as $member)
	{
	$lastChars = \substr($member->lastName, \strlen($member->lastName) - 2);

	if (\ctype_upper($lastChars))
		{
		echo $member->lastName . " {$member->memberId}\n";

		if (! \in_array($lastChars, ['MD', 'II', 'JR', 'DS', 'LP']))
			{
			$member->membership->delete();
			$member->delete();
			}
		elseif ($member->memberId > 25000)
			{
			$member->membership->delete();
			$member->delete();
			}
		}
	}
