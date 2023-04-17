<?php

namespace App\Table;

class ForumMember extends \PHPFUI\ORM\Table
	{
	final public const DAILY = 3;

	final public const INDIVIDUAL = 2;

	final public const UNSUBSCRIBED = 0;

	final public const VIEW = 1;

	protected static string $className = '\\' . \App\Record\ForumMember::class;

	public static function getCount(\App\Record\Forum $forum) : int
		{
		$input = [$forum->forumId, \App\Tools\Date::todayString()];

		return (int)\PHPFUI\ORM::getValue(
			'select count(*) from forumMember f left join member m on m.memberId=f.memberId
														left join membership s on s.membershipId=m.membershipId where f.forumId=? and s.expires>=? and m.deceased=0',
			$input
		);
		}

	public static function getDigestMembers(\App\Record\Forum $forum) : iterable
		{
		return self::getMembers($forum, ['emailType' => self::DAILY]);
		}

	public static function getEmailMembers(\App\Record\Forum $forum) : iterable
		{
		return self::getMembers($forum, ['emailType' => self::INDIVIDUAL]);
		}

	public static function getMembers(\App\Record\Forum $forum, array $additionalWhere = []) : \PHPFUI\ORM\DataObjectCursor
		{
		$and = '';
		$input = [$forum->forumId, \App\Tools\Date::todayString()];

		if ($additionalWhere)
			{
			foreach ($additionalWhere as $field => $value)
				{
				$input[] = $value;
				$and .= ' and ' . $field . '=?';
				}
			}
		$sql = 'select * from forumMember f left join member m on m.memberId=f.memberId left join membership s on s.membershipId=m.membershipId where f.forumId=? and s.expires>=? and m.deceased=0 ' . $and . ' order by m.lastName,m.firstName';

		return \PHPFUI\ORM::getDataObjectCursor($sql, $input);
		}

	public static function getSubscriptionTypes() : array
		{
		return [
			self::UNSUBSCRIBED => 'Unsubscribe',
			self::VIEW => 'Subscription - View On Web',
			self::INDIVIDUAL => 'Subscription - Individual Emails',
			self::DAILY => 'Subscription - Daily Digest Email',
		];
		}
	}
