<?php

namespace App\Model;

class API
	{
	private ?\PHPFUI\ORM\Table $table = null;

	private array $children = [];

	private array $related = [];

	private array $errors = [];

	private array $permissions = [];

	private array $allowedFields = [];

	private array $disallowedFields = ['password', 'loginAttempts', ];

	public function __construct(string $tableName, private readonly \PHPFUI\Interfaces\NanoController $controller)
		{
		$headers = \getallheaders();

		if (! \array_key_exists('Authorization', $headers))
			{
			$this->errors[] = 'Authorization header not found';

			return;
			}

		$header = $headers['Authorization'];
		$parts = \explode(' ', (string)$header);

		if ('Bearer' != $parts[0])
			{
			$this->errors[] = 'Bearer token not found';

			return;
			}

		$oauthToken = new \App\Record\OauthToken(['token' => $parts[1]]);

		if (! $oauthToken->loaded())
			{
			$this->errors[] = 'Bearer token is not valid';

			return;
			}

		if ($oauthToken->expires < \date('Y-m-d H:i:s'))
			{
			$this->errors[] = 'Bearer token has expired';

			return;
			}

		$this->permissions = $oauthToken->getPermissions();

		$className = 'App\\Table\\' . \ucfirst($tableName);

		if (\class_exists($className))
			{
			$this->table = new $className();
			$this->table->setLimit(50);
			}
		else
			{
			$this->errors[] = "Table {$tableName} does not exist.";
			$this->errors[] = 'Valid table names are:';

			foreach (\PHPFUI\ORM\Table::getAllTables() as $table)
				{
				$tableName = $table->getTableName();

				if ($this->isAuthorized('GET', $tableName))
					{
					$this->errors[] = $tableName;
					}
				}
			\http_response_code(418);
			}
		}

	public function getTable() : ?\PHPFUI\ORM\Table
		{
		return $this->table;
		}

	/**
	 * Call to stop processing
	 */
	public function nullTable() : static
		{
		$this->table = null;

		return $this;
		}

	public function getNextLink() : string
		{
		$get = $this->controller->getGet();

		if (null === $this->table->getOffset() || ! $this->table->getLimit())
			{
			return '';
			}

		$get['offset'] = $this->table->getOffset() + $this->table->getLimit();

		return $this->controller->getUri() . '?' . \http_build_query($get);
		}

	public function getPrevLink() : string
		{
		$get = $this->controller->getGet();

		if (null === $this->table->getOffset() || ! $this->table->getLimit())
			{
			return '';
			}

		$get['offset'] = \max(0, $this->table->getOffset() - $this->table->getLimit());

		return $this->controller->getUri() . '?' . \http_build_query($get);
		}

	public function getErrors() : array
		{
		return $this->errors;
		}

	public function applyParameters(array $parameters) : static
		{
		if (! $this->table)
			{
			$this->errors[] = 'non existant table';

			return $this;
			}
		$sort = 'asc';
		$sortField = '';

		foreach ($parameters as $name => $value)
			{
			$name = \strtolower($name);

			switch ($name)
				{
				case 'where':
					$condition = $this->getCondition(\json_decode((string)($value ?: '[]'), true));

					if (\count($condition))
						{
						$this->table->setWhere($condition);
						}

					break;

				case 'limit':
					$this->table->setLimit((int)$value);

					break;

				case 'offset':
					$this->table->setOffset((int)$value);

					break;

				case 'fields':
					$this->allowedFields = \explode(',', (string)$value);

					foreach ($this->allowedFields as $field)
						{
						$this->table->addSelect($field);
						}

					break;

				case 'sort':
					$sort = $value;

					break;

				case 'sortfield':
					$sortField = $value;

					break;

				case 'children':
					// @phpstan-ignore-next-line
					if ('*' == $value && $this->table)
						{
						$this->children = $this->table->getRecord()->getVirtualFields();
						}
					else
						{
						$this->children = \explode(',', (string)$value);
						}

					break;

				case 'related':
					// @phpstan-ignore-next-line
					if ('*' == $value && $this->table)
						{
						$this->related = [];

						foreach ($this->table->getRecord()->getFields() as $field => $definition)
							{
							if (\str_ends_with($field, 'Id'))
								{
								$this->related[] = \substr($field, 0, \strlen($field) - 2);
								}
							}
						}
					else
						{
						$this->related = \explode(',', (string)$value);
						}

					break;
				}
			}

		if ($sortField)
			{
			$this->table->addOrderBy($sortField, $sort);
			}

		return $this;
		}

	public function getData(\PHPFUI\ORM\Record $record) : array
		{
		$data = $record->toArray();

		if ($this->allowedFields)
			{
			$filtered = [];

			foreach ($this->allowedFields as $field)
				{
				$filtered[$field] = $data[$field];
				}
			$data = $filtered;
			}

		foreach ($this->disallowedFields as $field)
			{
			unset($data[$field]);
			}

		$relationships = $record->getVirtualFields();

		foreach ($this->children as $children)
			{
			$childTable = \lcfirst(\str_replace('Children', '', (string)$children));

			if (\in_array($children, $relationships) && $this->isAuthorized('GET', $childTable))
				{
				foreach ($record->{$children} as $child)
					{
					$data[$childTable][] = $child->toArray();
					}
				}
			}

		foreach ($this->related as $related)
			{
			$relatedId = $related . 'Id';

			if ($this->isAuthorized('GET', $related) && $record->getTableName() != $related && ! empty($record->{$relatedId}))
				{
				$data[$related] = $this->getData($record->{$related});
				}
			}

		return $data;
		}

	public function isAuthorized(string $method, string $tableName = '') : bool
		{
		if (! $tableName)
			{
			$tableName = $this->table?->getTableName() ?? 'x';
			}

		return isset($this->permissions[$tableName][$method]);
		}

	private function getOperator(string $symbol) : \PHPFUI\ORM\Operator
 {
	 return match ($symbol) {
		 '=' => new \PHPFUI\ORM\Operator\Equal(),
		 '!=' => new \PHPFUI\ORM\Operator\NotEqual(),
		 '>' => new \PHPFUI\ORM\Operator\GreaterThan(),
		 '>=' => new \PHPFUI\ORM\Operator\GreaterThanEqual(),
		 '<' => new \PHPFUI\ORM\Operator\LessThan(),
		 '<=' => new \PHPFUI\ORM\Operator\LessThanEqual(),
		 'IN' => new \PHPFUI\ORM\Operator\In(),
		 'NOT IN' => new \PHPFUI\ORM\Operator\NotIn(),
		 'LIKE' => new \PHPFUI\ORM\Operator\Like(),
		 'NOT LIKE' => new \PHPFUI\ORM\Operator\NotLike(),
		 default => throw new \Exception("'{$symbol}' is not a valid operator in where clause.  Must be one of (=, !=, >, >=, <, <=, LIKE, NOT LIKE, IN, NOT IN)"),
	 };
 }

	private function getCondition(?array $conditions) : \PHPFUI\ORM\Condition
		{
		$condition = new \PHPFUI\ORM\Condition();

		if (! $conditions)
			{
			return $condition;
			}

		foreach ($conditions as $row)
			{
			$subCondition = null;

			if (\is_array($row[1]))
				{
				$subCondition = $this->getCondition($row[1]);
				}
			else
				{
				$subCondition = new \PHPFUI\ORM\Condition($row[1], $row[3], $this->getOperator($row[2]));
				}

			switch ($row[0])
				{
				case 'AND':
					$condition->and($subCondition);

					break;

				case 'OR':
					$condition->or($subCondition);

					break;

				case 'OR NOT':
					$condition->orNot($subCondition);

					break;

				case 'AND NOT':
					$condition->andNot($subCondition);

					break;

				case '':
					$condition = $subCondition;

					break;

				default:
					throw new \Exception("'{$row[0]}' is not a valid logical condition in where clause.  Must be one of (AND, OR, AND NOT, OR NOT)");
				}
			}

		return $condition;
		}
	}
