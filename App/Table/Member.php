<?php

namespace App\Table;

class Member extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\Member::class;

	public function abandoned() : static
		{
		$this->setSelectedFields();
		$condition = new \PHPFUI\ORM\Condition('membership.expires', null);
		$condition->and('membership.pending', 1);
		$condition->and('member.verifiedEmail', 9, new \PHPFUI\ORM\Operator\LessThan());
		$this->setWhere($condition);

		return $this;
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
			if (\is_string($value) && \strlen($value) && \str_starts_with($field, 'membership_'))
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

			if (\count($categories))
				{
				$this->addJoin('memberCategory');
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
		$this->setSelect('firstName');
		$this->addSelect('lastName');
		$this->addSelect('memberId');
		$this->addSelect('email');
		$this->addSelect('showNothing');
		$this->addSelect('membership.*');
		$this->setJoin('membership');

		$condition = new \PHPFUI\ORM\Condition();

		foreach ($names as $key => $name)
			{
			$names[$key] = \htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8');
			}

		if (1 == ($count = \count($names)))
			{
			$orCondition = new \PHPFUI\ORM\Condition('firstName', "%{$names[0]}%", new \PHPFUI\ORM\Operator\Like());
			$orCondition->or('lastName', "%{$names[0]}%", new \PHPFUI\ORM\Operator\Like());
			$condition->and($orCondition);
			}
		elseif ($count)
			{
			$condition->and('firstName', "%{$names[0]}%", new \PHPFUI\ORM\Operator\Like());
			$condition->and('lastName', "%{$names[1]}%", new \PHPFUI\ORM\Operator\Like());
			}

		if ($currentMembers)
			{
			$condition->and('expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}
		$this->setWhere($condition);
		$this->setOrderBy('firstName');
		$this->addOrderBy('lastName');

		return $this->getArrayCursor();
		}

	public function getAllMembers(string $expirationStart = '', string $expirationEnd = '') : \PHPFUI\ORM\ArrayCursor
		{
		$this->setSelect('member.*');
		$this->addSelect('membership.*');
		$this->setJoin('membership');
		$condition = new \PHPFUI\ORM\Condition('expires', $expirationStart, new \PHPFUI\ORM\Operator\GreaterThanEqual());

		if ($expirationEnd)
			{
			$condition->and('expires', $expirationEnd, new \PHPFUI\ORM\Operator\LessThanEqual());
			}
		$this->setWhere($condition);
		$this->setOrderBy('memberId');

		return $this->getArrayCursor();
		}

	/**
	 * @param array<int> $categories
	 */
	public function getEmailableMembers(bool $all, bool $current, int $monthsPast = 0, int $monthsNew = 0, array $categories = [], string $extra = '') : static
		{
		if (1 == \count($categories) && 0 == $categories[0])
			{
			$categories = []; // all categories requested
			}
		$this->setDistinct();
		$this->addJoin('membership');

		$condition = new \PHPFUI\ORM\Condition('email', '%@%', new \PHPFUI\ORM\Operator\Like());

		if (! $all)
			{
			$condition->and('emailAnnouncements', 1);
			}

		$dateRestriction = new \PHPFUI\ORM\Condition();

		if ($current)
			{
			$dateRestriction->or('membership.expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if ($monthsPast)
			{
			$and = new \PHPFUI\ORM\Condition('membership.expires', \App\Tools\Date::todayString(-$monthsPast * 31), new \PHPFUI\ORM\Operator\GreaterThan());
			$and->and('membership.expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThan());
			$dateRestriction->or($and);
			}

		if ($monthsNew)
			{
			$dateRestriction->or(new \PHPFUI\ORM\Condition('membership.joined', \App\Tools\Date::todayString(-$monthsNew * 31), new \PHPFUI\ORM\Operator\GreaterThan()));
			}
		$condition->and($dateRestriction);

		if ($categories)
			{
			$this->addJoin('memberCategory');
			$condition->and('categoryId', $categories, new \PHPFUI\ORM\Operator\In());
			}

		$this->setWhere($condition);

		return $this;
		}

	public function getJournalMembers(string $expires) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setJoin('membership');
		$this->setSelect('firstName');
		$this->addSelect('lastName');
		$this->addSelect('email');
		$this->addSelect('memberId');

		$condition = new \PHPFUI\ORM\Condition('email', '%@%', new \PHPFUI\ORM\Operator\Like());
		$condition->and('expires', $expires, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('journal', 1);
		$this->setWhere($condition);

		return $this->getArrayCursor();
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

		$memberCategoryTable = new \App\Table\MemberCategory();

		$memberCategoryTable->setSelect('firstName');
		$memberCategoryTable->addSelect('lastName');
		$memberCategoryTable->addSelect('email');
		$memberCategoryTable->addSelect('member.memberId');
		$memberCategoryTable->addSelect('categoryId');
		$memberCategoryTable->addSelect('rideJournal');

		$memberCategoryTable->setJoin('member');
		$memberCategoryTable->addJoin('membership', new \PHPFUI\ORM\Condition('member.membershipId', new \PHPFUI\ORM\Literal('membership.membershipId')));

		$condition = new \PHPFUI\ORM\Condition('rideJournal', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('email', '%@%', new \PHPFUI\ORM\Operator\Like());
		$condition->and('expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());

		$memberCategoryTable->setWhere($condition);
		$memberCategoryTable->setOrderBy('member.memberId');
		$memberCategoryTable->addOrderBy('categoryId');

		return $memberCategoryTable->getDataObjectCursor();
		}

	/**
	 * @param array<int> $categories
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Member>
	 */
	public function getLeaders(array $categories = [], string $type = 'Ride Leader', ?string $fromDate = null, ?string $toDate = null, ?string $minLed = null, ?string $maxLed = null) : \PHPFUI\ORM\RecordCursor
		{
		$type = new \App\Table\Setting()->getStandardPermissionGroup($type)->name ?? 'Ride Leader';

		if (1 == \count($categories) && 0 == \current($categories))
			{
			$categories = []; // all categories requested
			}
		$this->addSelect('member.*');
		$this->addGroupBy('member.memberId');
		$this->addOrderBy('member.lastName');
		$this->addOrderBy('member.firstName');
		$this->setJoin('userPermission');
		$this->addJoin('membership');
		$this->addJoin('permission', new \PHPFUI\ORM\Condition('permission.name', $type));
		$where = new \PHPFUI\ORM\Condition('userPermission.permissionGroup', new \PHPFUI\ORM\Field('permission.permissionId'));
		$where->and('membership.expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());

		if ($categories)
			{
			$this->addJoin('memberCategory');
			$where->and('memberCategory.categoryId', $categories, new \PHPFUI\ORM\Operator\In());
			}

		if (null !== $minLed || null !== $maxLed || null !== $fromDate || null !== $toDate)
			{
			$rideTable = new \App\Table\Ride();
			$rideTable->addSelect('memberId');
			$rideTable->addGroupBy('memberId');
			$rideWhere = new \PHPFUI\ORM\Condition();
			$rideTable->setWhere($rideWhere);

			if (! empty($fromDate))
				{
				$rideWhere->and('rideDate', $fromDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
				}

			if (! empty($toDate))
				{
				$rideWhere->and('rideDate', $toDate, new \PHPFUI\ORM\Operator\LessThanEqual());
				}

			$havingCondition = new \PHPFUI\ORM\Condition();

			if (\strlen($minLed))
				{
				$havingCondition->and(new \PHPFUI\ORM\Literal('count(*)'), $minLed, new \PHPFUI\ORM\Operator\GreaterThanEqual());
				}

			if (\strlen($maxLed))
				{
				$havingCondition->and(new \PHPFUI\ORM\Literal('count(*)'), $maxLed, new \PHPFUI\ORM\Operator\LessThanEqual());
				}

			if (\count($havingCondition))
				{
				$rideTable->setHaving($havingCondition);
				}
			$where->and('member.memberId', $rideTable, new \PHPFUI\ORM\Operator\In());
			}
		$this->setWhere($where);

		return $this->getRecordCursor();
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
		$this->setJoin('membership');
		$this->setWhere(new \PHPFUI\ORM\Condition('memberId', $memberId));

		return $this->getDataObjectCursor();
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
			$this->setWhere(new \PHPFUI\ORM\Condition('member.memberId', 0));
			}

		return $this;
		}

	public function getMembersWithPermissionId(int $permissionId) : static
		{
		$this->addJoin('userPermission');
		$this->addJoin('membership');
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

	public function getNewMembers(string $start, string $end) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('membership');
		$this->addJoin('rideSignup');
		$this->setSelect('member.*');
		$this->addSelect('membership.*');
		$this->addSelect(new \PHPFUI\ORM\Literal('count(rideSignup.memberId)'), 'rides');
		$this->addGroupBy('member.memberId');
		$condition = new \PHPFUI\ORM\Condition('membership.expires', $start, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('membership.joined', $start, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('membership.joined', $end, new \PHPFUI\ORM\Operator\LessThan());
		$this->setWhere($condition);
		$this->setOrderBy('membership.joined', 'desc');

		return $this->getDataObjectCursor();
		}

	/**
	 * Get members with new ride notificatons turned on
	 */
	public function getNewRideInterests(int $categoryId) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'SELECT distinct m.firstName,m.lastName,m.email,m.memberId
			from member m
			left join memberCategory c on m.memberId=c.memberId ' .
			'left join membership s on s.membershipId=m.membershipId ' .
			'where m.newRideEmail and c.categoryId=? and m.email like "%@%" and s.expires>=?
			order by m.memberId';

		$this->setDistinct();
		$this->setSelect('firstName');
		$this->addSelect('lastName');
		$this->addSelect('email');
		$this->addSelect('member.memberId');

		$condition = new \PHPFUI\ORM\Condition('newRideEmail', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('categoryId', $categoryId);
		$condition->and('email', '%@%', new \PHPFUI\ORM\Operator\Like());
		$condition->and('expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$this->setWhere($condition);

		$this->setJoin('memberCategory');
		$this->addJoin('membership');
		$this->setOrderBy('member.memberId');

		return $this->getArrayCursor();
		}

	public function getNewsletterMembers(string $expires) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setJoin('membership');
		$condition = new \PHPFUI\ORM\Condition('email', '%@%', new \PHPFUI\ORM\Operator\Like());
		$condition->and('expires', $expires, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('emailNewsletter', 1, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$this->setWhere($condition);

		return $this->getArrayCursor();
		}

	public function getPendingMembers(string $date) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('membership');
		$condition = new \PHPFUI\ORM\Condition('pending', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('joined', $date, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);

		return $this->getDataObjectCursor();
		}

	/**
	 * @param array<int> $events
	 */
	public function getVolunteersForEvents(array $events) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setSelect('member.memberId');
		$this->addSelect('member.firstName');
		$this->addSelect('member.lastName');
		$this->addSelect('member.email');
		$this->setJoin('volunteerJobShift');
		$this->addJoin('job', new \PHPFUI\ORM\Condition('job.jobId', new \PHPFUI\ORM\Literal('volunteerJobShift.jobId')));
		$this->addJoin('jobEvent', new \PHPFUI\ORM\Condition('jobEvent.jobEventId', new \PHPFUI\ORM\Literal('job.jobEventId')));
		$this->setWhere(new \PHPFUI\ORM\Condition('jobEvent.jobEventId', $events, new \PHPFUI\ORM\Operator\In()));
		$this->setGroupBy('member.memberId');
		$this->setOrderBy('member.memberId');

		return $this->getArrayCursor();
		}

	public function getVolunteersForJob(\App\Record\Job $job) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setSelect('member.memberId');
		$this->addSelect('member.firstName');
		$this->addSelect('member.lastName');
		$this->addSelect('member.email');
		$this->setJoin('volunteerJobShift');
		$this->addJoin('job', new \PHPFUI\ORM\Condition('job.jobId', new \PHPFUI\ORM\Literal('volunteerJobShift.jobId')));
		$this->addJoin('jobEvent', new \PHPFUI\ORM\Condition('jobEvent.jobEventId', new \PHPFUI\ORM\Literal('job.jobEventId')));
		$this->setWhere(new \PHPFUI\ORM\Condition('job.jobId', $job->jobId));
		$this->setGroupBy('member.memberId');
		$this->setOrderBy('member.memberId');

		return $this->getArrayCursor();
		}

	public function lastSignIns(int $days) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setSelect('memberId');
		$this->addSelect('lastLogin');
		$this->setWhere(new \PHPFUI\ORM\Condition('lastLogin', \date('Y-m-d H:i:s', \time() - (86400 * $days)), new \PHPFUI\ORM\Operator\GreaterThan()));

		return $this->getDataObjectCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Member>
	 */
	public function membersInMembership(int $membershipId) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$this->setWhere(new \PHPFUI\ORM\Condition('membershipId', $membershipId));

		return $this->getRecordCursor();
		}

	public function missingNames() : static
		{
		$this->setSelectedFields();
		$condition = new \PHPFUI\ORM\Condition('membership.expires', null);
		$condition->and('membership.pending', 0);
		$missingName = new \PHPFUI\ORM\Condition('member.firstName', '', new \PHPFUI\ORM\Operator\LessThanEqual());
		$missingName->or('member.firstName', null);
		$missingName->or('member.lastName', null);
		$missingName->or('member.lastName', '', new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and($missingName);
		$this->setWhere($condition);

		return $this;
		}

	public function noPermissions() : static
		{
		$this->setSelectedFields();
		$permissionTable = new \App\Table\UserPermission()->addSelect('memberId');
		$condition = new \PHPFUI\ORM\Condition('member.memberId', $permissionTable, new \PHPFUI\ORM\Operator\NotIn());
		$this->setWhere($condition);

		return $this;
		}

	public function outstandingPoints(string $sort) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('volunteerPoints', 0, new \PHPFUI\ORM\Operator\GreaterThan()));
		$this->addOrderBy($sort);

		return $this->getArrayCursor();
		}

	public function updatePointDifference(int $memberId, int $difference) : bool
		{
		$sql = 'update member set volunteerPoints=volunteerPoints+? where memberId=?';

		return \PHPFUI\ORM::execute($sql, [$difference, $memberId]);
		}

	private function setSelectedFields() : static
		{
		$this->addJoin('membership');
		$this->addSelect('member.*');
		$this->addSelect('membership.*');

		return $this;
		}
	}
