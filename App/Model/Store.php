<?php

namespace App\Model;

class Store
	{
	/**
	 * @param array<string, string> $parameters
	 */
	public function copy(\App\Record\StoreItem $original, array $parameters) : \App\Record\StoreItem
		{
		$storeItem = clone $original;
		$storeItem->setFrom($parameters);
		$storeItem->storeItemId = 0;
		$storeItem->insert();

		// copy related records
		$storeItemDetailTable = new \App\Table\StoreItemDetail();
		$storeItemDetailTable->setWhere(new \PHPFUI\ORM\Condition('storeItemId', $original->storeItemId));

		foreach ($storeItemDetailTable->getRecordCursor() as $storeItemDetailOriginal)
			{
			$storeItemDetail = clone $storeItemDetailOriginal;
			$storeItemDetail->storeItemDetailId = 0;
			$storeItemDetail->storeItem = $storeItem;
			$storeItemDetail->insert();
			}

		$storeItemOptionTable = new \App\Table\StoreItemOption();
		$storeItemOptionTable->setWhere(new \PHPFUI\ORM\Condition('storeItemId', $original->storeItemId));

		foreach ($storeItemOptionTable->getRecordCursor() as $storeItemOptionOriginal)
			{
			$storeItemOption = clone $storeItemOptionOriginal;
			$storeItemOption->storeItem = $storeItem;
			$storeItemOption->insert();
			}

		$storeImageModel = new \App\Model\StoreImages();
		$storeImageModel->copyPhotos($original, $storeItem);

		return $storeItem;
		}
	}
