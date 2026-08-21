<?php

namespace App\Table;

class StoreItem extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\StoreItem::class;

	public function byTitle(?bool $hasVolunteerPoints = null, ?bool $activeOnly = null, ?bool $inStock = null, ?\App\Record\Folder $folder = null) : \PHPFUI\ORM\RecordCursor
		{
		$storeItemDetailTable = new \App\Table\StoreItemDetail();
		$storeItemDetailTable->addSelect('storeItemDetail.storeItemId');
		$storeItemDetailTable->addJoin('storeItem');

		$stockCondition = new \PHPFUI\ORM\Condition();

		if (true === $inStock)
			{
			$stockCondition = new \PHPFUI\ORM\Condition('quantity', 0, new \PHPFUI\ORM\Operator\GreaterThan());
			}

		if (false === $inStock)
			{
			$stockCondition = new \PHPFUI\ORM\Condition('quantity', 0);
			}

		if (null !== $folder && $folder->folderId)
			{
			$stockCondition->and('storeItem.folderId', $folder->folderId);
			}
		else
			{
			$noFolder = new \PHPFUI\ORM\Condition('storeItem.folderId', 0);
			$noFolder->or('stormItem.folderId', null);
			$stockCondition->and($noFolder);
			}
		$storeItemDetailTable->setWhere($stockCondition);

		$storeItemOptionTable = new \App\Table\StoreItemOption();
		$storeItemOptionTable->addSelect('storeItemId');

		$orderCondition = new \PHPFUI\ORM\Condition('storeItemId', $storeItemDetailTable, new \PHPFUI\ORM\Operator\In());
		$orderCondition->or('storeItemId', $storeItemOptionTable, new \PHPFUI\ORM\Operator\In());

		$condition = new \PHPFUI\ORM\Condition();
		$condition->and($orderCondition);

		if (null !== $activeOnly)
			{
			$condition->and('active', (int)$activeOnly);
			}

		if (false === $hasVolunteerPoints)
			{
			$condition->and('pointsOnly', 0);
			}

		if (null !== $folder && $folder->folderId)
			{
			$condition->and('folderId', $folder->folderId);
			}

		$this->setWhere($condition);
		$this->addOrderBy('title');

		return $this->getRecordCursor();
		}

	public function getHighest() : \App\Record\StoreItem
		{
		$this->setLimit(1);
		$this->setOrderBy('storeItemId', 'desc');

		return $this->getRecordCursor()->current();
		}
	}
