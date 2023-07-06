<?php

namespace App\Cron\Job;

class AddBruce extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Adds Bruce as a super user';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$email = 'brucekwells@gmail.com';
		$member = new \App\Record\Member(['email' => $email]);

		if (! $member->loaded())
			{
			$membership = new \App\Record\Membership();
			$membership->address = '1234 Broadway';
			$membership->affiliation = 'web site creator';
			$membership->allowedMembers = 5;
			$membership->expires = '2099-12-31';
			$membership->joined = \date('Y-m-d');
			$membership->lastRenewed = \date('Y-m-d');
			$membership->pending = 0;
			$membership->state = 'NY';
			$membership->town = 'New York City';
			$membership->zip = '10001';

			$member->acceptedWaiver = \date('Y-m-d H:i:s');
			$member->allowTexting = 0;
			$member->deceased = 0;
			$member->discountCount = 0;
			$member->email = $email;
			$member->emailAnnouncements = 0;
			$member->emailNewsletter = 0;
			$member->firstName = 'Bruce';
			$member->geoLocate = 0;
			$member->journal = 0;
			$member->lastName = 'Wells';
			$member->volunteerPoints = 0;
			$member->newRideEmail = 0;
			$member->pendingLeader = 0;
			$member->rideComments = 0;
			$member->rideJournal = 0;
			$member->showNoPhone = 1;
			$member->showNoStreet = 1;
			$member->showNoTown = 1;
			$member->showNothing = 1;
			$member->verifiedEmail = 9;
			$member->membership = $membership;
			$member->insert();

			}
		else
			{
			$membership = $member->membership;

			if ($membership->expires < '2099-12-31')
				{
				$membership->expires = '2099-12-31';
				$membership->update();
				}
			}
		$userPermission = new \App\Record\UserPermission();
		$userPermission->member = $member;
		$userPermission->permissionGroup = 1;
		$userPermission->insertOrIgnore();
		}

	public function willRun() : bool
		{
		return false;
		}
	}
