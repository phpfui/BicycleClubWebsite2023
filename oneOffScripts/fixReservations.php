<pre>
<?php
\set_time_limit(9999);
// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$reservationTable = new \App\CRUD\Reservation();
$reservationPersonTable = new \App\CRUD\ReservationPerson();
$paymentsTable = new \App\CRUD\Payments();
$membersTable = new \App\CRUD\Members();

$reservations = $reservationTable->readMultiple();

foreach ($reservations as $reservation)
	{
	$persons = $reservationPersonTable->getAttendesForReservation($reservation['reservationId']);

	if (! \count($persons))
		{
		// no event or empty name is a problem
		if (! $reservation['eventId'] || empty($reservation['reservationFirstName'] . $reservation['reservationLastName']))
			{
			$reservationTable->delete($reservation['reservationId']);
			echo "delete {$reservation['reservationId']}<br>";

			continue;
			}
		echo "{$reservation['reservationId']} event {$reservation['eventId']} {$reservation['reservationFirstName']} {$reservation['reservationLastName']}<br>";
		$reservation['firstName'] = $reservation['reservationFirstName'];
		$reservation['lastName'] = $reservation['reservationLastName'];
		$reservation['email'] = $reservation['reservationemail'];
		$reservationPersonTable->insert($reservation);
		}
	}
