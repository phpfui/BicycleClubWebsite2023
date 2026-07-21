<?php

namespace Tests\Table;

class BoardMember extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\BoardMember::class;

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\BoardMember>
	 */
	public function getBoardMembers() : \PHPFUI\ORM\RecordCursor
		{
		$this->addOrderBy('rank', 'desc');

		return $this->getRecordCursor();
		}

	public function getPosition(string $position) : \PHPFUI\ORM\DataObject
		{
		$this->addJoin('member');
		$this->setWhere(new \PHPFUI\ORM\Condition('title', $position));

		$cursor = $this->getDataObjectCursor();

		if (\count($cursor))
			{
			return $cursor->current();
			}

		return new \PHPFUI\ORM\DataObject([]);
		}
	}
