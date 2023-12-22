<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

function cleanEmail(string $email) : string
	{
	$email = \trim(\strtolower($email));

	if (\str_ends_with($email, '@gmail.com'))
		{
		$email = \str_replace('.', '', $email);
		$email = \str_replace('@gmailcom', '@gmail.com', $email);
		}

	if (! \filter_var($email, FILTER_VALIDATE_EMAIL))
		{
		$email = '';
		}

	return $email;
	}

$dataPurger = new \App\Model\DataPurge();
$dataPurger->addExceptionTable(new \App\Table\Setting());
$dataPurger->addExceptionTable(new \App\Table\Blog());
$dataPurger->addExceptionTable(new \App\Table\Story());
$dataPurger->addExceptionTable(new \App\Table\Permission());
$dataPurger->addExceptionTable(new \App\Table\PermissionGroup());
$dataPurger->addExceptionTable(new \App\Table\UserPermission());
$dataPurger->purge();

foreach (\glob(__DIR__ . '/*.csv') as $file)
	{
	echo "Importing file: {$file}\n";
	$insertedCount = \insertMembers($file);
	echo "Imported {$insertedCount} members\n";
	}
echo "Done importing\n";

function insertMembers(string $csvName) : int
	{
	$memberNames = [];
	$members = new \App\Tools\CSV\FileReader($csvName);
	$insertedCount = 0;

	foreach ($members as $row)
		{
		if ('PAID' != $row['Financial Status'])
			{
			continue;
			}
		$paidAt = \substr($row['Paid at'], 0, 10);

		$membership = new \App\Record\Membership();
		$membership->address = $row['Billing Address1'];
		$membership->town = $row['Billing City'];
		$membership->zip = $row['Billing Zip'];
		$membership->state = $row['Billing Province'];
		$membership->pending = 0;
		$membership->allowedMembers = 1;
		$membership->affiliation = $row['Product Form: How did you hear about the MHBC?'];
		$year = (int)\substr($paidAt, 0, 4);
		++$year;
		$expireDate = (string)$year . \substr($paidAt, 4);
		$expired = \App\Tools\Date::fromString($expireDate);
		$expired = \App\Tools\Date::endOfMonth($expired);
		$membership->expires = \App\Tools\Date::toString($expired);
		$newMember = 'Yes' == $row['Product Form: New Member?'];
		$membership->joined = $paidAt;

		if (! $newMember)
			{
			$membership->lastRenewed = $paidAt;
			$membership->joined = \App\Tools\Date::toString(\App\Tools\Date::fromString($paidAt) - 365);
			}

	//	echo "paid at $paidAt - expires {$membership->expires} joined {$membership->joined} - lastRenewed {$membership->lastRenewed} new {$row['Product Form: New Member?']}\n";
	//	continue;

		$member = new \App\Record\Member();
		$privacy = (int)('No' == $row['Product Form: Email and phone number listed on the site?']);
		\setName($row['Product Form: Name'], $member);
		$member->acceptedWaiver = $row['Created at'];
		$member->allowTexting = 1;
		$member->cellPhone = $row['Product Form: Phone'];
		$member->deceased = 0;
		$member->email = \cleanEmail($row['Product Form: Email']);
		$member->emergencyContact = $row['Product Form: Emergency Contact Name'];
		$member->emergencyPhone = $row['Product Form: Emergency Contact Phone Number'];
		$member->emailAnnouncements = 1;
		$member->emailNewsletter = 1;
		$member->geoLocate = 1;
		$member->journal = 1;
		$member->volunteerPoints = 0;
		$member->newRideEmail = 1;
		$member->pendingLeader = 0;
		$member->rideComments = 1;
		$member->rideJournal = 1;
		$member->showNoPhone = $privacy;
		$member->showNoStreet = $privacy;
		$member->showNoTown = 0;
		$member->showNothing = 0;
		$member->verifiedEmail = 9;
		$member->membership = $membership;
		$memberNames[$member->fullName()] = true;
		++$insertedCount;

		$userPermission = new \App\Record\UserPermission();
		$userPermission->member = $member;
		$userPermission->permissionGroup = 6; // normal member
		$userPermission->insert();

		$leader = 'Yes' == $row['Product Form: Ride Leader'];

		if ($leader)
			{
			$userPermission = new \App\Record\UserPermission();
			$userPermission->member = $member;
			$userPermission->permissionGroup = 2;
			$userPermission->insert();
			}

		$membersOnMembership = 1;

		for ($i = 1; $i <= 5; ++$i)
			{
			$key = 'Product Form: #' . $i;

			if (\array_key_exists($key, $row))
				{
				$memberName = $row[$key];

				if (empty($memberName))
					{
					continue;
					}
				$newMember = new \App\Record\Member();
				$newMember->setFrom($member->toArray());
				\setName($memberName, $newMember);

				if (isset($memberNames[$newMember->fullName()]))
					{
					continue;
					}

				$memberNames[$member->fullName()] = true;
				$newMember->memberId = null;
				$newMember->cellPhone = '';
				$newMember->email = null;
				$newMember->acceptedWaiver = null;
				$newId = $newMember->insert();
				++$insertedCount;

				$userPermission = new \App\Record\UserPermission();
				$userPermission->member = $newMember;
				$userPermission->permissionGroup = 6; // normal member
				$userPermission->insert();
				++$membersOnMembership;
				}
			}

		if ($membersOnMembership != $membership->allowedMembers)
			{
			$membership->allowedMembers = 10;
			$membership->update();
			}

		$total = (float)$row['Total'];
		$invoice = new \App\Record\Invoice();
		$invoice->discount = (float)$row['Discount Amount'];
		$invoice->fullfillmentDate = $invoice->orderDate = $invoice->paymentDate = $paidAt;
		$invoice->member = $member;
		$invoice->paidByCheck = 0;
		$invoice->pointsUsed = 0;
		$invoice->paypalPaid = $total;
		$invoice->paypaltx = $row['Payment Reference'];
		$invoice->totalPrice = $total;
		$invoice->totalShipping = (float)$row['Shipping'];
		$invoice->totalTax = (float)$row['Taxes'];

		$invoiceItem = new \App\Record\InvoiceItem();
		$invoiceItem->description = $row['Lineitem name'];
		$invoiceItem->detailLine = $row['Lineitem sku'];
		$invoiceItem->invoice = $invoice;
		$invoiceItem->price = (float)$row['Lineitem price'];
		$invoiceItem->quantity = (int)$row['Lineitem quantity'];
		$invoiceItem->shipping = (float)$row['Shipping'];
		$invoiceItem->tax = (float)$row['Taxes'];
		$invoiceItem->title = 'Membership';
		$invoiceItem->type = \App\Model\Cart::TYPE_MEMBERSHIP;
		$invoiceItem->insert();

		$payment = new \App\Record\Payment();
		$payment->membership = $membership;
		$payment->amount = $total;
		$payment->paymentNumber = $row['Payment Reference'];
		$payment->dateReceived = $payment->paymentDated = $paidAt;
		$payment->invoice = $invoice;
		$payment->paymentType = 4;
		$payment->insert();
		}

	return $insertedCount;
	}

function setName(string $name, \App\Record\Member $member) : void
	{
	$parts = \explode(' ', $name);
	$first = $last = [];
	$first[] = \array_shift($parts);

	foreach ($parts as $part)
		{
		if (1 == \strlen($part))
			{
			$first[] = $part;

			continue;
			}

		if (\str_contains($part, '.'))
			{
			$first[] = $part;
			}
		else
			{
			$last[] = $part;
			}
		}
	$member->firstName = \implode(' ', $first);
	$member->lastName = \implode(' ', $last);
	}
