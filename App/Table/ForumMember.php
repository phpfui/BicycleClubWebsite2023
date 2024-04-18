<?php

namespace App\Table;

class ForumMember extends \PHPFUI\ORM\Table
	{
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

	public function getDigestMembers(\App\Record\Forum $forum) : \PHPFUI\ORM\DataObjectCursor
		{
		return $this->getMembers($forum, ['emailType' => \App\Enum\Forum\SubscriptionType::DAILY_DIGEST_EMAIL]);
		}

	public function getEmailMembers(\App\Record\Forum $forum) : \PHPFUI\ORM\DataObjectCursor
		{
		return $this->getMembers($forum, ['emailType' => \App\Enum\Forum\SubscriptionType::INDIVIDUAL_EMAILS]);
		}

	/**
	 * @param array<string,mixed> $additionalWhere
	 */
	public function getMembers(\App\Record\Forum $forum, array $additionalWhere = []) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setMembersQuery($forum, $additionalWhere);

		return $this->getDataObjectCursor();
		}

	/**
	 * @param array<string,mixed> $additionalWhere
	 */
	public function setMembersQuery(\App\Record\Forum $forum, array $additionalWhere = []) : self
		{
		$this->addJoin('member');
		$this->addJoin('membership', new \PHPFUI\ORM\Condition('member.membershipId', new \PHPFUI\ORM\Field('membership.membershipId')));
		$this->addOrderBy('member.lastName');
		$this->addOrderBy('member.firstName');
		$condition = new \PHPFUI\ORM\Condition('forumId', $forum->forumId);
		$condition->and('expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('deceased', 0);

		foreach ($additionalWhere as $field => $value)
			{
			$condition->and($field, $value);
			}

		$this->setWhere($condition);

		return $this;
		}
	}
