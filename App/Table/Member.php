<?php

namespace App\Table;

class Member extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\Member::class;

	public function badExpirations() : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = self::getSelectFields() . ' left join payment p on p.membershipId=m.membershipId WHERE p.amount=30.00 and p.paymentType=3 and p.dateReceived>=s.expires-60 and s.expires>? order by s.expires';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\App\Tools\Date::todayString(-366)]);
		}

	public static function currentMemberCount() : int
		{
		$sql = 'SELECT count(*) FROM member m ' .
			'left join membership s on m.membershipId=s.membershipId ' .
			'where s.expires>=?';

		return (int)\PHPFUI\ORM::getValue($sql, [\App\Tools\Date::todayString()]);
		}

	/**
	 * @param array<string,mixed> $parameters
	 */
	public function find(array $parameters) : \PHPFUI\ORM\DataObjectCursor
		{
		$whereCondition = $this->getWhereCondition();
		$this->addJoin('membership');
		$this->setFullJoinSelects();
		$this->addSelect(new \PHPFUI\ORM\Literal('concat(member.firstName, " ", member.lastName)'), 'memberName');

		if (! isset($parameters['all']))
			{
			$whereCondition->and('membership.expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		foreach ($parameters as $field => $value)
			{
			if (\str_starts_with($field, 'membership_'))
				{
				$whereCondition->and(\str_replace('_', '.', $field), '%' . $value . '%', new \PHPFUI\ORM\Operator\Like());
				}
			}

		if (! empty($parameters['categories']) && \is_array($parameters['categories']))
			{
			$categories = [];

			foreach ($parameters['categories'] as $category)
				{
				if ($category) // filter out all category, not useful
					{
					$categories[] = $category;
					}
				}

			if ($categories)
				{
				$this->addJoin('memberCategory', 'memberId');
				$whereCondition->and('memberCategory.categoryId', $categories, new \PHPFUI\ORM\Operator\In());
				}
			}

		return parent::addFind($parameters);
		}

	/**
	 * @param array<string> $names
	 */
	public function findByName(array $names, bool $currentMembers = true) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select m.firstName,m.lastName,m.memberId,m.email,s.* from member m left join membership s on m.membershipId=s.membershipId where ';
		$input = [];

		foreach ($names as $key => $name)
			{
			$names[$key] = \htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8');
			}

		if (1 == ($count = \count($names)))
			{
			$input = ["%{$names[0]}%",
				"%{$names[0]}%", ];
			$sql .= '(firstName like ? or lastName like ?)';
			}
		elseif ($count)
			{
			$input = ["%{$names[0]}%",
				"%{$names[1]}%", ];
			$sql .= '(firstName like ? and lastName like ?)';
			}

		if ($currentMembers)
			{
			$sql .= ' and s.expires>=?';
			$input[] = \App\Tools\Date::todayString();
			}
		$sql .= ' order by firstName,lastName';

		return \PHPFUI\ORM::getArrayCursor($sql, $input);
		}

	public function getAllMembers(string $expirationStart = '', string $expirationEnd = '') : \PHPFUI\ORM\ArrayCursor
	 {
	 $sql = 'select * from member m left join membership s on m.membershipId=s.membershipId where s.expires>=?';

	 if (! $expirationStart)
		 {
		 $expirationStart = \App\Tools\Date::todayString();
		 }
	 $input = [$expirationStart];

	 if ($expirationEnd)
		 {
		 $input[] = $expirationEnd;
		 $sql .= ' and s.expires<=?';
		 }

	 return \PHPFUI\ORM::getArrayCursor($sql, $input);
	 }

	/**
	 * @param array<int> $categories
	 */
	public static function getEmailableMembers(bool $all, bool $current, int $monthsPast = 0, int $monthsNew = 0, array $categories = [], string $extra = '') : \PHPFUI\ORM\ArrayCursor
		{
		if (1 == \count($categories) && 0 == $categories[0])
			{
			$categories = []; // all categories requested
			}
		$sql = 'select distinct m.firstName,m.lastName,m.email,m.memberId from member m left join membership s on m.membershipId=s.membershipId ';

		if ($categories)
			{
			$sql .= 'left join memberCategory c on c.memberId=m.memberId ';
			}
		$sql .= 'where m.email LIKE "%@%"';
		$condition = ' and (';
		$input = [];

		if ($current)
			{
			$condition .= 's.expires>=?';
			$input[] = \App\Tools\Date::todayString();
			}

		if ($monthsPast)
			{
			if (\strlen($condition) > 10)
				{
				$condition .= ' or ';
				}
			$condition .= '(s.expires>? and s.expires<?)';
			$input[] = \App\Tools\Date::todayString(-$monthsPast * 31);
			$input[] = \App\Tools\Date::todayString();
			}

		if ($monthsNew)
			{
			if (\strlen($condition) > 10)
				{
				$condition .= ' or ';
				}
			$condition .= 's.joined>?';
			$input[] = \App\Tools\Date::todayString(-$monthsNew * 31);
			}

		if (\strlen($condition) > 10)
			{
			$sql .= $condition . ')';
			}

		if ($categories)
			{
			$sql .= ' and c.categoryId in (' . \implode(',', $categories) . ')';
			}

		if (! $all)
			{
			$sql .= ' and m.emailAnnouncements=1';
			}
		$sql .= $extra;

		return \PHPFUI\ORM::getArrayCursor($sql, $input);
		}

	public static function getJournalMembers(string $expires) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select m.firstName,m.lastName,m.email,m.memberId from member m left join membership s on m.membershipId=s.membershipId where m.email LIKE "%@%" and s.expires>=? and m.journal=1';

		return \PHPFUI\ORM::getArrayCursor($sql, [$expires]);
		}

	/**
	 * Get members with ride notificatons turned on
	 */
	public function getJournalRideInterests() : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'SELECT m.firstName,m.lastName,m.email,m.memberId,c.categoryId,m.rideJournal from memberCategory c ' .
			'left join member m on m.memberId=c.memberId ' .
			'left join membership s on s.membershipId=m.membershipId ' .
			'where m.rideJournal>0 and m.email like "%@%" and s.expires>=? order by memberId,c.categoryId';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\App\Tools\Date::todayString()]);
		}

	/**
	 * @param array<int> $categories
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Member>
	 */
	public static function getLeaders(array $categories = [], string $type = 'Ride Leader', string $fromDate = '1000-01-01', string $toDate = '1000-01-01') : \PHPFUI\ORM\RecordCursor
		{
		if (1 == \count($categories) && 0 == \current($categories))
			{
			$categories = []; // all categories requested
			}
		$sql = 'select m.* from member m left join userPermission u on u.memberId = m.memberId left join membership s on s.membershipId = m.membershipId left join permission p on p.name=? ';
		$input = [$type];

		if ($categories)
			{
			$sql .= 'left join memberCategory c on c.memberId=m.memberId ';
			}
		$sql .= 'where u.permissionGroup = p.permissionId and s.expires>=? ';
		$input[] = \App\Tools\Date::todayString();

		if ($categories)
			{
			$sql .= 'and c.categoryId in (' . \implode(',', $categories) . ') ';
			}

		if ($fromDate > '1000-01-01')
			{
			if ('1000-01-01' > $toDate)
				{
				$toDate = '2999-12-31';
				}
			$sql .= 'and m.memberId in (select memberId from ride where rideDate>=? and rideDate<=?) ';
			$input[] = $fromDate;
			$input[] = $toDate;
			}
		$sql .= 'group by m.memberId order by m.lastName, m.firstName';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Member(), $sql, $input);
		}

	/**
	 * @return array<string,string>
	 */
	public function getMembership(int $memberId) : array
		{
		$sql = 'select * from member m left join membership s on m.membershipId=s.membershipId where m.memberId=?';

		return \PHPFUI\ORM::getRow($sql, [$memberId]);
		}

	public function getMembershipCursor(int $memberId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select *,concat(firstName, " ", lastName) as memberName from member m left join membership s on m.membershipId=s.membershipId where m.memberId=?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId]);
		}

	public function getMembershipObject(int $memberId) : \PHPFUI\ORM\DataObject
		{
		return new \PHPFUI\ORM\DataObject($this->getMembership($memberId));
		}

	public function getMembersWithPermission(string $permissionName) : static
		{
		$settingTable = new \App\Table\Setting();
		$permission = $settingTable->getStandardPermissionGroup($permissionName);

		if ($permission && $permission->permissionId)
			{
			$this->getMembersWithPermissionId($permission->permissionId);
			}
		else
			{
			$this->setWhere(new \PHPFUI\ORM\Condition('memberId', 0));
			\App\Tools\Logger::get()->debug($permissionName, 'permission not found');
			}

		return $this;
		}

	public function getMembersWithPermissionId(int $permissionId) : static
		{
		$this->addJoin('userPermission', 'memberId');
		$this->addJoin('membership', 'membershipId');
		$condition = new \PHPFUI\ORM\Condition('permissionGroup', $permissionId);
		$condition->and('expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$this->setWhere($condition);

		return $this;
		}

	public function getName(?int $memberId) : string
		{
		$sql = 'select IFNULL( (SELECT concat(firstName, " ", lastName) from member where memberId=?) ,"System")';

		return \PHPFUI\ORM::getValue($sql, [(int)$memberId]);
		}

	public static function getNewMembers(string $start, string $end) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = self::getSelectFields() . ' where s.expires>? and s.joined>=? and s.joined<? order by s.joined desc';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$start, $start, $end, ]);
		}

	/**
	 * Get members with new ride notificatons turned on
	 */
	public function getNewRideInterests(int $categoryId) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'SELECT m.firstName,m.lastName,m.email,m.memberId from member m left join memberCategory c on m.memberId=c.memberId ' .
			'left join membership s on s.membershipId=m.membershipId ' .
			'where m.newRideEmail and c.categoryId=? and m.email like "%@%" and s.expires>=?';

		return \PHPFUI\ORM::getArrayCursor($sql, [$categoryId, \App\Tools\Date::todayString()]);
		}

	public static function getNewsletterMembers(string $expires) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select * from member m left join membership s on m.membershipId=s.membershipId where m.email LIKE "%@%" and s.expires>=? and m.emailNewsletter>=1';

		return \PHPFUI\ORM::getArrayCursor($sql, [$expires]);
		}

	public function getPendingMembers(string $date) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = self::getSelectFields() . ' where s.pending>0 and s.joined<=?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$date]);
		}

	/**
	 * @param array<int> $events
	 */
	public static function getVolunteersForEvents(array $events) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select m.memberId,m.firstName,m.lastName,m.email from member m
			left join volunteerJobShift vjs on vjs.memberId=m.memberId
			left join job j on j.jobId=vjs.jobId
			left join jobEvent je on je.jobEventId=j.jobEventId
			where je.jobEventId in (' . \implode(',', \array_fill(0, \count($events), '?')) . ') group by m.memberId';

		return \PHPFUI\ORM::getArrayCursor($sql, $events);
		}

	public function getVolunteersForJob(\App\Record\Job $job) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select m.memberId,m.firstName,m.lastName,m.email from member m left join volunteerJobShift vjs on vjs.memberId=m.memberId left join job j on j.jobId=vjs.jobId left join jobEvent je on je.jobEventId=j.jobEventId where j.jobId=? group by m.memberId';

		return \PHPFUI\ORM::getArrayCursor($sql, [$job->jobId]);
		}

	public static function lastSignIns(int $days) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'SELECT memberId,lastLogin FROM member where lastLogin > ?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\date('Y-m-d H:i:s', \time() - (86400 * $days))]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Member>
	 */
	public static function membersInMembership(int $membershipId) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'SELECT *,concat(m.firstName," ",m.lastName) memberName FROM member m,membership s where s.membershipId=m.membershipId and s.membershipId=?';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Member(), $sql, [$membershipId]);
		}

	public function missingNames() : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = self::getSelectFields() . " where s.pending=0 and s.expires>=? and m.firstName<='' or m.lastName<=''";

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\App\Tools\Date::todayString()]);
		}

	public function noMembers() : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select "Missing" as memberName,m.* from membership m where membershipId not in (select membershipId from member) order by joined';

		return \PHPFUI\ORM::getDataObjectCursor($sql);
		}

	public function noPayments() : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = self::getSelectFields() . ' where s.expires>=? and s.pending=0 and s.membershipId NOT IN (SELECT membershipId from payment)';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\App\Tools\Date::todayString()]);
		}

	public function noPermissions() : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = self::getSelectFields() . ' WHERE m.memberId NOT IN (SELECT memberId from userPermission)';

		return \PHPFUI\ORM::getDataObjectCursor($sql);
		}

	public static function outstandingPoints(string $sort) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select * from member where volunteerPoints>0 order by ' . $sort;

		return \PHPFUI\ORM::getArrayCursor($sql);
		}

	public static function recentSignIns() : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = self::getSelectFields() . ' where acceptedWaiver>"2000" order by lastLogin desc limit 25';

		return \PHPFUI\ORM::getDataObjectCursor($sql);
		}

	public function updatePointDifference(int $memberId, int $difference) : bool
		{
		$sql = 'update member set volunteerPoints=volunteerPoints+? where memberId=?';

		return \PHPFUI\ORM::execute($sql, [$difference, $memberId]);
		}

	private static function getSelectFields(string $countRows = '') : string
		{
		return 'select ' . $countRows . ' m.*,s.*,concat(m.firstName," ",m.lastName) memberName from member m left join membership s on s.membershipId=m.membershipId ';
		}
	}
