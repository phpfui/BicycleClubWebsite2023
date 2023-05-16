<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RWGPSAlternate> $alternateRoutes
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RWGPSComments> $comments
 */
class RWGPS extends \App\Record\Definition\RWGPS
	{
	protected static array $virtualFields = [
		'alternateRoutes' => [\PHPFUI\ORM\Children::class, \App\Table\RWGPSAlternate::class],
		'comments' => [\PHPFUI\ORM\Children::class, \App\Table\RWGPSComment::class, 'lastEdited', 'desc'],
	];

	public function computeFeetPerMile() : static
		{
		if (! empty($this->elevation) && ! empty($this->mileage))
			{
			$this->feetPerMile = $this->elevation / $this->mileage;
			}

		return $this;
		}

	public function insert() : int
		{
		$this->computeFeetPerMile();

		return parent::insert();
		}

	public function insertOrUpdate() : int
		{
		$this->computeFeetPerMile();

		return parent::insertOrUpdate();
		}

	/**
	 * @return array['count', 'rating']
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
		$this->computeFeetPerMile();

		return parent::update();
		}
	}
