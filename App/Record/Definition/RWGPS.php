<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property int $RWGPSId MySQL type int
 * @property \App\Record\RWGPS $RWGPS related record
 * @property ?int $club MySQL type int
 * @property ?string $country MySQL type varchar(255)
 * @property ?string $csv MySQL type text
 * @property ?string $description MySQL type varchar(255)
 * @property ?float $elevationFeet MySQL type decimal(8,0)
 * @property ?float $elevationMeters MySQL type decimal(8,0)
 * @property ?float $feetPerMile MySQL type decimal(5,1)
 * @property ?float $km MySQL type decimal(4,2)
 * @property string $lastSynced MySQL type datetime
 * @property string $lastUpdated MySQL type datetime
 * @property ?float $latitude MySQL type decimal(10,6)
 * @property ?float $longitude MySQL type decimal(10,6)
 * @property ?float $metersPerKm MySQL type decimal(5,2)
 * @property ?float $miles MySQL type decimal(4,2)
 * @property ?int $percentPaved MySQL type int
 * @property ?string $query MySQL type varchar(255)
 * @property ?int $startLocationId MySQL type int
 * @property \App\Record\StartLocation $startLocation related record
 * @property ?string $state MySQL type varchar(255)
 * @property ?string $title MySQL type varchar(255)
 * @property ?string $town MySQL type varchar(50)
 * @property ?string $zip MySQL type varchar(10)
 */
abstract class RWGPS extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = false;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'RWGPSId' => ['int', 'int', 0, false, ],
		'club' => ['int', 'int', 0, true, 0, ],
		'country' => ['varchar(255)', 'string', 255, true, ],
		'csv' => ['text', 'string', 65535, true, ],
		'description' => ['varchar(255)', 'string', 255, true, '', ],
		'elevationFeet' => ['decimal(8,0)', 'float', 8, true, ],
		'elevationMeters' => ['decimal(8,0)', 'float', 8, true, ],
		'feetPerMile' => ['decimal(5,1)', 'float', 5, true, 0.0, ],
		'km' => ['decimal(4,2)', 'float', 4, true, ],
		'lastSynced' => ['datetime', 'string', 20, false, '0000-00-00 00:00:00', ],
		'lastUpdated' => ['datetime', 'string', 20, false, null, ],
		'latitude' => ['decimal(10,6)', 'float', 10, true, ],
		'longitude' => ['decimal(10,6)', 'float', 10, true, ],
		'metersPerKm' => ['decimal(5,2)', 'float', 5, true, ],
		'miles' => ['decimal(4,2)', 'float', 4, true, ],
		'percentPaved' => ['int', 'int', 0, true, 100, ],
		'query' => ['varchar(255)', 'string', 255, true, ],
		'startLocationId' => ['int', 'int', 0, true, ],
		'state' => ['varchar(255)', 'string', 255, true, ],
		'title' => ['varchar(255)', 'string', 255, true, '', ],
		'town' => ['varchar(50)', 'string', 50, true, '', ],
		'zip' => ['varchar(10)', 'string', 10, true, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['RWGPSId', ];

	protected static string $table = 'RWGPS';
	}
