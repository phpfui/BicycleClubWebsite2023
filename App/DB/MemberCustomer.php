<?php

namespace App\DB;

/**
 * @property string $address
 * @property string $affiliation
 * @property int $allowedMembers
 * @property int $customerId
 * @property string $email
 * @property string $firstName
 * @property string $lastName
 * @property int $memberId
 * @property string $state
 * @property string $town
 * @property string $zip
 */
class MemberCustomer
	{
	private \App\Record\Customer $customer;

	private \App\Record\Member $member;

	private \App\Record\Membership $membership;

	public function __construct(int $primaryKey)
		{
		if ($primaryKey > 0)
			{
			$this->member = new \App\Record\Member($primaryKey);
			$this->membership = $this->member->membership;
			$this->customer = new \App\Record\Customer();
			}
		else
			{
			$this->customer = new \App\Record\Customer(0 - $primaryKey);
			$this->member = new \App\Record\Member();
			$this->membership = new \App\Record\Membership();
			}
		}

	public function __get(string $field)
		{
		if ($this->member->loaded())
			{
			if (isset($this->member->{$field}))
				{
				return $this->member->{$field};
				}

			return $this->membership->{$field};
			}

		return $this->customer->{$field};
		}

	/**
	 * Allows for empty($object->field) to work correctly
	 */
	public function __isset(string $field) : bool
		{
		if ($this->member->loaded())
			{
			if (isset($this->member->{$field}))
				{
				return true;
				}

			return isset($this->membership->{$field});
			}

		return isset($this->customer->{$field});
		}

	public function __set(string $field, $value)
		{
		if ($this->member->loaded())
			{
			if (isset($this->member->{$field}))
				{
				$this->member->{$field} = $value;
				}
			else
				{
				$this->membership->{$field} = $value;
				}
			}
		else
			{
			$this->customer->{$field} = $value;
			}

		return $value;
		}

	public function getCustomer() : \App\Record\Customer
		{
		return $this->customer;
		}

	public function getMember() : \App\Record\Member
		{
		if (! $this->member->loaded())
			{
			$this->member->setFrom($this->customer->toArray());
			}

		return $this->member;
		}

	public function getMembership() : \App\Record\Membership
		{
		if (! $this->membership->loaded())
			{
			$this->membership->setFrom($this->customer->toArray());
			}

		return $this->membership;
		}

	public function save(array $fields) : void
		{
		if ($this->member->loaded())
			{
			foreach ($fields as $field => $value)
				{
				$this->{$field} = $value;
				}
			$this->member->update();
			$this->membership->update();
			}
		}

	public function toArray() : array
		{
		if ($this->member->loaded())
			{
			return \array_merge($this->member->toArray(), $this->membership->toArray());
			}

		return $this->customer->toArray();
		}

	public function update() : bool
		{
		if ($this->member->loaded())
			{
			$this->member->update();

			return $this->membership->update();
			}

		return $this->customer->update();
		}

	public function validate() : array
		{
		if ($this->member->loaded())
			{
			return \array_merge($this->member->validate(), $this->membership->validate());
			}

		return $this->customer->validate();
		}
	}
