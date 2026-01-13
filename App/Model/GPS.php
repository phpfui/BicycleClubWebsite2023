<?php

namespace App\Model;

class GPS
	{
	/**
	 * @param array<string,mixed> $route
	 */
	public static function getMapPinLink(array $route) : string
		{
		if (! self::validGeoLocation($route))
			{
			return '';
			}

		return "https://www.google.com/maps/?q={$route['latitude']},{$route['longitude']}";
		}

	/**
	 * @param array<string,mixed> $route
	 */
	protected static function validGeoLocation(array $route) : bool
		{
		return isset($route['latitude'], $route['longitude']) && ((float)$route['latitude'] + (float)$route['longitude']);
		}
	}
