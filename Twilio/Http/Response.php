<?php

namespace Twilio\Http;

class Response
{
	protected $content;

	protected $headers;

	protected $statusCode;

	public function __construct(int $statusCode, ?string $content, ?array $headers = []) {
		$this->statusCode = $statusCode;
		$this->content = $content;
		$this->headers = $headers;
	}

	public function __toString() : string {
		return '[Response] HTTP ' . $this->getStatusCode() . ' ' . $this->content;
	}

	public function getContent() {
		return \json_decode($this->content, true);
	}

	public function getHeaders() : array {
		return $this->headers;
	}

	public function getStatusCode() : int {
		return $this->statusCode;
	}

	public function ok() : bool {
		return $this->getStatusCode() < 400;
	}
}
