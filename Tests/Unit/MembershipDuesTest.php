<?php

namespace Tests\Unit;

class MembershipDuesTest extends \PHPUnit\Framework\TestCase
  {
	public function testDuesFamily() : void
		{
		$membershipDues = new \App\Model\MembershipDues(false);
		$membershipDues->PaidMembers = 'Family';
		$this->assertEquals(['30'], $membershipDues->AnnualDues, '["30"] is not default value for MembershipDues::AnnualDues');

		for ($members = 1; $members < 2; ++$members)
			{
			for ($years = 0; $years < 10; ++$years)
				{
				$this->assertEquals(30.0 * $years, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}

		for ($members = 3; $members < 10; ++$members)
			{
			for ($years = 0; $years < 10; ++$years)
				{
				$this->assertEquals(30.0 * $years + 30.0 * ($members - 2) * $years, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}

		// test discounted rates
		$dues = [90, 80, 75, 65, 50, 40, 30, 20, 10, 0];
		$membershipDues->AnnualDues = $dues;

		for ($members = 1; $members < 2; ++$members)
			{
			for ($years = 1; $years < 10; ++$years)
				{
				$this->assertEquals($dues[$years - 1] * $years, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}

		for ($members = 3; $members < 10; ++$members)
			{
			for ($years = 1; $years < 10; ++$years)
				{
				$this->assertEquals($dues[$years - 1] * $years + $dues[$years - 1] * ($members - 2) * $years, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}

		// test discounted rates with additional member fees
		$dues = [90, 80, 75, 65, 50, 40, 30, 20, 10, 0];
		$membershipDues->AnnualDues = $dues;
		$additional = [10, 20, 30, 40, 50, 60, 70, 80, 90];
		$membershipDues->AdditionalMemberDues = $additional;

		for ($members = 1; $members < 2; ++$members)
			{
			for ($years = 1; $years < 10; ++$years)
				{
				$this->assertEquals($dues[$years - 1] * $years, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}

		for ($members = 3; $members < 10; ++$members)
			{
			for ($years = 1; $years < 10; ++$years)
				{
				$this->assertEquals($dues[$years - 1] * $years + $additional[$years - 1] * ($members - 2) * $years, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}
		}

	public function testDuesPaid() : void
		{
		$membershipDues = new \App\Model\MembershipDues(false);
		$membershipDues->PaidMembers = 'Paid';
		$this->assertEquals(['30'], $membershipDues->AnnualDues, '["30"] is not default value for MembershipDues::AnnualDues');

		for ($members = 1; $members < 10; ++$members)
			{
			for ($years = 0; $years < 10; ++$years)
				{
				$this->assertEquals(30.0 * $years * $members, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}

		$membershipDues->AdditionalMemberDues = ['10'];

		for ($members = 1; $members < 10; ++$members)
			{
			for ($years = 0; $years < 10; ++$years)
				{
				$additional = 10.0 * ($members - 1) * $years;
				$this->assertEquals(30.0 * $years + $additional, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership with $10 additional member price");
				}
			}

		// test multi year discounts
		$dues = [90, 80, 70, 60, 50, 40, 30, 20, 10, 5];
		$membershipDues->AnnualDues = $dues;
		$additional = [];
		$membershipDues->AdditionalMemberDues = $additional;

		for ($members = 1; $members < 10; ++$members)
			{
			for ($years = 1; $years < 10; ++$years)
				{
				$this->assertEquals($dues[$years - 1] * $years * $members, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}

		// test multi year discounts
		$dues = [90, 80, 70, 60, 50, 40, 30, 20, 10, 5];
		$membershipDues->AnnualDues = $dues;
		$additional = [10, 20, 30, 40, 50, 60, 70, 80, 90];
		$membershipDues->AdditionalMemberDues = $additional;

		for ($members = 1; $members < 10; ++$members)
			{
			for ($years = 1; $years < 10; ++$years)
				{
				$this->assertEquals($dues[$years - 1] * $years + $additional[$years - 1] * ($members - 1) * $years, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}

		}

  public function testDuesUnlimited() : void
		{
		$membershipDues = new \App\Model\MembershipDues(false);
		$this->assertEquals('Unlimited', $membershipDues->PaidMembers, 'Unlimited is not default value for MembershipDues::PaidMembers');
		$this->assertEquals(['30'], $membershipDues->AnnualDues, '["30"] is not default value for MembershipDues::AnnualDues');
		// this should not affect anything
		$membershipDues->AdditionalMemberDues = [1, 2, 3];

		for ($members = 0; $members < 10; ++$members)
			{
			for ($years = 0; $years < 10; ++$years)
				{
				$this->assertEquals(30.0 * $years, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}
		// test discounted additional years
		$dues = [30, 30, 25, 25, 20, 20, 15, 15, 10, 10];
		$membershipDues->AnnualDues = $dues;

		for ($members = 0; $members < 10; ++$members)
			{
			for ($years = 1; $years < 10; ++$years)
				{
				$this->assertEquals($dues[$years - 1] * $years, $membershipDues->getTotalMembershipPrice($members, $years), "Bad price for {$members} members and {$years} years of {$membershipDues->PaidMembers} membership");
				}
			}
		}
  }
