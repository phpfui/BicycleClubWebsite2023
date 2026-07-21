<?php

namespace App\Table;

class PhotoTag extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PhotoTag::class;

	/**
	 * @param array<int> $keepers
	 */
	public function deleteNotIn(int $photoId, array $keepers) : void
		{
		$sql = 'delete from photoTag where photoId=?';

		if ($keepers)
			{
			$sql .= ' and photoTagId not in (' . \implode(',', $keepers) . ')';
			}
		\PHPFUI\ORM::execute($sql, [$photoId]);
		}

	public function getHighestRight(int $photoId, int $row) : int
		{
		$sql = 'select leftToRight from photoTag where photoId=? and frontToBack=? order by leftToRight desc limit 1';
		$input = [$photoId, $row];

		$value = (int)\PHPFUI\ORM::getValue($sql, $input);

		return $value + 1;
		}

	public function getTagsForPhoto(int $photoId) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setOrderBy('frontToBack');
		$this->addOrderBy('leftToRight');
		$this->setWhere(new \PHPFUI\ORM\Condition('photoId', $photoId));

		return $this->getArrayCursor();
		}

	public function mostTagged() : \PHPFUI\ORM\ArrayCursor
		{
		$this->setJoin('member');
		$this->setSelect('photoTag.memberId');
		$this->addSelect(new \PHPFUI\ORM\Literal('count(photoTag.memberId)'), 'count');
		$this->addSelect('member.*');

		$this->setGroupBy('photoTag.memberId');
		$this->setOrderBy('count', 'desc');
		$this->addOrderBy('lastName');
		$this->addOrderBy('firstName');

		$this->setLimit(50);

		return $this->getArrayCursor();
		}

	public function topTaggers() : \PHPFUI\ORM\ArrayCursor
		{
		$this->setSelect('taggerId');
		$this->addSelect(new \PHPFUI\ORM\Literal('count(taggerId)'), 'count');
		$this->addSelect('member.*');
		$this->setJoin('member', new \PHPFUI\ORM\Condition('member.memberId', new \PHPFUI\ORM\Literal('taggerId')));
		$this->setGroupBy('taggerId');
		$this->setOrderBy('count', 'desc');
		$this->addOrderBy('member.memberId');
		$this->setLimit(50);

		return $this->getArrayCursor();
		}
	}
