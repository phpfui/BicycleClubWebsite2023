<?php

namespace Tinify;

class Source
{
	private $url;

	private $commands;

	public function __construct($url, $commands = []) {
		$this->url = $url;
		$this->commands = $commands;
	}

	public function convert($options) {
		$commands = \array_merge($this->commands, ['convert' => $options]);

		return new self($this->url, $commands);
	}

	public static function fromBuffer($string) {
		$response = Tinify::getClient()->request('post', '/shrink', $string);

		return new self($response->headers['location']);
	}

	public static function fromFile($path) {
		return self::fromBuffer(\file_get_contents($path));
	}

	public static function fromUrl($url) {
		$body = ['source' => ['url' => $url]];
		$response = Tinify::getClient()->request('post', '/shrink', $body);

		return new self($response->headers['location']);
	}

	public function preserve() {
		$options = $this->flatten(\func_get_args());
		$commands = \array_merge($this->commands, ['preserve' => $options]);

		return new self($this->url, $commands);
	}

	public function resize($options) {
		$commands = \array_merge($this->commands, ['resize' => $options]);

		return new self($this->url, $commands);
	}

	public function result() {
		$response = Tinify::getClient()->request('get', $this->url, $this->commands);

		return new Result($response->headers, $response->body);
	}

	public function store($options) {
		$response = Tinify::getClient()->request(
			'post',
			$this->url,
			\array_merge($this->commands, ['store' => $options])
		);

		return new Result($response->headers, $response->body);
	}

	public function toBuffer() {
		return $this->result()->toBuffer();
	}

	public function toFile($path) {
		return $this->result()->toFile($path);
	}

	public function transform($options) {
		$commands = \array_merge($this->commands, ['transform' => $options]);

		return new self($this->url, $commands);
	}

	private static function flatten($options) {
		$flattened = [];

		foreach ($options as $option) {
			if (\is_array($option)) {
				$flattened = \array_merge($flattened, $option);
			} else {
				$flattened[] = $option;
			}
		}

		return $flattened;
	}
}
