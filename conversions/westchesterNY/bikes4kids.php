<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$sql = 'select r.id,r.rideDate,r.title,rs.memberId,m.firstName,m.lastName from rides r left join ridesignup rs on rs.rideId=r.id left join members m on m.memberId=rs.memberId where r.title like "%bikes4kids%" and r.title not like "%cancel%" and r.rideDate>=? and rs.attended=2 and r.rideStatus!=1;';

$bikes4kids = \PHPFUI\ORM::getRows($sql, [2457689]);

$rideId = 0;

$jobEventTable = new \App\CRUD\JobEvent();
$jobsTable = new \App\CRUD\Jobs();
$jobShiftsTable = new \App\CRUD\JobShifts();
$rideTable = new \App\CRUD\Ride();
$volunteerJobShift = new \App\CRUD\VolunteerJobShift();

$job = ['date' => 0, 'title' => 'Mechanic', 'location' => '169 Theodore Fremd Avenue, Rye NY',
	'description' => 'Work on bikes', 'organizer' => 4000, 'jobEventId' => 0, ];
$shift = ['jobId' => 0, 'startTime' => '6pm', 'endTime' => '9pm', 'needed' => 10, ];

$jobId = null;
$jobShiftId = null;

foreach ($bikes4kids as $bikes4kid)
	{
	if ($bikes4kid['id'] != $rideId)
		{
		$rideId = $bikes4kid['id'];
		$jobEvent = ['name' => $bikes4kid['title'], 'cutoffDate' => $bikes4kid['rideDate'], 'date' => $bikes4kid['rideDate'], 'organizer' => 4000];
		$jobEventId = $jobEventTable->insert($jobEvent);
		$job['jobEventId'] = $jobEventId;
		$job['date'] = $bikes4kid['rideDate'];
		$jobId = $jobsTable->insert($job);
		$shift['jobId'] = $jobId;
		$jobShiftId = $jobShiftsTable->insert($shift);
		$rideTable->delete($rideId);
		}
	$vjs = ['jobShiftId' => $jobShiftId,
		'memberId' => $bikes4kid['memberId'],
		'jobId' => $jobId,
		'worked' => 1,
		'signedUpDate' => $bikes4kid['rideDate'],
		'shiftLeader' => 4000 == $bikes4kid['memberId'], ];
	$volunteerJobShift->insert($vjs);
	}

$sql = 'delete from ride where title like "%bikes4kids%" and title like "%worknight%" and rideDate>=?';
\PHPFUI\ORM::execute($sql, [2457689]);

$model = new \App\Model\Volunteer();

// points for volunteers
$model->assignVolunteerPoints(\App\Tools\Date::today() - 2457689);

// write out all the points
$model->saveMemberPoints();
