<?php

namespace App\API\V1;

class Schema extends \App\View\API\Base implements \PHPFUI\Interfaces\NanoClass
	{
	/** @param array<string> $parameters */
	public function landingPage(array $parameters = []) : void
		{
		if ('GET' != $_SERVER['REQUEST_METHOD'])
			{
			\http_response_code(405);

			return;
			}

		try
			{
			if (\count($parameters) < 2)
				{
				$response = [];

				// use a known table to avoid false error reporting
				$model = new \App\Model\API('setting', $this->controller);
				// log any errors
				if ($model->getErrors())
					{
					foreach ($model->getErrors() as $error)
						{
						$this->logError($error, 401);
						}

					return;
					}

				foreach (\PHPFUI\ORM\Table::getAllTables(['Setting', 'OauthToken', 'OauthUser']) as $table)
					{
					$tableName = $table->getTableName();

					if ($model->isAuthorized('GET', $tableName))
						{
						$response[$tableName] = $this->getSchema($table);
						}
					}
				$this->setResponse($response);
				}
			else
				{
				$model = new \App\Model\API($parameters[1], $this->controller);
				// log any errors
				if ($model->getErrors())
					{
					foreach ($model->getErrors() as $error)
						{
						$this->logError($error, 401);
						}

					return;
					}

				$table = $model->getTable();

				$tableName = $table->getTableName();

				if ($model->isAuthorized('GET', $tableName))
					{
					$this->setResponse([$tableName => $this->getSchema($table)]);
					}
				else
					{
					$this->logError('You are not authorized for table ' . $tableName, 401);
					}
				}
			}
		catch (\Throwable $e)
			{
			$this->logError($e->getMessage(), 400);
			}
		}

	/** @return array<string, mixed> */
	private function getSchema(\PHPFUI\ORM\Table $table) : array
		{
		$fields = $table->getFields();
		$keys = ['mysql_type', 'php_type', 'length', 'key', 'nullable', 'default', ];

		$response = [];

		foreach ($fields as $key => $row)
			{
			$schema = [];

			foreach ($row as $index => $value)
				{
				$schema[$keys[$index]] = $value;
				}
			$response[$key] = $schema;
			}
		$response['primaryKeys'] = $table->getPrimaryKeys();
		$response['related'] = $table->getRecord()->getVirtualFields();

		return $response;
		}
	}
