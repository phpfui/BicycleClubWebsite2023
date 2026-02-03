<?php

namespace App\Model;

class Member
	{
	final public const int ADDITIONAL_MEMBERSHIP = 2;

	final public const int DONATION = 3;

	final public const int EVENT_ADDITIONAL_MEMBERSHIP = 5;

	final public const int EVENT_MEMBERSHIP = 4;

	final public const int FIRST_MEMBERSHIP = 1;

	final public const string MEMBERSHIP_ADDITIONAL_TITLE = '12 Month Membership Additional Member';

	final public const string MEMBERSHIP_DONATION_TITLE = 'Additional Donation';

	final public const int MEMBERSHIP_JOIN = 2;

	final public const string MEMBERSHIP_ONE_ADDITIONAL_TITLE = 'Additional Member';

	final public const int MEMBERSHIP_RENEWAL = 1;

	final public const string MEMBERSHIP_TITLE = '12 Month Membership';

	final public const int ONE_ADDITIONAL_MEMBER = 6;

	/** @var	array<string> */
	protected array $defaultFields = ['rideJournal', 'newRideEmail', 'emailNewsletter', 'emailAnnouncements', 'journal', 'rideComments', 'geoLocate',
		'showNothing', 'showNoStreet', 'showNoTown', 'showNoPhone', 'showNoRideSignup', 'showNoSignin', 'showNoSocialMedia', ];

	/** @var array<string,int> */
	protected array $passwordOptions = ['cost' => 11];

	/** @var array<string> */
	protected array $supervisorFields = [
		'lastLogin',
		'allowedMembers',
		'volunteerPoints',
		'acceptedWaiver',
		'pending',
		'pendingLeader',
		'deceased',
		'expires',
		'joined',
		'affiliation',
		'lastRenewed',
	];

	private readonly \App\Model\MembershipDues $duesModel;

	private readonly \App\Table\Member $memberTable;

	private readonly \App\Table\Setting $settingTable;

	private int $storeItemIdType = self::MEMBERSHIP_RENEWAL;

	public function __construct()
		{
		$this->memberTable = new \App\Table\Member();
		$this->settingTable = new \App\Table\Setting();
		$this->duesModel = new \App\Model\MembershipDues();
		}

	/**
	 * @param array<string,mixed> $member
	 */
	public function add(array $member) : int
		{
		$member['pendingLeader'] = 0;
		$member['pending'] = 1;
		unset($member['memberId'], $member['membershipId']);

		$member['email'] = static::cleanEmail($member['email']);
		$member['firstName'] = \ucwords((string)$member['firstName']);
		$member['lastName'] = \ucwords((string)$member['lastName']);
		$member['expires'] = $member['lastRenewed'] = null;
		$member['acceptedWaiver'] = null;
		$member['password'] = $this->hashPassword($member['password']);
		$membership = new \App\Record\Membership();
		$membership->setFrom($member);
		$member['membershipId'] = $membership->insert();
		$memberRecord = new \App\Record\Member();
		$this->setDefaultFields($memberRecord);
		$memberRecord->setFrom($member);

		$id = $memberRecord->insert();
		$categoryTable = new \App\Table\Category();

		foreach ($categoryTable->getDefaults() as $categoryId)
			{
			$mc = new \App\Record\MemberCategory();
			$mc->member = $memberRecord;
			$mc->categoryId = $categoryId;
			$mc->insert();
			}
		$this->sendVerifyEmail($memberRecord);

		return (int)$id;
		}

	/**
	 * return added memberId if adding a member is allowed, else return 0
	 *
	 * @param array<string,mixed> $member
	 */
	public function addMemberToMembership(array $member) : int
		{
		$memberRecord = new \App\Record\Member(['email' => $member['email']]);

		if ($memberRecord->loaded())
			{
			\App\Model\Session::setFlash('alert', 'The email address is already in use. You may request a new password if needed.');

			return 0;
			}

		$members = $this->memberTable->membersInMembership($member['membershipId']);

		$maxMembersOnMembership = (int)$this->duesModel->MaxMembersOnMembership;
		$memberId = 0;

		if (! $maxMembersOnMembership || $maxMembersOnMembership > \count($members))
			{
			unset($member['memberId'], $member['lastLogin'], $member['password']);

			$member['verifiedEmail'] = 9;
			unset($member['volunteerPoints'], $member['acceptedWaiver']);

			$member['pendingLeader'] = 0;
			$member['deceased'] = 0;

			$memberRecord = new \App\Record\Member();
			$memberRecord->setFrom($member);
			$memberId = $memberRecord->insert();
			$this->setNormalMemberPermission($memberRecord);
			}
		else
			{
			\App\Model\Session::setFlash('alert', 'No more members are allowed on this membership');
			}

		return (int)$memberId;
		}

	public static function addYears(string $date, int $numYears) : string
		{
		// are we extending, or have we lapsed, if extending, they may be renewing early, so add to the current expiration date
		$expires = \max(\App\Tools\Date::fromString($date), \App\Tools\Date::today());
		/** @noinspection PhpUnusedLocalVariableInspection */
		[$expMonth, $expDay, $expYear] = \explode('/', \jdtogregorian($expires));
		$expYear = (int)$expYear;
		$expMonth = (int)$expMonth;
		$expYear += $numYears;

		if (++$expMonth > 12)
			{
			$expYear += 1;
			$expMonth = 1;
			}

		return \App\Tools\Date::increment(\App\Tools\Date::makeString($expYear, $expMonth, 1), -1);
		}

	public static function cleanEmail(?string $email) : string
		{
		$email = \strtolower(\trim($email ?? ''));

		// Strip dots and + in gmail domains
		if ($end = \strpos($email, '@gmail.com'))
			{
			$email = \substr($email, 0, $end);
			$email = \str_replace('.', '', $email);
			$plus = \strpos($email, '+');

			if ($plus)
				{
				$email = \substr($email, 0, $plus);
				}
			$email .= '@gmail.com';
			}

		return $email;
		}

	/**
	 * @param array<string,string> $post
	 */
	public function combineMembers(array $post) : int
		{
		$members = [];
		$memberId = (int)($post['memberId'] ?? 0);

		$membershipsCombined = null;
		$tables = null;

		$addFields = ['volunteerPoints', 'discountCount'];

		if ($memberId)
			{
			$member = new \App\Record\Member($memberId);

			foreach ($post as $key => $value)
				{
				if ($value && \str_contains($key, 'combine'))
					{
					[$junk, $combinedMemberId] = \explode('-', $key);

					if ($memberId != $combinedMemberId)
						{
						$combine = new \App\Record\Member($combinedMemberId);

						if ($combine->membershipId == $member->membershipId)
							{
							$members[] = $combinedMemberId;

							foreach ($addFields as $field)
								{
								$member->{$field} += $combine->{$field};
								}

							foreach ($member->toArray() as $key => $value)
								{
								if (empty($value))
									{
									$member->{$key} = $combine->{$key};
									}
								}
							}
						}
					}
				}
			$member->update();

			// Combine other memberId references
			$path = PROJECT_ROOT . '/App/Table/*.php';
			/**
			 * @var array<\PHPFUI\ORM\Table> $tables
			 */
			$tables = [];

			foreach (\glob($path) as $class)
				{
				$class = \str_replace(PROJECT_ROOT, '', (string)$class);
				$class = \str_replace('/', '\\', $class);
				$class = \substr($class, \strrpos($class, __NAMESPACE__));
				$class = \substr($class, 0, \strpos($class, '.'));
				/**
				 *  @var \PHPFUI\ORM\Table $table
				 */
				$table = new $class();

				$tableName = $table->getTableName();

				if (\array_key_exists('memberId', $table->getFields()) && ! \in_array($tableName, ['member', 'userPermission']))
					{
					$tables[] = $table;
					}
				}

			$userPermissionTable = new \App\Table\UserPermission();

			$newValue = ['memberId' => $memberId];

			foreach ($members as $deleteMemberId)
				{
				$condition = new \PHPFUI\ORM\Condition('memberId', $deleteMemberId);

				foreach ($tables as $table)
					{
					$table->setWhere($condition);
					$table->update($newValue);
					}
				$userPermissionTable->setWhere($condition);
				$userPermissionTable->delete();
				$this->memberTable->setWhere($condition);
				$this->memberTable->delete();
				}
			}

		return $memberId;
		}

	/**
	 * @param array<string,string> $post
	 *
	 * @return int $membershipId
	 */
	public function combineMembership(array $post) : int
		{
		$members = [];
		$membershipsCombined = [];
		$membershipId = (int)($post['membershipId'] ?? 0);

		if (! $membershipId)
			{
			return 0;
			}

		foreach ($post as $key => $value)
			{
			if ($post[$key] && \str_contains($key, 'memberId'))
				{
				[$junk, $memberId] = \explode('-', $key);
				$member = new \App\Record\Member($memberId);

				if ($member->loaded())
					{
					$members[] = $memberId;
					$membershipsCombined[] = $member->membershipId;
					$member->membershipId = $membershipId;
					$member->update();
					}
				}
			}

		// combine memberships if no members left on it
		$paymentsTable = new \App\Table\Payment();
		$pollResponseTable = new \App\Table\PollResponse();

		foreach ($membershipsCombined as $oldmembershipId)
			{
			if (! \count($this->memberTable->membersInMembership($oldmembershipId)))
				{
				$condition = new \PHPFUI\ORM\Condition('membershipId', $oldmembershipId);
				$data = ['membershipId' => $membershipId];
				$key = ['membershipId' => $oldmembershipId];
				$paymentsTable->setWhere($condition);
				$paymentsTable->update($data);
				$pollResponseTable->setWhere($condition);
				$pollResponseTable->update($data);

				// get the old membership
				$oldMembership = new \App\Record\Membership($oldmembershipId);
				// get the current membership
				$membership = new \App\Record\Membership($membershipId);
				// add in any expiration dates
				$today = \App\Tools\Date::todayString();
				$days = 0;

				if (($membership->expires ?? '') > $today)
					{
					$days += \App\Tools\Date::diff($today, $membership->expires);
					}

				if (($oldMembership->expires ?? '') > $today)
					{
					$days += \App\Tools\Date::diff($today, $oldMembership->expires);
					}
				$oldMembership->delete();

				// is there any time left on the membership?
				if ($days)
					{
					$year = (int)\App\Tools\Date::year(\App\Tools\Date::today() + $days);
					$month = (int)\App\Tools\Date::month(\App\Tools\Date::today() + $days);

					if (++$month > 12)
						{
						$month = 1;
						++$year;
						}
					$membership->expires = \App\Tools\Date::toString(\App\Tools\Date::make($year, $month, 1) - 1);
					$membership->update();
					}
				}
			}

		return $membershipId;
		}

	public function confirmEmail(string $emailHash) : bool
		{
		$additionalEmailTable = new \App\Table\AdditionalEmail();
		$condition = new \PHPFUI\ORM\Condition('memberId', \App\Model\Session::signedInMemberId());
		$condition->and('verified', 0);
		$additionalEmailTable->setWhere($condition);

		foreach ($additionalEmailTable->getRecordCursor() as $email)
			{
			if (\hash('sha512', (string)$email->email) === $emailHash)
				{
				$email->verified = 1;
				$email->update();

				return true;
				}
			}

		return false;
		}

	public function delete(int $memberId) : void
		{
		\App\Table\UserPermission::deletePermissionsForMember($memberId);
		$condition = new \PHPFUI\ORM\Condition('memberId', $memberId);

		$member = new \App\Record\Member($memberId);
		$member->delete();

		$vjs = new \App\Table\VolunteerJobShift();
		$vjs->setWhere($condition);
		$vjs->delete();

		$mc = new \App\Table\MemberCategory();
		$mc->setWhere($condition);
		$mc->delete();

		$vpr = new \App\Table\VolunteerPollResponse();
		$vpr->setWhere($condition);
		$vpr->delete();
		}

	public function emailIsUnused(string $email) : bool
		{
		$member = new \App\Record\Member(['email' => static::cleanEmail($email)]);

		return ! $member->loaded();
		}

	/**
	 * @return array<string,mixed> person with email
	 */
	public function executeInvoice(\App\Record\Invoice $invoice, \App\Record\InvoiceItem $invoiceItem, \App\Record\Payment $payment) : array
		{
		// set membership to new expiration date
		$member = $invoice->member;
		$today = \App\Tools\Date::todayString();
		$paidMembers = $this->duesModel->PaidMembers;
		$membership = new \App\Record\Membership();

		switch($invoiceItem->storeItemDetailId)
			{
			case self::EVENT_MEMBERSHIP:

				$customerModel = new \App\Model\Customer();
				$customer = $customerModel->read($invoice->memberId);
				$customer->affiliation = 'Event included / Required Membership';
				$customer->allowedMembers = 1;
				$member = $customer->getMember();
				$member->membership = $customer->getMembership();
				$invoice->member = $member;
				$invoice->update();

				if (! $payment->empty())
					{
					$payment->membership = $membership;
					$payment->update();
					}
				$this->resetPassword($customer['email']);

				// Intentionally fall through
			case self::FIRST_MEMBERSHIP:

				if ($membership->empty())
					{
					$membership = $member->membership;
					}

				$membership->pending = 0;

				if (! empty($membership->joined))
					{
					$membership->lastRenewed = $today;
					}
				else
					{
					$membership->lastRenewed = null;
					$membership->joined = $today;
					}
				$invoice->fullfillmentDate = $today;
				$invoice->update();
				$member->verifiedEmail = 9;
				$member->update();

				// if family, set to unlimited members on membership
				if ('Unlimited' == $paidMembers)
					{
					$membership->allowedMembers = (int)$this->duesModel->MaxMembersOnMembership;
					}
				else
					{
					$membership->allowedMembers = 1;
					}

				$membershipTerm = $this->duesModel->MembershipTerm;

				if ('Annual' == $membershipTerm)
					{
					$currentMonth = (int)\date('n');
					$year = (int)\date('Y');
					$startMonth = (int)$this->duesModel->MembershipStartMonth;
					$graceMonth = (int)$this->duesModel->MembershipGraceMonth;

					// no wrap over year end
					if ($startMonth < $graceMonth)
						{
						// we are renewing before grace period, set expire last year, then increment later
						++$year;

						if ($currentMonth >= $graceMonth)
							{
							++$year;
							}
						}
					else // we wrapped around year
						{
						if ($currentMonth >= $graceMonth && $currentMonth < $startMonth)	// normal, must renew at annual time
							{
							++$year;
							}
						}
					$membership->expires = \App\Tools\Date::toString(\App\Tools\Date::make($year, $startMonth, 1) - 1);
					}
				else
					{
					if (! $membership->expires)
						{
						$membership->expires = \App\Tools\Date::toString(\App\Tools\Date::endOfMonth(\App\Tools\Date::today()));
						}
					$membership->expires = static::addYears($membership->expires, $invoiceItem->quantity);
					}
				$membership->update();

				// set all members to have normal member privledge
				$this->memberTable->setWhere(new \PHPFUI\ORM\Condition('membershipId', $member->membershipId));
				$normalMember = $this->settingTable->getStandardPermissionGroup('Normal Member');
				$pendingMember = $this->settingTable->getStandardPermissionGroup('Pending Member');

				foreach ($this->memberTable->getRecordCursor() as $memberRecord)
					{
					\App\Table\UserPermission::addPermissionToUser($memberRecord->memberId, $normalMember->permissionId);
					\App\Table\UserPermission::removePermissionFromUser($memberRecord->memberId, $pendingMember->permissionId);
					$memberRecord->acceptedWaiver = null;
					$memberRecord->update();
					}

				break;

			case self::ADDITIONAL_MEMBERSHIP:

				if ($membership->empty())
					{
					$membership = $member->membership;
					}

				// if family, set to unlimited members on membership
				if ('Family' == $paidMembers || 'Unlimited' == $paidMembers)
					{
					$membership->allowedMembers = (int)$this->duesModel->MaxMembersOnMembership;
					}
				else
					{
					$membership->allowedMembers += $invoiceItem->quantity;
					}
				$membership->update();

				break;

			case self::ONE_ADDITIONAL_MEMBER:

				if ($membership->empty())
					{
					$membership = $member->membership;
					}

				++$membership->allowedMembers;
				$membership->update();

				break;

			case self::EVENT_ADDITIONAL_MEMBERSHIP:

				if ($membership->empty())
					{
					$membership = $member->membership;
					}
				$membership->allowedMembers++;
				$membership->update();

				break;

			}
		\App\Model\Session::registerMember($member);

		return $member->toArray();
		}

	/**
	 * @return array<string,string>
	 */
	public function get(int $memberId) : array
		{
		return $this->memberTable->getMembership($memberId);
		}

	/**
	 * @return array<string,\PHPFUI\ORM\FieldDefinition>
	 */
	public function getFields() : array
		{
		return $this->memberTable->getFields();
		}

	public function getNewMemberInvoice(\App\Record\Member $member) : \App\Record\Invoice
		{
		$invoice = new \App\Record\Invoice(['memberId' => $member->memberId]);

		if ($invoice->loaded())
			{
			return $invoice;
			}

		return $this->createInvoice($member, 0, 1);
		}

	public function getRenewInvoice(
		\App\Record\Member $member,
		int $additionalMembers,
		float $price,
		int $years,
		float $donation,
		string $dedication,
		\App\Record\DiscountCode $discountCode = new \App\Record\DiscountCode()
	) : \App\Record\Invoice
		{
		$invoice = new \App\Record\Invoice(['memberId' => $member->memberId,
			'orderDate' => \App\Tools\Date::todayString(),
			'paymentDate' => null,
			'totalPrice' => $price, ]);

		if ($invoice->loaded())
			{
			return $invoice;
			}

		return $this->createInvoice($member, $additionalMembers, $years, $donation, $dedication, $discountCode);
		}

	public function getVerifyCode(?string $password) : int
		{
		$verifyCode = \preg_replace('/[^0-9]*/', '', $password ?? '');
		$len = \strlen($verifyCode);
		$minLen = 5;

		if ($len > $minLen)
			{
			$verifyCode = \substr($verifyCode, $len - $minLen, $minLen);
			}

		return (int)$verifyCode;
		}

	public function hashPassword(string $password) : ?string
		{
		return \password_hash(\trim($password), PASSWORD_DEFAULT, $this->passwordOptions);
		}

	public function hasValidAddress(\App\Record\Membership $membership) : bool
		{
		$fields = ['address', 'town', 'state', 'zip'];

		foreach ($fields as $field)
			{
			if (! $membership->{$field})
				{
				return false;
				}
			}

		return true;
		}

	public function memberInMembership(\App\Record\Member $member) : bool
		{
		return $member->membershipId == \App\Model\Session::signedInMembershipId();
		}

	public function purgePending(int $days = 3) : void
		{
		$membershipTable = new \App\Table\Membership();
		$before = \App\Tools\Date::todayString(-$days);
		$condition = new \PHPFUI\ORM\Condition('joined', $before, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('pending', 1);
		$membershipTable->setWhere($condition);

		foreach ($membershipTable->getRecordCursor() as $membership)
			{
			$membership->delete();
			}
		}

	public static function replace(\App\Record\Member $old, \App\Record\Member $replaceWith) : void
		{
		foreach (\PHPFUI\ORM\Table::getAllTables() as $table)
			{
			if ('member' == $table->getTableName())
				{
				continue;
				}
			$fields = $table->getFields();

			if (\array_key_exists('memberId', $fields))
				{
				$table->setWhere(new \PHPFUI\ORM\Condition('memberId', $old->memberId));

				try
					{
					$table->update(['memberId' => $replaceWith->memberId]);
					}
				catch (\Exception $e)
					{
					$table->delete();
					}
				}
			}
		}

	public function resetPassword(string $email, bool $text = false) : void
		{
		$member = new \App\Record\Member(['email' => static::cleanEmail($email)]);

		if ($member->loaded())
			{
			$member->passwordReset = \bin2hex(\random_bytes(10));
			$member->passwordResetExpires = \date('Y-m-d H:i:s', \time() + 3600 * 24);
			$member->loginAttempts = (string)\json_encode([]);
			$member->update();
			$resetLink = $this->settingTable->value('homePage') . '/Membership/passwordNew/' . $member->memberId . '/' . $member->passwordReset;

			if ($text && $member->cellPhone)
				{
				$sms = new \App\Model\SMS("Here is your requested password reset link:\n\n{$resetLink}");
				$sms->textMember($member);
				}
			else
				{
				$email = new \App\Tools\EMail();
				$email->setSubject('Reset your ' . $this->settingTable->value('clubName') . ' password');
				$body = $this->settingTable->value('NewPasswordEmail') ?: '<a href="~password~">Click here to enter a new password</a>';
				$member->password = $resetLink;
				$email->setBody(\App\Tools\TextHelper::processText($body, $member->toArray()));
				$email->setHtml();
				$email->addToMember($member);
				$email->setFrom($this->settingTable->value('Web_MasterEmail'), $this->settingTable->value('Web_MasterName'));
				$email->send();
				}

			if ($member->verifiedEmail <= 1)
				{
				$this->sendVerifyEmail($member);
				}
			}
		}

	/**
	 * set full save to false if not validating user access to supervisor fields
	 *
	 * @param array<string,mixed> $member fields to update index by field name
	 * @param bool $fullSave save all the fields passed, if false, filter out supervisor fields
	 */
	public function saveFromPost(array $member, bool $fullSave = true) : void
		{
		if (isset($member['email']))
			{
			$member['email'] = static::cleanEmail($member['email']);
			}

		if (! empty($member['password']))
			{
			$member['password'] = $this->hashPassword($member['password']);
			}

		if (! empty($member['updateCategories']))
			{
			$permissions = new \App\Model\Permission();
			$member['rideScheduleFilter'] ? $permissions->addPermissionToUser($member['memberId'], 'Ride Schedule Filter') : $permissions->revokePermissionForUser($member['memberId'], 'Ride Schedule Filter');
			$memberCategoryTable = new \App\Table\MemberCategory();
			$memberCategoryTable->setWhere(new \PHPFUI\ORM\Condition('memberId', $member['memberId']));
			$memberCategoryTable->delete();

			foreach ($member['categories'] ?? [] as $categoryId)
				{
				$memberCategory = new \App\Record\MemberCategory();
				$memberCategory->setFrom(['memberId' => $member['memberId'], 'categoryId' => $categoryId]);
				$memberCategory->insert();
				}
			}

		if (! $fullSave)
			{
			foreach ($this->supervisorFields as $field)
				{
				unset($member[$field]);
				}
			}

		if (isset($member['volunteerPoints']))
			{
			$oldMember = new \App\Record\Member($member['memberId']);

			if ($oldMember->volunteerPoints != $member['volunteerPoints'])
				{
				$pointHistory = new \App\Record\PointHistory();
				$pointHistory->setFrom($member);
				$pointHistory->editorId = \App\Model\Session::signedInMemberId();
				$pointHistory->oldLeaderPoints = $oldMember->volunteerPoints;
				$pointHistory->insert();
				}
			}

		if (isset($member['membershipId']))
			{
			$membership = new \App\Record\Membership($member['membershipId']);
			$membership->setFrom($member);
			$membership->update();
			}

		if (! $fullSave)
			{
			unset($member['membershipId']);
			}

		$memberRecord = new \App\Record\Member($member['memberId']);
		$memberRecord->setFrom($member);
		$memberRecord->update();
		}

	public function sendVerifyEmail(\App\Record\Member $member) : void
		{
		if ($member->loaded())
			{
			$verifyCode = $this->getVerifyCode($member->password);
			$body = '<p>Thanks for your interest in joining the ' . $this->settingTable->value('clubName') . '</p>';
			$body .= '<p>We need to verify your email address.</p>';
			$home = $this->settingTable->value('homePage');
			$body .= "<p><a href='{$home}/Membership/verify/{$member->memberId}/{$verifyCode}'>Please click here to verify your email address.</a></p>";
			$body .= '<p>Once you verify your email, you can proceed with joining the club.</p>';
			$email = new \App\Tools\EMail();
			$email->setSubject('Please verify your email address for ' . $this->settingTable->value('clubName'));
			$email->setBody($body);
			$email->setHtml();
			$email->addTo($member->email);
			$memberPicker = new \App\Model\MemberPicker('Web Master');
			$membershipChair = $memberPicker->getMember();
			$email->setFromMember($membershipChair);
			$email->send();
			}
		}

	public function setDefaultFields(\App\Record\Member $member) : static
		{
		foreach ($this->defaultFields as $field)
			{
			$member->{$field} = (int)($this->settingTable->value($field . 'Default') ?: 0);
			}

		return $this;
		}

	public function setNormalMemberPermission(\App\Record\Member $member) : void
		{
		$permission = $this->settingTable->getStandardPermissionGroup('Normal Member');
		\App\Table\UserPermission::addPermissionToUser($member->memberId, $permission->permissionId);
		}

	public function setStoreItemIdType(int $type) : static
		{
		$this->storeItemIdType = $type;

		return $this;
		}

	public function signInMember(string $email, string $password) : string
		{
		$errorMessage = 'Invalid email address or password.';
		$email = static::cleanEmail($email);
		$password = \trim($password);

		if (\filter_var($email, FILTER_VALIDATE_EMAIL) && \strlen($password))
			{
			$member = new \App\Record\Member(['email' => $email]);

			if ($member->loaded())
				{
				$loginAttempts = \json_decode($member->loginAttempts ?? '', true);

				if (! \is_array($loginAttempts))
					{
					$loginAttempts = [];
					}

				$recentAttempts = [\time()];

				foreach($loginAttempts as $attempt)
					{
					if ($attempt > \time() - 300)
						{
						$recentAttempts[] = $attempt;
						}
					}

				if (\count($recentAttempts) <= 6)
					{
					$hash = $member->password ?? '';

					// Verify stored hash against plain-text password
					if (\password_verify($password, $hash))
						{
						$recentAttempts = [];
						$member->lastLogin = \date('Y-m-d H:i:s');

						// Check if a newer hashing algorithm is available or the cost has changed
						if (\password_needs_rehash($hash, PASSWORD_DEFAULT, $this->passwordOptions))
							{
							// If so, create a new hash, and replace the old one
							$member->password = $this->hashPassword($password);
							}
						\App\Model\Session::registerMember($member);
						$member->update();
						$errorMessage = '';
						}
					}
				else
					{
					$errorMessage = 'Too many login attempts. Please wait to try again.';
					}
				// save the last 20 login attempts max
				$member->loginAttempts = \json_encode(\array_slice($recentAttempts, 0, 20), JSON_THROW_ON_ERROR);
				$member->update();
				}
			}

		if ($errorMessage)
			{
			\App\Model\Session::unregisterMember();
			}

		return $errorMessage;
		}

	public function signWaiver(\App\Record\Member $member) : void
		{
		$member->acceptedWaiver = \App\Model\Session::signWaiver();
		$member->update();
		$memberWaiver = new \App\Model\MemberWaiver($member);
		$memberWaiver->emailConfirmation(true);
		}

	/**
	 * @param array<string,string> $parameters
	 */
	public function updateSubscriptions(array $parameters) : int
		{
		$updateCount = 0;

		if (isset($parameters['emails']))
			{
			$settings = [];
			$subscribe = (int)('yes' == $parameters['subscribe']);

			foreach ($parameters as $field => $value)
				{
				if ('1' == $value)
					{
					$settings[$field] = $subscribe;
					}
				}
			$emails = \explode("\n", (string)$parameters['emails']);

			foreach ($emails as $email)
				{
				$email = static::cleanEmail($email);
				$member = new \App\Record\Member(['email' => $email]);

				if ($member->loaded())
					{
					foreach ($settings as $field => $value)
						{
						$member->{$field} = $value;
						}
					$member->update();
					++$updateCount;
					}
				}
			}

		return $updateCount;
		}

	/**
	 * @return array<string> errors
	 */
	public function validatePassword(string $passwordToValidate) : array
		{
		$policy = new \App\Model\PasswordPolicy();

		$errors = $policy->validate($passwordToValidate);

		if ($errors)
			{
			\App\Model\Session::setFlash('alert', $errors);
			}

		return $errors;
		}

	public function verifyEmail(string $verifyEmail) : bool
		{
		$additionalEmailTable = new \App\Table\AdditionalEmail();
		$condition = new \PHPFUI\ORM\Condition('memberId', \App\Model\Session::signedInMemberId());
		$condition->and('verified', 0);
		$additionalEmailTable->setWhere($condition);

		foreach ($additionalEmailTable->getRecordCursor() as $additionalEmail)
			{
			if ($verifyEmail == $additionalEmail->email)
				{
				$member = \App\Model\Session::getSignedInMember();
				$club = $this->settingTable->value('clubName');
				$hash = \hash('sha512', $verifyEmail);
				$site = $this->settingTable->value('homePage');
				$link = "<a href='{$site}/Membership/confirmEmail/{$hash}'>Verify {$verifyEmail} for {$club}</a>";
				$email = new \App\Tools\EMail();
				$email->addTo($verifyEmail);
				$email->setSubject("Please verify your email address for {$club}");
				$body = "You have been asked to verify this email address for {$club}<p>" .
					'If you did not request this, you can just ignore this email. If you want to verify this email address, please click on the link below<p>' .
					$link;
				$email->setBody($body);
				$email->setHtml();
				$email->send();

				return true;
				}
			}

		return false;
		}

	public function verifyPassword(string $passwordToVerify, \App\Record\Member $member) : bool
		{
		return \password_verify($passwordToVerify, $member->password ?? \uniqid());
		}

	private function createInvoice(\App\Record\Member $member, int $additionalMembers, int $years, float $donation = 0.0, string $dedication = '', \App\Record\DiscountCode $discountCode = new \App\Record\DiscountCode()) : \App\Record\Invoice
		{
		$annualDues = $this->duesModel->getMembershipPrice(1, $years);
		$additionalMemberDues = $this->duesModel->getAdditionalMembershipPrice($additionalMembers + 1, $years);
		$totalPrice = $this->duesModel->getTotalMembershipPrice($additionalMembers + 1, $years) + $donation;
		$today = \App\Tools\Date::todayString();
		$invoice = new \App\Record\Invoice();
		$invoice->orderDate = $today;
		$invoice->member = $member;
		$invoice->totalPrice = $totalPrice;
		$invoice->totalShipping = 0.0;
		$invoice->totalTax = 0.0;
		$invoice->discountCode = $discountCode;
		$invoice->discount = 0.0;
		$invoice->paymentDate = null;
		$invoice->pointsUsed = 0;
		$invoice->paypalPaid = 0.0;
		$invoice->fullfillmentDate = null;
		$invoice->instructions = '';
		$invoiceId = $invoice->insert();

		if ($years > 0)
			{
			$invoiceItem = new \App\Record\InvoiceItem();
			$invoiceItem->invoice = $invoice;
			$invoiceItem->storeItemId = $this->storeItemIdType;
			$invoiceItem->storeItemDetailId = self::FIRST_MEMBERSHIP;
			$invoiceItem->title = self::MEMBERSHIP_TITLE;
			$invoiceItem->description = '';
			$invoiceItem->detailLine = '';
			$invoiceItem->price = (float)\number_format($annualDues / $years, 2);
			$invoiceItem->shipping = 0.0;
			$invoiceItem->quantity = $years;
			$invoiceItem->type = \App\Enum\Store\Type::MEMBERSHIP;
			$invoiceItem->tax = 0.0;
			$invoiceItem->insert();
			}

		$paidMembers = $this->duesModel->PaidMembers;

		if ('Unlimited' == $paidMembers)
			{
			$additionalMembers = 0;
			}
		elseif ('Family' == $paidMembers && $additionalMembers)
			{
			$additionalMembers = 1;
			}

		if (-1 == $years)
			{
			$invoiceItem = new \App\Record\InvoiceItem();
			$invoiceItem->invoice = $invoice;
			$invoiceItem->storeItemId = $this->storeItemIdType;
			$invoiceItem->detailLine = '';
			$invoiceItem->shipping = 0.0;
			$invoiceItem->type = \App\Enum\Store\Type::MEMBERSHIP;
			$invoiceItem->tax = 0.0;

			$invoiceItem->storeItemDetailId = self::ONE_ADDITIONAL_MEMBER;
			$invoiceItem->price = (float)\number_format($additionalMemberDues, 2);
			$invoiceItem->quantity = 1;
			$invoiceItem->title = self::MEMBERSHIP_ONE_ADDITIONAL_TITLE;
			$invoiceItem->description = ' Membership for an additional household member';
			$invoiceItem->insert();
			}
		elseif ($additionalMemberDues > 0.0 && $additionalMembers)
			{
			$invoiceItem = new \App\Record\InvoiceItem();
			$invoiceItem->invoice = $invoice;
			$invoiceItem->storeItemId = $this->storeItemIdType;
			$invoiceItem->detailLine = '';
			$invoiceItem->shipping = 0.0;
			$invoiceItem->type = \App\Enum\Store\Type::MEMBERSHIP;
			$invoiceItem->tax = 0.0;

			$invoiceItem->storeItemDetailId = self::ADDITIONAL_MEMBERSHIP;
			$invoiceItem->price = (float)\number_format($additionalMemberDues / $years / $additionalMembers, 2);
			$invoiceItem->quantity = $additionalMembers;
			$invoiceItem->title = self::MEMBERSHIP_ADDITIONAL_TITLE;
			$invoiceItem->description = 'One 12 Month Membership for an additional household member';
			$invoiceItem->insert();
			}

		if ($years > 0 && ! $discountCode->empty())
			{
			$discountCodeModel = new \App\Model\DiscountCode($discountCode);
			$invoice->discount = $discountCodeModel->computeDiscount($invoice->InvoiceItemChildren, $totalPrice);
			$invoice->update();
			}

		if ($donation)
			{
			$invoiceItem = new \App\Record\InvoiceItem();
			$invoiceItem->invoice = $invoice;
			$invoiceItem->storeItemId = $this->storeItemIdType;
			$invoiceItem->storeItemDetailId = self::DONATION;
			$invoiceItem->title = self::MEMBERSHIP_DONATION_TITLE;
			$invoiceItem->description = 'Thanks for your contribution. This is your receipt for tax purposes.';
			$invoiceItem->detailLine = $dedication;
			$invoiceItem->price = $donation;
			$invoiceItem->shipping = 0.0;
			$invoiceItem->quantity = 1;
			$invoiceItem->type = \App\Enum\Store\Type::MEMBERSHIP;
			$invoiceItem->tax = 0.0;
			$invoiceItem->insert();
			}

		return $invoice;
		}
	}
