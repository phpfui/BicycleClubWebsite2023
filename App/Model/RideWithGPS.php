<?php

namespace App\Model;

class RideWithGPS extends GPS
	{
	private readonly string $clubId;

	/** @var array<string,string> */
	private array $states = [
		'Alaska' => 'AK',
		'Alabama' => 'AL',
		'Arkansas' => 'AR',
		'Arizona' => 'AZ',
		'California' => 'CA',
		'Colorado' => 'CO',
		'Connecticut' => 'CT',
		'District of Columbia' => 'DC',
		'Delaware' => 'DE',
		'Florida' => 'FL',
		'Georgia' => 'GA',
		'Hawaii' => 'HI',
		'Iowa' => 'IA',
		'Idaho' => 'ID',
		'Illinois' => 'IL',
		'Indiana' => 'IN',
		'Kansas' => 'KS',
		'Kentucky' => 'KY',
		'Louisiana' => 'LA',
		'Massachusetts' => 'MA',
		'Maryland' => 'MD',
		'Maine' => 'ME',
		'Michigan' => 'MI',
		'Minnesota' => 'MN',
		'Missouri' => 'MO',
		'Mississippi' => 'MS',
		'Montana' => 'MT',
		'North Carolina' => 'NC',
		'North Dakota' => 'ND',
		'Nebraska' => 'NE',
		'New Hampshire' => 'NH',
		'New Jersey' => 'NJ',
		'New Mexico' => 'NM',
		'Nevada' => 'NV',
		'New York' => 'NY',
		'Ohio' => 'OH',
		'Oklahoma' => 'OK',
		'Oregon' => 'OR',
		'Pennsylvania' => 'PA',
		'Puerto Rico' => 'PR',
		'Rhode Island' => 'RI',
		'South Carolina' => 'SC',
		'Sout hDakota' => 'SD',
		'Tennessee' => 'TN',
		'Texas' => 'TX',
		'Utah' => 'UT',
		'Virginia' => 'VA',
		'Vermont' => 'VT',
		'Washington' => 'WA',
		'Wisconsin' => 'WI',
		'West Virginia' => 'WV',
		'Wyoming' => 'WY',
	];

	public function __construct()
		{
		$settingTable = new \App\Table\Setting();
		$this->clubId = $settingTable->value('RideWithGPSClubId');
		}

	/**
	 * @return array<int, array<mixed>> containing all RWGPS ids in club library indexed by rwgpsId as an integer
	 */
	public function getClubRoutes() : array
		{
		$routes = [];

		if (! $this->clubId)
			{
			return $routes;
			}

		$offset = 50;
		$limit = 50;
		$url = "https://ridewithgps.com/clubs/{$this->clubId}/routes.json";
		$results = \json_decode(\file_get_contents($url), true);
		$count = $results['results_count'] ?? 0;

		while (\count($routes) < $count)
			{
			foreach ($results['results'] as $result)
				{
				$routes[(int)$result['id']] = $result;
				}
			$result = \file_get_contents($url . '?' . \http_build_query(['offset' => $offset, 'limit' => $limit]));
			$results = \json_decode($result, true);
			$offset += $limit;
			}

		return $routes;
		}

	public static function getRWGPSFromLink(string $link) : ?\App\Record\RWGPS
		{
		$parts = \explode('/', $link);
		$urlParts = \parse_url($link);
		$RWGPSId = 0;

		foreach ($parts as $index => $part)
			{
			if ('collections' == $part)
				{
				// do nothing
				break;
				}
			elseif ('routes' == $part)
				{
				if (\count($parts) > $index + 1)
					{
					$RWGPSId = (int)$parts[$index + 1];

					break;
					}
				}
			elseif ('trips' == $part)
				{
				if (\count($parts) > $index + 1)
					{
					$RWGPSId = 0 - (int)$parts[$index + 1];

					break;
					}
				}
			}

		if (! $RWGPSId)
			{
			return null;
			}

		$rwgps = new \App\Record\RWGPS($RWGPSId);
		$rwgps->RWGPSId = $RWGPSId;

		if (isset($urlParts['query']))
			{
			$rwgps->query = $urlParts['query'];
			}

		if (isset($urlParts['fragment']))
			{
			$rwgps->query .= '#' . $urlParts['fragment'];
			}
		$rwgps->insertOrUpdate();

		return $rwgps;
		}

	public function scrape(\App\Record\RWGPS $rwgps) : ?\App\Record\RWGPS
		{
		if (! $rwgps->loaded())
			{
			return null;
			}
		$url = "https://ridewithgps.com/routes/{$rwgps->RWGPSId}.json";

		if ($rwgps->query)
			{
			$url .= '?' . $rwgps->query;
			}

		$client = new \GuzzleHttp\Client(['verify' => false, 'http_errors' => false]);

		try
			{
			$response = $client->request('GET', $url);
			}
		catch (\Throwable $e)
			{
			\App\Tools\Logger::get()->debug($url);
			\App\Tools\Logger::get()->debug($rwgps);

			return null;
			}
		$status = $response->getStatusCode();

		if (200 != $status)
			{
			// 404 = not found, 403 = not public
			if ($status >= 400 && $status < 500 && $rwgps->RWGPSId)
				{
				$rideTable = new \App\Table\Ride();
				$rideTable->changeRWGPSId($rwgps->RWGPSId, null);
				$rwgps->delete();
				}
			else
				{
				\App\Tools\Logger::get()->debug("RWGPS returned {$status} for {$url}");
				}

			return null;
			}

		$json = $response->getBody();
		$data = \json_decode($json, true);

		$this->updateFromData($rwgps, $data);

		return $rwgps;
		}

	/**
	 * @param array<string,mixed> $data
	 */
	public function updateFromData(\App\Record\RWGPS $rwgps, array $data) : void
		{
		$rwgps->RWGPSId = $data['id'];
		$rwgps->town = $data['locality'];
		$state = \App\Tools\TextHelper::properCase($data['administrative_area'] ?? '');
		$rwgps->state = $this->states[$state] ?? $state;

		if (2 == \strlen($rwgps->state))
			{
			$rwgps->state = \strtoupper($rwgps->state);
			}
		$rwgps->country = $data['country_code'];
		$rwgps->zip = $data['postal_code'];
		$rwgps->description = $data['description'];
		$rwgps->miles = $data['distance'] * 0.0006213727366498068;
		$rwgps->km = $data['distance'] / 1000.0;
		$rwgps->elevationMeters = $data['elevation_gain'];
		$rwgps->elevationFeet = $data['elevation_gain'] * 3.28084;
		$rwgps->title = $data['name'];
		$rwgps->longitude = $data['first_lng'];
		$rwgps->latitude = $data['first_lat'];
		$rwgps->percentPaved = 100 - (int)$data['unpaved_pct'];
		$updated_at = (int)$data['updated_at'];

		if ($data['updated_at'] == $updated_at)
			{
			$rwgps->lastUpdated = \date('Y-m-d H:i:s', $updated_at);
			}
		else
			{
			$rwgps->lastUpdated = \date('Y-m-d H:i:s', \strtotime($data['updated_at']));
			}
		$rwgps->lastSynced = \date('Y-m-d H:i:s');

		$rwgps->csv = $data['has_course_points'] ? '' : null;

		if (\count($data['course_points'] ?? []))
			{
			$stream = \fopen('php://memory', 'r+');
			$header = false;
			$lastDistance = 0.0;

			foreach ($data['course_points'] as $point)
				{
				$distance = (float)($point['d'] ?? 0.0);
				$gox = $distance - $lastDistance;
				$row = ['turn' => $point['t'], 'street' => $point['n'] ?? '', 'distance' => \round($distance, 2), 'gox' => \round($gox, 2)];
				$lastDistance = $distance;

				if (! $header)
					{
					$header = true;
					\fputcsv($stream, \array_keys($row));
					}
				\fputcsv($stream, $row);
				}
			\rewind($stream);
			$rwgps->csv = \stream_get_contents($stream);
			}
		}
	}
