<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RWGPSAlternate> $alternateRoutes
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RWGPSComment> $comments
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RWGPSRating> $ratings
 */
class RWGPS extends \App\Record\Definition\RWGPS
	{
	use \App\DB\Trait\Directions;

	protected static ?string $units = null;

	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'alternateRoutes' => [\PHPFUI\ORM\Children::class, \App\Table\RWGPSAlternate::class],
		'comments' => [\PHPFUI\ORM\Children::class, \App\Table\RWGPSComment::class, 'lastEdited', 'desc'],
		'ratings' => [\PHPFUI\ORM\Children::class, \App\Table\RWGPSRating::class],
	];

	public function clean() : static
		{
		$this->town = $this->town ?? '';
		$this->state = $this->state ?? '';
		$this->zip = $this->zip ?? '';
		$this->description = $this->description ?? '';
		$this->title = $this->title ?? '';
		$this->club = $this->club ?? 0;
		$this->csv = \App\Model\RideWithGPS::normalizeCSV($this->csv);

		return $this;
		}

	public function delete() : bool
		{
		new \App\Table\CueSheet()->setWhere(new \PHPFUI\ORM\Condition('RWGPSId', $this->RWGPSId))->update(['RWGPSId' => null]);
		new \App\Table\RideRWGPS()->setWhere(new \PHPFUI\ORM\Condition('RWGPSId', $this->RWGPSId))->delete();

		return parent::delete();
		}

	/**
	 * @return string distance with correct units depending on setting
	 */
	public function distance() : string
		{
		$units = $this->getUnits();

		$distance = 'km' == $units ? $this->km : $this->miles;

		return \number_format((float)$distance, 1) . ' ' . $units;
		}

	/**
	 * @return string elevation with correct units depending on setting
	 */
	public function elevation() : string
		{
		$units = $this->getUnits();

		$elevation = 'km' == $units ? $this->elevationMeters : $this->elevationFeet;

		return \number_format(\round((float)$elevation), 0) . ' ' . self::getSmallUnits();
		}

	/**
	 * @return float elevation as raw number in correct units depending on setting
	 */
	public function elevationFloat() : float
		{
		return (float)('km' == $this->getUnits() ? $this->elevationMeters : $this->elevationFeet);
		}

	public function gain() : string
		{
		$units = $this->getUnits();

		if ('km' == $units)
			{
			$units = 'm/km';
			$elevation = $this->metersPerKm;
			}
		else
			{
			$units = 'ft/mile';
			$elevation = $this->feetPerMile;
			}

		return \number_format((float)$elevation, 0) . ' ' . $units;
		}

	public function getCSVReader() : \App\Tools\CSV\Reader
		{
		return new \App\Tools\CSV\StringReader($this->csv);
		}

	public static function getSmallUnits() : string
		{
		return 'km' == self::getUnits() ? 'Meters' : 'Feet';
		}

	public static function getUnits() : string
		{
		if (null === self::$units)
			{
			self::$units = new \App\Record\Setting('RWGPSUnits')->value;
			}

		return self::$units;
		}

	public function insert() : int
		{
		$this->computeElevationGain();

		return parent::insert();
		}

	public function insertOrUpdate() : int
		{
		$this->computeElevationGain();

		return parent::insertOrUpdate();
		}

	/**
	 * @return array<string,mixed>
	 */
	public function rating() : array
		{
		$rwgpsRating = new \App\Table\RWGPSRating();
		$rwgpsRating->setWhere(new \PHPFUI\ORM\Condition('RWGPSId', $this->RWGPSId));
		$rwgpsRating->addSelect(new \PHPFUI\ORM\Literal('avg(rating)'), 'rating');
		$rwgpsRating->addSelect(new \PHPFUI\ORM\Literal('count(*)'), 'count');

		return $rwgpsRating->getArrayCursor()->current();
		}

	public function routeLink() : string
		{
		$RWGPSId = \abs($this->RWGPSId ?? 0);

		if (! $RWGPSId)
			{
			return '';
			}

		$type = $this->RWGPSId > 0 ? 'routes' : 'trips';
		$query = $this->query ?? '';

		if ($query)
			{
			$query = '?' . $query;
			}

		return "https://ridewithgps.com/{$type}/{$RWGPSId}{$query}";
		}

	public function update() : bool
		{
		$this->computeElevationGain();

		return parent::update();
		}

	private function computeElevationGain() : static
		{
		if (! empty($this->elevationFeet) && ! empty($this->miles))
			{
			$this->feetPerMile = $this->elevationFeet / $this->miles;
			}

		if (! empty($this->elevationMeters) && ! empty($this->km))
			{
			$this->metersPerKm = $this->elevationMeters / $this->km;
			}

		return $this;
		}
	}
