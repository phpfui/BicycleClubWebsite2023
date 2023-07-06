<?php

namespace Tinify;

class Result extends ResultMeta
{
	protected $data;

	public function __construct($meta, $data) {
		$this->meta = $meta;
		$this->data = $data;
	}

	public function contentType() {
		return $this->mediaType();
	}

	public function data() {
		return $this->data;
	}

	public function mediaType() {
		return $this->meta['content-type'];
	}

	public function size() {
		return (int)($this->meta['content-length']);
	}

	public function toBuffer() {
		return $this->data;
	}

	public function toFile($path) {
		return \file_put_contents($path, $this->toBuffer());
	}
}
