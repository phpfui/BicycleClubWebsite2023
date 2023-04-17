<?php

namespace App\Table;

class BoardMember extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\BoardMember::class;

	public function clearRank() : void
		{
		$this->update(['rank' => 0]);
		}

	public function getBoardMember(string $position, string $optional_text = '') : string
		{
		$pos = $this->getPosition($position);

		if (! $pos->empty())
			{
			$output = '<a href="~URL~">email ~FIRST~ ~LAST~, ~POSITION~</a>';

			if (\strlen($optional_text))
				{
				$output = $optional_text;
				}
			$output = \str_replace('~FIRST~', $pos->firstName, $output);
			$output = \str_replace('~LAST~', $pos->lastName, $output);
			$output = \str_replace('~POSITION~', $position, $output);
			$csrf = \App\Model\Session::csrf();
			$output = \str_replace('~URL~', "/ContactUs/{$csrf}?id={$pos->memberId}", $output);

			return $output;
			}

		return $position;
		}

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
