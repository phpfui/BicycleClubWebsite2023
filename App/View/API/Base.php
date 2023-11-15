<?php

namespace App\View\API;

class Base implements \Stringable
	{
	/**
	 * @var array<string,mixed>
	 */
	private array $meta = [];

	/**
	 * @var array<string,array<string,mixed>>
	 */
	private array $response = [];

	public function __construct(protected \PHPFUI\Interfaces\NanoController $controller)
		{
		}

	public function __toString() : string
		{
		\header('Content-Type: application/json');

		foreach ($this->meta as $index => $data)
			{
			if (1 == (\is_countable($data) ? \count($data) : 0))
				{
				$this->meta[$index] = \array_shift($data);
				}
			}
		$jsonArray = $this->meta;

		if (\count($this->response))
			{
			$jsonArray['data'] = $this->response;
			}

		return \json_encode($jsonArray, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
		}

	public function log(mixed $data, string $type = 'status') : static
		{
		$this->meta[$type][] = $data;

		return $this;
		}

	public function logError(mixed $error, int $responseCode) : static
		{
		\http_response_code($responseCode);

		return $this->log($error, 'errors');
		}

	/**
	 * @param array<string,array<string,mixed>> $response
	 */
	public function setResponse(array $response) : static
		{
		$this->response = $response;

		return $this;
		}
	}
