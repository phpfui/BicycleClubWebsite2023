<?php

namespace App\Model;

class RideWithGPS
	{
//	private ?\RideWithGPS\API\Client $client = null;

	private readonly string $clubId;

	public function __construct()
		{
		$settingTable = new \App\Table\Setting();
		$this->clubId = $settingTable->value('RideWithGPSClubId');
		$key = $settingTable->value('RideWithGPSAPIKey');
		$token = $settingTable->value('RideWithGPSAuthToken');

//	if ($key && $token && $this->clubId)
//		{
//		$this->client = new \RideWithGPS\API\Client($key, $token);
//		}
		}

	public function cleanStreet(string $street, bool $minimize = true) : string
		{
		// do as much cleaning as we can
		$street = \PHPFUI\TextHelper::unicode_decode(\PHPFUI\TextHelper::unhtmlentities($street));
		$street = \preg_replace('/[^ -~]/', '', $street);
		$street = \str_replace('?', '', $street);

		if (! $minimize)
			{
			return $street;
			}

		$street = \str_replace('State Highway', 'RT', $street);
		$parts = \explode(' ', $street);

		if ('Turn' == $parts[0])
			{
			\array_shift($parts);
			}

		foreach ($parts as &$part)
			{
			$part = \str_replace(['Avenue', 'Drive', 'Street', 'Lane', 'Road', 'Place', 'left', 'right', 'onto', ], ['Ave', 'Dr', 'St', 'Ln', 'Rd', 'Pl', 'L', 'R', '-', ], $part);
			}
		$parts[0] = \ucfirst($parts[0]);
		$street = \implode(' ', $parts);

		return $street;
		}

	/**
	 * @return array containing all RWGPS ids in club library
	 *
	 * @psalm-return list<mixed>
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

	public function getCSVReader(string $csv) : \App\Tools\CSVReader
		{
		$tempFile = new \App\Tools\TempFile();
		$newHeaders = 'turn,street,distance,elevation,description,edited';
		$metricHeaders = 'Type,Notes,Distance (km) From Start,Elevation (m),Description,Edited';
		// could be metric, if so, convert to English
		if (\str_contains($csv, $metricHeaders))
			{
			$cues = \str_replace($metricHeaders, $newHeaders, $csv);
			$metricFile = new \App\Tools\TempFile();
			\file_put_contents($metricFile, $cues);
			$metricReader = new \App\Tools\CSVReader($metricFile);

			$metricWriter = new \App\Tools\CSVWriter($tempFile, ',', false);
			$metricWriter->addHeaderRow();

			foreach ($metricReader as $row)
				{
				$row['distance'] = \number_format((float)$row['distance'] * 0.621371, 2);
				$metricWriter->outputRow($row);
				}
			unset($metricWriter);
			}
		else
			{
			$headers = 'Type,Notes,Distance (miles) From Start,Elevation (ft),Description,Edited';
			$cues = \str_replace($headers, $newHeaders, $csv);
			\file_put_contents($tempFile, $cues);
			}

		return new \App\Tools\CSVReader($tempFile);
		}

	public static function getDirectionsLink(\App\Record\RWGPS $route) : string
		{
		if (! self::validGeoLocation($route->toArray()))
			{
			return '';
			}

		return "https://www.google.com/maps/dir/?api=1&destination={$route->latitude},{$route->longitude}";
		}

	public static function getMapPinLink(array $route) : string
		{
		if (! self::validGeoLocation($route))
			{
			return '';
			}

		return "https://www.google.com/maps/?q={$route['latitude']},{$route['longitude']}";
		}

	public static function getRouteLink(?int $RWGPSId) : string
		{
		if (! $RWGPSId)
			{
			return '';
			}

		$rwgps = new \App\Record\RWGPS();
		$type = $RWGPSId > 0 ? 'routes' : 'trips';
		$RWGPSId = \abs($RWGPSId);
		$query = $rwgps->query ?? '';

		if ($query)
			{
			$query = '?' . $query;
			}

		return "https://ridewithgps.com/{$type}/{$RWGPSId}{$query}";
		}

	/**
	 * @return (int|string)[]|false
	 *
	 * @psalm-return array{scheme?: string, user?: string, pass?: string, host?: string, port?: int, path?: string, query?: string, fragment?: string, RWGPSId: int}|false
	 */
	public static function getRWGPSIdFromLink(string $link) : array | bool
		{
		$parts = \explode('/', $link);
		$id = \parse_url($link);

		foreach ($parts as $index => $part)
			{
			if ('routes' == $part)
				{
				if (\count($parts) > $index + 1)
					{
					$id['RWGPSId'] = (int)$parts[$index + 1];

					break;
					}
				}
			elseif ('trips' == $part)
				{
				if (\count($parts) > $index + 1)
					{
					$id['RWGPSId'] = 0 - (int)$parts[$index + 1];

					break;
					}
				}
			}

		if (! isset($id['RWGPSId']))
			{
			$id['RWGPSId'] = 0;
			}

		return $id;
		}

	public function scrape(\App\Record\RWGPS $rwgps, bool $delay = true) : ?\App\Record\RWGPS
		{
		if ($delay)
			{
			\sleep(\random_int(3, 5));
			}
		$url = static::getRouteLink($rwgps->RWGPSId);

		if (empty($url))
			{
			return null;
			}

		$client = new \GuzzleHttp\Client(['verify' => false, 'http_errors' => false]);

		try
			{
			$response = $client->request('GET', $url);
			}
		catch (\Throwable)
			{
			\App\Tools\Logger::get()->debug($url);
			\App\Tools\Logger::get()->debug($rwgps);

			return null;
			}
		$rwgps->status = $response->getStatusCode();
		$rwgps->lastUpdated = \App\Tools\Date::todayString();

		if (200 != $rwgps->status)
			{
			// 404 = not found, 403 = not public
			if ($rwgps->status >= 400 && $rwgps->status < 500)
				{
				$rideTable = new \App\Table\Ride();
				$rideTable->setWhere(new \PHPFUI\ORM\Condition('RWGPSId', $rwgps->RWGPSId));
				$rideTable->delete();
				$rwgps->delete();
				}
			else
				{
				\App\Tools\Logger::get()->debug("RWGPS returned {$rwgps->status} for {$url}");
				}

			return null;
			}
		$html = $response->getBody();
		$dom = new \voku\helper\HtmlDomParser("{$html}");

		foreach ($dom->find('meta') as $node)
			{
			$attributes = $node->getAllAttributes();
			$content = $attributes['content'] ?? '';
			$name = $attributes['name'] ?? '';
			$property = $attributes['property'] ?? '';

			if ('keywords' == $name)
				{
				$parts = \explode(',', $content);
				$rwgps->town = \str_replace('Town of ', '', $parts[0] ?? '');
				$rwgps->state = \str_replace(['New York', 'New Jersey', 'Connecticut'], ['NY', 'NJ', 'CT'], $parts[1] ?? '');
				$rwgps->zip = $parts[2] ?? '';
				}
			elseif ('description' == $name)
				{
				if (\strpos($content, '--'))
					{
					$parts = \explode(' -- ', $content);
					$rwgps->description = \trim($parts[0]);
					$content = $parts[1] ?? '';
					}
				$parts = \explode(' ', $content);
				$prior = '';

				foreach ($parts as $part)
					{
					if ('mi,' == $part)
						{
						$rwgps->mileage = (float)$prior;
						}
					elseif ('ft.' == $part)
						{
						$rwgps->elevation = (int)\trim($prior, '+');
						}
					$prior = $part;
					}
				}
			elseif ('og:title' == $property)
				{
				$content = \str_replace('(copy)', '', $content);
				$content = \str_replace('  ', ' ', $content);
				$content = \trim($content);
				$rwgps->title = $content;
				}
			}

		if ($rwgps->RWGPSId > 0)
			{
			$rwgps->csv = $this->getFile('csv', $url, $delay);
			}
		else
			{
			$rwgps->csv = '';
			}
		$kml = $this->getFile('kml', $url, $delay);

		if ($kml)
			{
			$coords = \str_replace(\chr(10), '', $kml);
			$tag = '<coordinates>';
			$coords = \substr($coords, \strpos($coords, $tag) + \strlen($tag));
			$values = \explode(',', $coords);

			if (isset($values[0]))
				{
				$rwgps->longitude = (float)$values[0];
				}

			if (isset($values[1]))
				{
				$rwgps->latitude = (float)$values[1];
				}
			}

		return $rwgps;
		}

	private function getFile(string $extension, string $url, bool $delay) : string
		{
		if ($delay)
			{
			\sleep(\random_int(3, 5));
			}
		$parts = \parse_url($url);
		$parts['path'] .= '.' . $extension;

		$urlWithExtension = $this->unparse_url($parts);

		try
			{
			return \file_get_contents($urlWithExtension);
			}
		catch (\Throwable)
			{
			}

		return '';
		}

	private function unparse_url(array $parsed_url) : string
		{
		$scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host = $parsed_url['host'] ?? '';
		$port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user = $parsed_url['user'] ?? '';
		$pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
		$pass = ($user || $pass) ? "{$pass}@" : '';
		$path = $parsed_url['path'] ?? '';
		$query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

		return "{$scheme}{$user}{$pass}{$host}{$port}{$path}{$query}{$fragment}";
		}

	private static function validGeoLocation(array $route) : bool
		{
		return isset($route['latitude']) && isset($route['longitude']) && ((float)$route['latitude'] + (float)$route['longitude']);
		}
	}

/*
	[id] => 43200270
	[group_membership_id] => 474439
	[name] => Fort Edward to Fort Ann on Champlain Canalway Trail
	[description] => Part of the Empire State Trail
	[created_at] => 2023-06-07T13:22:44-07:00
	[distance] => 38017.2
	[elevation_gain] => 77.8214
	[elevation_loss] => 77.3618
	[visibility] => 0
	[first_lat] => 43.27367
	[first_lng] => -73.58005
	[last_lat] => 43.27367
	[last_lng] => -73.58005
	[is_trip] =>
	[postal_code] => 12828
	[locality] => Fort Edward
	[administrative_area] => NY
	[pavement_type_id] =>
	[country_code] => US
	[has_course_points] => 1
	[updated_at] => 2023-06-07T13:22:44-07:00
	[best_for_id] =>
	[planner_options] => 64
	[user_id] => 459297
	[deleted_at] =>
	[sw_lng] => -73.58006
	[sw_lat] => 43.27367
	[ne_lng] => -73.48618
	[ne_lat] => 43.413873
	[track_id] => 6480e7146b34d70c8e2c9b5c
	[archived_at] =>
	[likes_count] => 0
	[track_type] => out_and_back
	[terrain] => flat
	[difficulty] => easy
	[unpaved_pct] => 42
	[nav_enabled] => 1
 */
