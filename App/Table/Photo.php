<?php

namespace App\Table;

class Photo extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Photo::class;

	/**
	 * @param array<string,string> $parameters
	 */
	public function search(array $parameters = []) : static
		{
		$tables = [
			'photo',
			'photoTag',
			'photoComment',
		];

		$condition = new \PHPFUI\ORM\Condition();

		foreach ($tables as $tableName)
			{
			if ('photo' != $tableName)
				{
				$this->addJoin($tableName);
				}

			foreach ($parameters as $fieldName => $value)
				{
				if ($value)
					{
					$condition->or($fieldName, '%' . $value . '%', new \PHPFUI\ORM\Operator\Like());
					}
				}
			}

		$this->setWhere($condition);
		$this->setGroupBy('photo.photoId')->setDistinct();

		return $this;
		}
	}
