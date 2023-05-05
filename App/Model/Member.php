<?php

namespace App\Model;

class Member
	{
	final public const ADDITIONAL_MEMBERSHIP = 2;

	final public const DONATION = 3;

	final public const EVENT_ADDITIONAL_MEMBERSHIP = 5;

	final public const EVENT_MEMBERSHIP = 4;

	final public const FIRST_MEMBERSHIP = 1;

	final public const MEMBERSHIP_ADDITIONAL_TITLE = '12 Month Membership Additional Member';

	final public const MEMBERSHIP_DONATION_TITLE = 'Additional Donation';

	final public const MEMBERSHIP_TITLE = '12 Month Membership';

	protected array $passwordOptions = ['cost' => 11];

	protected array $supervisorFields = [
		'lastLogin',
		'allowedMembers',
		'leaderPoints',
		'acceptedWaiver',
		'pending',
		'pendingLeader',
		'deceased',
		'expires',
		'joined',
		'affiliation',
		'lastRenewed',
	];

	private readonly \App\Table\Member $memberTable;

	private readonly \App\Table\Setting $settingTable;

	public function __construct()
		{
		$this->memberTable = new \App\Table\Member();
		$this->settingTable = new \App\Table\Setting();
		}

	public function add(array $member) : int
		{
		$member['pendingLeader'] = 0;
		$member['pending'] = 1;
		unset($member['memberId'], $member['membershipId']);

		$member['email'] = static::cleanEmail($member['email']);
		$member['firstName'] = \ucwords((string)$member['firstName']);
		$member['lastName'] = \ucwords((string)$member['lastName']);
		$member['expires'] = $member['lastRenewed'] = 0;
		$member['joined'] = \App\Tools\Date::todayString();
		$defaultFields = ['rideJournal', 'newRideEmail', 'emailNewsletter', 'emailAnnouncements', 'journal'];

		foreach ($defaultFields as $field)
			{
			$member[$field] = $this->settingTable->value($field . 'Default') ?: 0;
			}
		$member['acceptedWaiver'] = null;
		$member['password'] = $this->hashPassword($member['password']);
		$membership = new \App\Record\Membership();
		$membership->setFrom($member);
		$member['membershipId'] = $membership->insert();
		$memberRecord = new \App\Record\Member();
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

		return $id;
		}

	/**
	 * return added memberId if adding a member is allowed, else return 0
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

		$maxMembersOnMembership = (int)$this->settingTable->value('maxMembersOnMembership');
		$memberId = 0;

		if (! $maxMembersOnMembership || $maxMembersOnMembership > \count($members))
			{
			unset($member['memberId'], $member['lastLogin'], $member['password']);

			$member['verifiedEmail'] = 9;
			unset($member['leaderPoints'], $member['acceptedWaiver']);

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

		return $memberId;
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

	public static function cleanEmail(string $email) : string
		{
		$email = \strtolower(\trim($email));
		// Strip dots in gmail domains
		if ($end = \strpos($email, '@gmail.com'))
			{
			$email = \str_replace('.', '', $email);
			$email = \str_replace('@gmailcom', '@gmail.com', $email);
			}

		return $email;
		}

	public function combineMembers(array $post) : int
		{
		$members = [];
		$memberId = $post['memberId'] ?? 0;

		$membershipsCombined = null;
		$tables = null;

		if ($memberId)
			{
			$member = new \App\Record\Member($memberId);

			foreach ($post as $key => $value)
				{
				if (\str_contains($key, 'combine'))
					{
					[$junk, $combinedMemberId] = \explode('-', $key);

					if ($memberId != $combinedMemberId)
						{
						$combine = new \App\Record\Member($combinedMemberId);

						if ($combine->membershipId == $member->membershipId)
							{
							$members[] = $memberId;

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
			$tables[] = new \App\Table\AuditTrail();
			$tables[] = new \App\Table\CartItem();
			$tables[] = new \App\Table\Invoice();
			$tables[] = new \App\Table\PollResponse();
			$tables[] = new \App\Table\Reservation();
			$tables[] = new \App\Table\VolunteerJobShift();
			$tables[] = new \App\Table\VolunteerPollResponse();
			$tables[] = new \App\Table\AdditionalEmail();
			$tables[] = new \App\Table\AssistantLeader();
			$tables[] = new \App\Table\BoardMember();
			$tables[] = new \App\Table\ForumMember();
			$tables[] = new \App\Table\ForumMessage();
			$tables[] = new \App\Table\GaRider();
			$tables[] = new \App\Table\MemberCategory();
			$tables[] = new \App\Table\MemberOfMonth();
			$tables[] = new \App\Table\RideComment();
			$tables[] = new \App\Table\Ride();
			$tables[] = new \App\Table\RideSignup();
			$tables[] = new \App\Table\SigninSheet();
			$tables[] = new \App\Table\CueSheet();
			$tables[] = new \App\Table\Poll();
			$tables[] = new \App\Table\SlideShow();

			$userPermissionTable = new \App\Table\UserPermission();

			$newValue = ['memberId' => $memberId];

			foreach ($members as $deleteMemberId)
				{
				$key = ['memberId' => $deleteMemberId];
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
	 * @return int $membershipId
	 */
	public function combineMembership(array $post) : int
		{
		$members = [];
		$membershipsCombined = [];
		$membershipId = $post['membershipId'] ?? 0;

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
					$year = \App\Tools\Date::year(\App\Tools\Date::today() + $days);
					$month = \App\Tools\Date::month(\App\Tools\Date::today() + $days);

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
			if ($emailHash == \hash('sha512', (string)$email->email))
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

	public function executeInvoice(\App\Record\Invoice $invoice, \App\Record\InvoiceItem $invoiceItem, \App\Record\Payment $payment) : array
		{
		// set membership to new expiration date
		$member = $invoice->member;
		$today = \App\Tools\Date::todayString();
		$paidMembers = $this->settingTable->value('PaidMembers');
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
					$membership->allowedMembers = (int)$this->settingTable->value('maxMembersOnMembership');
					}
				else
					{
					$membership->allowedMembers = 1;
					}

				$membershipTerm = $this->settingTable->value('MembershipTerm');

				if ('Annual' == $membershipTerm)
					{
					$currentMonth = (int)\date('n');
					$year = (int)\date('Y');
					$startMonth = (int)$this->settingTable->value('MembershipStartMonth');
					$graceMonth = (int)$this->settingTable->value('MembershipGraceMonth');
					// no wrap over year end
					if ($startMonth < $graceMonth)
						{
						// we are renewing before grace period, set expire last year, then increment later
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
					}
				$membership->expires = static::addYears($membership->expires, $invoiceItem->quantity);
				$membership->update();

				// set all members to have normal member privledge
				$this->memberTable->setWhere(new \PHPFUI\ORM\Condition('membershipId', $member->membershipId));
				$normalMember = new \App\Record\Permission(['name' => 'Normal Member']);
				$pendingMember = new \App\Record\Permission(['name' => 'Pending Member']);

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
					$membership->allowedMembers = (int)$this->settingTable->value('maxMembersOnMembership');
					}
				else
					{
					$membership->allowedMembers += $invoiceItem->quantity;
					}
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

	public function get(int $memberId) : array
		{
		return $this->memberTable->getMembership($memberId);
		}

	public function getFields() : array
		{
		return $this->memberTable->getFields();
		}

	public function getMembershipPrice(int $members, int $years = 1) : float
		{
		$membershipType = $this->settingTable->value('MembershipType');
		$dues = 'Subscription' == $membershipType ? 'subscriptionDues' : 'annualDues';
		$duesPrice = (float)$this->settingTable->value($dues);
		$additionalDues = (float)$this->settingTable->value('additionalMemberDues');

		if ($additionalDues > 0)
			{
			$maxMembers = (int)$this->settingTable->value('maxMembersOnMembership');
			$paidMembers = $this->settingTable->value('PaidMembers');

			if ('Family' == $paidMembers)
				{
				$maxMembers = 2;
				}

			if ($maxMembers > 0)
				{
				$members = \min($members, $maxMembers);
				}
			}

		return ($duesPrice + ($members - 1) * $additionalDues) * $years;
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

	public function getRandomPassword() : string
		{
		\mt_srand();
		$newPassword = \random_int(0, 99999);

		while (\strlen($newPassword) < 5)
			{
			$newPassword .= \random_int(0, 9);
			}

		return 'Password-' . $newPassword;
		}

	public function getRenewInvoice(\App\Record\Member $member, int $additionalMembers, float $price, int $years, float $donation, string $dedication) : \App\Record\Invoice
		{
		$invoice = new \App\Record\Invoice(['memberId' => $member->memberId,
			'orderDate' => \App\Tools\Date::todayString(),
			'paymentDate' => 0,
			'totalPrice' => $price, ]);

		if ($invoice->loaded())
			{
			return $invoice;
			}

		return $this->createInvoice($member, $additionalMembers, $years, $donation, $dedication);
		}

	public function getVerifyCode(string $password) : int
		{
		$verifyCode = \preg_replace('/[^0-9]*/', '', $password);
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

	public function resetPassword(string $email, bool $text = false) : void
		{
		$member = new \App\Record\Member(['email' => static::cleanEmail($email)]);

		if ($member->loaded())
			{
			$member->passwordReset = \bin2hex(\random_bytes(10));
			$member->passwordResetExpires = \date('Y-m-d H:i:s', \time() + 3600 * 24);
			$member->loginAttempts = \json_encode([]);
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
				$email->addToMember($member->toArray());
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
	 * @param array $member fields to update index by field name
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

		if (isset($member['leaderPoints']))
			{
			$oldMember = new \App\Record\Member($member['memberId']);

			if ($oldMember->leaderPoints != $member['leaderPoints'])
				{
				$pointHistory = new \App\Record\PointHistory();
				$pointHistory->setFrom($member);
				$pointHistory->editorId = \App\Model\Session::signedInMemberId();
				$pointHistory->oldLeaderPoints = $oldMember->leaderPoints;
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
			$body .= "<p><a href='{$home}/Membership/verify/{$member->memberId}/{$verifyCode}'>Please click here to everify your email address.</a></p>";
			$body .= '<p>Once you verify your email, you can proceed with joining the club.</p>';
			$email = new \App\Tools\EMail();
			$email->setSubject('Please verify your email address for ' . $this->settingTable->value('clubName'));
			$email->setBody($body);
			$email->setHtml();
			$email->addTo($member->email);
			$memberPicker = new \App\Model\MemberPicker('Membership Chair');
			$membershipChair = $memberPicker->getMember();
			$email->setFromMember($membershipChair);
			$email->send();
			}
		}

	public function setNormalMemberPermission(\App\Record\Member $member) : void
		{
		$permission = new \App\Record\Permission(['name' => 'Normal Member']);
		\App\Table\UserPermission::addPermissionToUser($member->memberId, $permission->permissionId);
		}

	public function signInMember(string $email, string $password) : array
		{
		$returnValue = [];
		$message = 'Invalid email address or password.';
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
						$returnValue = $member->toArray();
						}
					}
				else
					{
					$message = 'Too many login attempts. Please wait to try again.';
					}
				// save the last 20 login attempts max
				$member->loginAttempts = \json_encode(\array_slice($recentAttempts, 0, 20), JSON_THROW_ON_ERROR);
				$member->update();
				}
			}

		if (! $returnValue)
			{
			$returnValue['error'] = $message;
			\App\Model\Session::unregisterMember();
			}

		return $returnValue;
		}

	public function signWaiver(\App\Record\Member $member) : void
		{
		$member->acceptedWaiver = \App\Model\Session::signWaiver();
		$member->update();
		$memberWaiver = new \App\Model\MemberWaiver($member);
		$memberWaiver->emailConfirmation(true);
		}

	/**
	 *
	 * @psalm-return 0|positive-int
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
		return \password_verify($passwordToVerify, $member->password);
		}

	private function createInvoice(\App\Record\Member $member, int $additionalMembers, int $years, float $donation = 0.0, string $dedication = '') : \App\Record\Invoice
		{
		$annualDues = (float)$this->settingTable->value('annualDues');
		$additionalMemberDues = (float)$this->settingTable->value('additionalMemberDues');
		$totalPrice = $annualDues * $years + $additionalMembers * $years * $additionalMemberDues + $donation;
		$today = \App\Tools\Date::todayString();
		$invoice = new \App\Record\Invoice();
		$invoice->orderDate = $today;
		$invoice->member = $member;
		$invoice->totalPrice = $totalPrice;
		$invoice->totalShipping = 0.0;
		$invoice->totalTax = 0.0;
		$invoice->discount = 0.0;
		$invoice->paymentDate = null;
		$invoice->pointsUsed = 0;
		$invoice->paypalPaid = 0.0;
		$invoice->fullfillmentDate = null;
		$invoice->instructions = '';
		$invoiceId = $invoice->insert();

		if ($years)
			{
			$invoiceItem = new \App\Record\InvoiceItem();
			$invoiceItem->invoice = $invoice;
			$invoiceItem->storeItemId = 1;
			$invoiceItem->storeItemDetailId = self::FIRST_MEMBERSHIP;
			$invoiceItem->title = self::MEMBERSHIP_TITLE;
			$invoiceItem->description = '';
			$invoiceItem->detailLine = '';
			$invoiceItem->price = (float)$annualDues;
			$invoiceItem->shipping = 0.0;
			$invoiceItem->quantity = $years;
			$invoiceItem->type = \App\Model\Cart::TYPE_MEMBERSHIP;
			$invoiceItem->tax = 0.0;
			$invoiceItem->insert();
			}

		$paidMembers = $this->settingTable->value('PaidMembers');

		if ('Unlimited' == $paidMembers)
			{
			$additionalMembers = 0;
			}
		elseif ('Family' == $paidMembers && $additionalMembers)
			{
			$additionalMembers = 1;
			}

		if ($additionalMemberDues > 0.0 && $additionalMembers)
			{
			$invoiceItem = new \App\Record\InvoiceItem();
			$invoiceItem->invoice = $invoice;
			$invoiceItem->storeItemId = 1;
			$invoiceItem->detailLine = '';
			$invoiceItem->shipping = 0.0;
			$invoiceItem->type = \App\Model\Cart::TYPE_MEMBERSHIP;
			$invoiceItem->tax = 0.0;

			$invoiceItem->storeItemDetailId = self::ADDITIONAL_MEMBERSHIP;
			$invoiceItem->price = $additionalMemberDues;
			$invoiceItem->quantity = $additionalMembers;
			$invoiceItem->title = self::MEMBERSHIP_ADDITIONAL_TITLE;
			$invoiceItem->description = 'One 12 Month Membership for an additional household member';
			$invoiceItem->insert();
			}

		if ($donation)
			{
			$invoiceItem = new \App\Record\InvoiceItem();
			$invoiceItem->invoice = $invoice;
			$invoiceItem->storeItemId = 1;
			$invoiceItem->storeItemDetailId = self::DONATION;
			$invoiceItem->title = self::MEMBERSHIP_DONATION_TITLE;
			$invoiceItem->description = 'Thanks for your contribution. This is your receipt for tax purposes.';
			$invoiceItem->detailLine = $dedication;
			$invoiceItem->price = $donation;
			$invoiceItem->shipping = 0.0;
			$invoiceItem->quantity = 1;
			$invoiceItem->type = \App\Model\Cart::TYPE_MEMBERSHIP;
			$invoiceItem->tax = 0.0;
			$invoiceItem->insert();
			}

		return $invoice;
		}
	}
