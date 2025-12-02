<?php

namespace App\Model;

class RideWithGPS extends \App\Model\GPS
	{
	private string $apiKey = '';

	private string $authToken = '';

	private string $baseUri = 'https://ridewithgps.com';

	private ?\GuzzleHttp\Client $client = null;

	private readonly string $clubId;

	private readonly string $queryString;

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
		$this->apiKey = $settingTable->value('RideWithGPSAPIkey');
		$this->queryString = \http_build_query(
			['email' => $settingTable->value('RideWithGPSEmail'),
				'password' => $settingTable->value('RideWithGPSPassword'),
				'apikey' => $this->apiKey, ]
		);
		}

	public function getAuthToken() : string
		{
		if ($this->authToken)
			{
			return $this->authToken;
			}

		if (! $this->apiKey)
			{
			return '';
			}

		$url = $this->baseUri . '/users/current.json?' . $this->queryString;
		$json = @\file_get_contents($url);

		if (false === $json)
			{
			return '';
			}

		$results = \json_decode($json, true);

		return $this->authToken = ($results['user']['auth_token'] ?? '');
		}

	/**
	 * @return array<string, array<string,string>> indexed by email address containing id,first_name,last_name,display_name
	 */
	public function getClubMembers() : array
		{
		$client = $this->getGuzzleClient();

		if (! $client)
			{
			return [];
			}
		$url = "{$this->baseUri}/clubs/{$this->clubId}/table_members.json?" . $this->queryString;

		try
			{
			$response = $client->get($url);
			}
		catch (\Throwable $e)
			{
			\App\Tools\Logger::get()->debug($url);

			return [];
			}

		$results = \json_decode($response->getBody()->getContents(), true);
		$members = [];

		foreach ($results as $memberData)
			{
			$email = $memberData['user']['real_email'] ?? '';
			unset($memberData['user']['real_email']);
			$memberData['user']['id'] = $memberData['id'];
			$memberData['user']['active'] = $memberData['active'] ? 1 : 0;
			$members[$email] = $memberData['user'];
			}

		return $members;
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
		$url = $this->baseUri . "/clubs/{$this->clubId}/routes.json?" . $this->queryString;
		$json = @\file_get_contents($url);

		if (false === $json)
			{
			return $routes;
			}

		$results = \json_decode($json, true);
		$count = $results['results_count'] ?? 0;

		while (\count($routes) < $count)
			{
			foreach ($results['results'] as $result)
				{
				$routes[(int)$result['id']] = $result;
				}
			$result = @\file_get_contents($url . '?' . \http_build_query(['offset' => $offset, 'limit' => $limit]));

			if (false === $result)
				{
				break;
				}

			$results = \json_decode($result, true);
			$offset += $limit;
			}

		return $routes;
		}

	public static function getElevation(\App\Record\RWGPS $RWGPS) : float
		{
		$rideTable = new \App\Table\Ride();
		$rideTable->addJoin('rideRWGPS');
		$rideTable->addSelect(new \PHPFUI\ORM\Literal('AVG(ride.elevation)'), 'elevation');
		$rideTable->addSelect(new \PHPFUI\ORM\Literal('count(*)'), 'count');
		$condition = new \PHPFUI\ORM\Condition('rideRWGPS.RWGPSId', $RWGPS->RWGPSId);
		$condition->and('ride.elevation', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('ride.rideStatus', \App\Enum\Ride\Status::COMPLETED);
		$condition->and('ride.pending', 0);
		$rideTable->setWhere($condition);

		$row = $rideTable->getRows()[0];

		if ($row['count'])
			{
			$elevation = (float)$row['elevation'];
			}
		else
			{
			$elevation = $RWGPS->elevationFloat();
			}

		return \round($elevation, 2);
		}

	public function getRWGPSFromLink(string $link) : ?\App\Record\RWGPS
		{
		if (! \strlen($link))
			{
			return null;
			}
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

		if (! $this->scrape($rwgps, true))
			{
			return null;
			}

		$rwgps->insertOrUpdate();

		return $rwgps;
		}

	public static function normalizeCSV(?string $csv) : ?string
		{
		if (null === $csv)
			{
			return null;
			}

		$reader = new \App\Tools\CSV\StringReader($csv);
		$writer = new \App\Tools\CSV\StringWriter();

		foreach ($reader as $row)
			{
			$row['distance'] = \number_format((float)\filter_var($row['distance'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION), 2, '.', '');
			$row['gox'] = \number_format((float)\filter_var($row['gox'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION), 2, '.', '');
			$writer->outputRow($row);
			}

		return (string)$writer;
		}

	public function scrape(\App\Record\RWGPS $rwgps, bool $alwaysScrape = false) : ?\App\Record\RWGPS
		{
		if (! $rwgps->RWGPSId || (! $alwaysScrape && ! $rwgps->loaded()))
			{
			return null;
			}
		$type = ($rwgps->RWGPSId > 0) ? 'routes' : 'trips';
		$id = \abs($rwgps->RWGPSId);
		$url = "{$this->baseUri}/{$type}/{$id}.json";
		$client = $this->getGuzzleClient();

		if (! $client)
			{
			return null;
			}

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
			if ($status >= 400 && $status < 500)
				{
				new \App\Table\RideRWGPS()->changeRWGPSId($rwgps, null);
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
	 * @param array<string,mixed> $original
	 */
	public function updateFromData(\App\Record\RWGPS $rwgps, array $original) : void
		{
		if (isset($original['type']))
			{
			$data = $original[$original['type']];
			}
		else
			{
			$data = $original;
			}

		$rwgps->RWGPSId = (int)$data['id'];
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

		if (isset($data['privacy_code']))
			{
			$rwgps->query = 'privacy_code=' . $data['privacy_code'];
			}
		$rwgps->percentPaved = 100 - (int)($data['unpaved_pct'] ?? 0);
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

		$rwgps->csv = null;

		if (! empty($data['has_course_points']) && \count($data['course_points'] ?? []))
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
					\fputcsv($stream, \array_keys($row), escape:'\\');
					}
				\fputcsv($stream, $row, escape:'\\');
				}
			\rewind($stream);
			$csv = \stream_get_contents($stream);

			if ($csv)
				{
				$rwgps->csv = $csv;
				}
			}
		}

	/**
	 * To set a user as inactive:
	 * POST https://ridewithgps.com/clubs/1/update_member_field.json (form-data content type)
	 * club_member_id=<member_id>&field=active&value=0
	 *
	 * @param array<string, string> $member
	 */
	public function updateMember(array $member, bool $active = false) : bool
		{
		$url = "{$this->baseUri}/clubs/{$this->clubId}/update_member_field.json";
		$formData = ['club_member_id' => $member['id'], 'field' => 'active', 'value' => $active ? 1 : 0];

		$client = $this->getGuzzleClient();

		if (! $client)
			{
			return false;
			}

		try
			{
			$response = $client->request('POST', $url, ['form_params' => $formData, ]);
			}
		catch (\Throwable $e)
			{
			\App\Tools\Logger::get()->debug($formData, $url);

			return false;
			}

		return 200 == $response->getStatusCode();
		}

	private function getGuzzleClient() : ?\GuzzleHttp\Client
		{
		if (! $this->apiKey)
			{
			return null;
			}

		if (! $this->client)
			{
			if (! $this->getAuthToken())
				{
				\App\Tools\Logger::get()->debug('Can not get RWGPS Auth Token, check settings');

				return null;
				}

			$this->client = new \GuzzleHttp\Client([
				'verify' => false,
				'http_errors' => false,
				'headers' => [
					'Accept' => 'application/json',
					'x-rwgps-api-version' => '2',
					'x-rwgps-api-key' => $this->apiKey,
					'x-rwgps-auth-token' => $this->authToken,
				],
			]);
			}

		return $this->client;
		}
	}
