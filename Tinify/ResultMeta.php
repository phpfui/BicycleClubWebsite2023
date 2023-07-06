<?php

namespace Tinify;

class ResultMeta
{
	protected $meta;

	public function __construct($meta) {
		$this->meta = $meta;
	}

	public function extension() {
		if (isset($this->meta['content-type'])) {
			$parts = \explode('/', $this->meta['content-type']);

			return \end($parts);
		}

	}

	public function height() {
		return (int)($this->meta['image-height']);
	}

	public function location() {
		return $this->meta['location'] ?? null;
	}

	public function width() {
		return (int)($this->meta['image-width']);
	}
}
