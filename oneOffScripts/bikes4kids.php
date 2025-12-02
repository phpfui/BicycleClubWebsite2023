<?php

include 'common.php';

$sql = 'select member.memberId, 21 as forumId from volunteerJobShift vjs
left join member on member.memberId=vjs.memberId
left join job on job.jobId=vjs.jobId
left join jobEvent je on je.jobEventId=job.jobEventId
where je.name like "bikes4kids%" and member.memberId is not null and member.email > ""
group by member.email;';

foreach (\PHPFUI\ORM::getArrayCursor($sql) as $member)
	{
	$forumMember = new \App\Record\ForumMember($member);

	if (! $forumMember->loaded())
		{
		$member['emailType'] = App\Enum\Forum\SubscriptionType::INDIVIDUAL_EMAILS->value;
		$forumMember->setFrom($member);
		$forumMember->insert();
		}
	}
