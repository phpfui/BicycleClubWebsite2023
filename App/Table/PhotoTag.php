<?php

namespace App\Table;

class PhotoTag extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PhotoTag::class;

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

	public function getTagsForPhoto(int $photoId) : iterable
		{
		$sql = 'select * from photoTag where photoId=? order by frontToBack, leftToRight';
		$input = [$photoId];

		return \PHPFUI\ORM::getArrayCursor($sql, $input);
		}

	public function mostTagged() : iterable
		{
		$sql = 'select pt.memberId,count(pt.memberId) count,m.* from photoTag pt left join member m on m.memberId=pt.memberId group by pt.memberId order by count desc,m.lastName,m.firstName limit 50';

		return \PHPFUI\ORM::getArrayCursor($sql);
		}

	public function topTaggers() : iterable
		{
		$sql = 'select taggerId,count(taggerId) count,m.* from photoTag pt left join member m on m.memberId=pt.taggerId group by pt.taggerId order by count desc limit 50';

		return \PHPFUI\ORM::getArrayCursor($sql);
		}
	}
