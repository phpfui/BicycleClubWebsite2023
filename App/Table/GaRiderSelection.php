<?php

namespace App\Table;

class GaRiderSelection extends \PHPFUI\ORM\Table
	{
	protected static string $className = \App\Record\GaRiderSelection::class;

	/**
	 * @param array<string,string|array<string>> $post
	 */
	public function updateFromPost(array $post) : static
		{
		$gaRiderId = (int)$post['gaRiderId'];

		$this->setWhere(new \PHPFUI\ORM\Condition('gaRiderId', $gaRiderId));
		$this->delete();

		foreach ($post['gaOptionId'] as $gaOptionId => $gaSelectionId)
			{
			if (! $gaSelectionId)
				{
				continue;
				}
			$selection = new \App\Record\GaRiderSelection();
			$selection->gaRiderId = $gaRiderId;
			$selection->gaOptionId = (int)$gaOptionId;
			$selection->gaSelectionId = (int)$gaSelectionId;
			$selection->insert();
			}

		return $this;
		}
	}
