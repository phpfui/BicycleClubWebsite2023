<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RWGPSAlternate> $alternateRoutes
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RWGPSComments> $comments
 */
class RWGPS extends \App\Record\Definition\RWGPS
	{
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

		return $this;
		}

	public function computeElevationGain() : static
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

	public function getCSVReader() : \App\Tools\CSV\FileReader
		{
		return new \App\Tools\CSV\FileReader($this->csv);
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

	public function update() : bool
		{
		$this->computeElevationGain();

		return parent::update();
		}
	}
