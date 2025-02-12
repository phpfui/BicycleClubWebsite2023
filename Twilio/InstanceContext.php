<?php

namespace Twilio;

class InstanceContext
{
	protected $solution = [];

	protected $uri;

	protected $version;

	public function __construct(Version $version) {
		$this->version = $version;
	}

	public function __toString() : string {
		return '[InstanceContext]';
	}
}
