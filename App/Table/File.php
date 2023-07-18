<?php

namespace App\Table;

class File extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\File::class;

	/**
	 * @param array<string,string> $parameters
	 */
	public function search(array $parameters = []) : static
		{
		$condition = new \PHPFUI\ORM\Condition();

		$fields = ['file', 'fileName', 'extension'];

		foreach ($fields as $name)
			{
			if (! empty($parameters[$name]))
				{
				$condition->and($name, '%' . $parameters[$name] . '%', new \PHPFUI\ORM\Operator\Like());
				}
			}

		$this->setWhere($condition);

		return $this;
		}
	}
