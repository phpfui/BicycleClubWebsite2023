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

		foreach ($tables as $name)
			{
			if (! empty($parameters[$name]))
				{
				if ('photo' != $name)
					{
					$this->addJoin($name);
					}
				$condition->and($name, '%' . $parameters[$name] . '%', new \PHPFUI\ORM\Operator\Like());
				}
			}

		$this->setWhere($condition);

		return $this;
		}
	}
