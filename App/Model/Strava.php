<?php

namespace App\Model;

class Strava
	{
	private function __construct(private array $data)
		{
		}

	public function getElevationFeet() : int
		{
		return (int)($this->data['ft'] ?? 0.0);
		}

	public function getMileage() : float
		{
		return (float)($this->data['mi'] ?? 0.0);
		}

	public static function loadFromActivityId(string $activityId) : ?\App\Model\Strava
		{
		$url = 'https://www.strava.com/activities/' . $activityId;
		$html = \file_get_contents($url);

		if (! $html)
			{
			return null;
			}
		$dom = new \voku\helper\HtmlDomParser($html);

		$parts = [];

		foreach ($dom->find('.unit') as $node)
			{
			$unit = $node->text();
			$value = \str_replace([' ' . $unit, ','], '', $node->parentNode()->text());
			$parts[$unit] = $value;
			}

		return new \App\Model\Strava($parts);
		}
	}
