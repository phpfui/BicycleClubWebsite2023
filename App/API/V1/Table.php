<?php

namespace App\API\V1;

class Table extends \App\View\API\Base implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Model\API $model;

	private readonly string $uri;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->uri = $controller->getUri();
		$parts = \explode('/', $this->uri);
		$this->model = new \App\Model\API($parts[3] ?? '', $this->controller);

		// log any errors
		if ($this->model->getErrors())
			{
			foreach ($this->model->getErrors() as $error)
				{
				$this->logError($error, 401);
				}

			return;
			}

		try
			{
			$this->model->applyParameters($controller->getGet());
			}
		catch (\Throwable $e)
			{
			$this->model->nullTable();
			$this->logError($e->getMessage(), 400);
			}
		}

	/** @param array<string, string> $parameters */
	public function landingPage(array $parameters = []) : void
		{
		$table = $this->model->getTable();

		if (! $table)
			{
			return;
			}

		if (! $this->model->isAuthorized($_SERVER['REQUEST_METHOD']))
			{
			\http_response_code(401);

			return;
			}
		$record = null;

		if (\count($parameters) > 2)
			{
			\array_shift($parameters);
			\array_shift($parameters);
			$keys = [];

			foreach ($table->getPrimaryKeys() as $key)
				{
				$keys[$key] = \array_shift($parameters);
				}
			$record = $table->getRecord();
			$record->setFrom($keys);
			}

		try
			{
			switch ($_SERVER['REQUEST_METHOD'])
				{
				case 'GET':
					if ($record)
						{
						$record->read($keys);

						if ($record->loaded())
							{
							$this->setResponse($this->model->getData($record, $this->model->getRequestedRelated()));
							}
						else
							{
							$this->logError('Record not found', 404);
							}
						}
					else
						{
						$response = [];
						$cursor = $table->getRecordCursor();
						$next = $this->model->getNextLink();

						if ($next && $cursor->count() == $table->getLimit())
							{
							$this->log($next, 'next');
							}
						$prev = $this->model->getPrevLink();

						if ($prev)
							{
							$this->log($prev, 'prev');
							}

						foreach ($cursor as $record)
							{
							$response[] = $this->model->getData($record, $this->model->getRequestedRelated());
							}
						$this->setResponse($response); // @phpstan-ignore-line
						}

					break;

				case 'PUT':
					$data = \json_decode(\file_get_contents('php://input'), true);

					if (null === $data)
						{
						$this->logError('Body json is maliformed', 406);
						}
					else
						{
						if ($record)
							{
							$record->reload();

							if ($record->loaded())
								{
								$record->setFrom($data);
								$errors = $record->validate();

								if (! $errors)
									{
									$record->update();
									}
								else
									{
									$this->logError($errors, 406);
									}
								}
							else
								{
								$this->logError('Record not found', 404);
								}
							}
						elseif (! \count($table->getWhereCondition()))
							{
							$this->logError('PUT all records not allowed', 406);
							}
						else
							{
							$table->update($data);
							}
						}

					break;

				case 'POST':
					$record = $table->getRecord();
					$record->setEmpty();
					$post = $this->controller->getPost();
					unset($post['password']);
					$record->setFrom($post);
					$errors = $record->validate();

					if (! $errors)
						{
						if ($record->insert())
							{
							\http_response_code(201);
							$this->setResponse($record->toArray());
							}
						else
							{
							$this->logError(\PHPFUI\ORM::getLastError(), 406);
							}
						}
					else
						{
						$this->logError($errors, 406);
						}

					break;

				case 'DELETE':
					if ($record)
						{
						$record->reload();

						if ($record->loaded())
							{
							\http_response_code(204);
							$record->delete();
							}
						else
							{
							$this->logError('Record not found', 404);
							}
						}
					else
						{
						if (! \count($table->getWhereCondition()))
							{
							$this->logError('DELETE all records not allowed', 406);
							}
						else
							{
							\http_response_code(204);
							$table->delete();
							}
						}

					break;
				}

			foreach (\PHPFUI\ORM::getLastErrors() as $errors)
				{
				$this->logError($errors['error'], 400);
				}
			}
		catch (\Throwable $e)
			{
			$this->logError($e->getMessage(), 400);
			}
		}
	}
