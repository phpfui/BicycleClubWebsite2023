<?php

$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include 'common.php';

//$qrCode = new \Endroid\QrCode\QrCode(
//	data: $url,
//	encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
//	errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::High,
//	size: 1200,
//	margin: 10,
//	roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin,
//	foregroundColor: new \Endroid\QrCode\Color\Color(0, 0, 0),
//	backgroundColor: new \Endroid\QrCode\Color\Color(255, 255, 255),
//	);
//
//$writer = new \Endroid\QrCode\Writer\PngWriter();
//$result = $writer->write($qrCode);
//file_put_contents('QRCode.png', $result->getString());
//
//echo "QRCode.png created for $url\n";

$memberModel = new \App\Model\Member();

$gaEvent = new \App\Record\GaEvent((int)($argv[2] ?? 45));

$settingTable = new \App\Table\Setting();
$normalMember = $settingTable->getStandardPermissionGroup('Normal Member');
$pendingMember = $settingTable->getStandardPermissionGroup('Pending Member');

$gaRiderTable = new \App\Table\GaRider();
$gaRiderTable->addSelect('gaRider.*');
$gaRiderTable->addSelect('member.membershipId');
$gaRiderTable->addJoin('gaEvent');
$gaRiderTable->addJoin('member');
$gaRiderTable->addJoin('membership', new \PHPFUI\ORM\Condition('membership.membershipId', new \PHPFUI\ORM\Literal('member.membershipId')));

$condition = new \PHPFUI\ORM\Condition('gaRider.gaEventid', $gaEvent->gaEventId);
$membershipCondition = new \PHPFUI\ORM\Condition('member.membershipId', null);
$membershipCondition->or('membership.pending', 0, new \PHPFUI\ORM\Operator\GreaterThan());
$condition->and($membershipCondition);

$gaRiderTable->setWhere($condition);

foreach ($gaRiderTable->getRecordCursor() as $gaRider)
	{
	$member = $gaRider->member;
	if ($member->loaded())
		{
		echo 'found ' . $member->fullName();
		$membership = $member->membership;
		if ($membership->loaded())
			{
			echo " with membership\n";
			}
		else
			{
			echo " MISSING  membership\n";
			$membership = new \App\Record\Membership();
			$membership->setFrom($gaRider->toArray());
			}
		}
	else
		{
		echo $gaRider->fullName() . " has no member\n";
		$member = new \App\Record\Member();
		$member->setFrom($gaRider->toArray());
		$membership = new \App\Record\Membership();
		$membership->setFrom($gaRider->toArray());
		}
	$membership->expires = $gaEvent->membershipExpires;
	$membership->pending = 0;
	$membership->joined = $gaRider->signedUpOn;
	$membership->affiliation = $gaEvent->title;
	$membership->allowedMembers = 1;
	$membership->insertOrUpdate();
	$member->membership = $membership;
	$member->cellPhone = $member->phone;
	$member->emergencyContact = $gaRider->contact;
	$member->emergencyPhone = $gaRider->contactPhone;
	$member->verifiedEmail = 9;
	$memberModel->setDefaultFields($member);
	$member->insertOrUpdate();

	$memberModel->setNormalMemberPermission($member);

	$gaRider->member = $member;
	$gaRider->update();
	}

//echo $gaRiderTable->getLastSQL();
