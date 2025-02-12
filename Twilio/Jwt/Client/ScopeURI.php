<?php

namespace Twilio\Jwt\Client;

/**
 * Scope URI implementation
 *
 * Simple way to represent configurable privileges in an OAuth
 * friendly way. For our case, they look like this:
 *
 * scope:<service>:<privilege>?<params>
 *
 * For example:
 * scope:client:incoming?name=jonas
 */
class ScopeURI
{
	public $params;

	public $privilege;

	public $service;

	public function __construct(string $service, string $privilege, array $params = []) {
		$this->service = $service;
		$this->privilege = $privilege;
		$this->params = $params;
	}

	/**
	 * Parse a scope URI into a ScopeURI object
	 *
	 * @param string $uri The scope URI
	 * @throws \UnexpectedValueException
	 * @return ScopeURI The parsed scope uri
	 */
	public static function parse(string $uri) : ScopeURI {
		if (0 !== \strpos($uri, 'scope:')) {
			throw new \UnexpectedValueException(
				'Not a scope URI according to scheme'
			);
		}

		$parts = \explode('?', $uri, 1);
		$params = null;

		if (\count($parts) > 1) {
			\parse_str($parts[1], $params);
		}

		$parts = \explode(':', $parts[0], 2);

		if (3 !== \count($parts)) {
			throw new \UnexpectedValueException(
				'Not enough parts for scope URI'
			);
		}

		[$scheme, $service, $privilege] = $parts;

		return new ScopeURI($service, $privilege, $params);
	}

	public function toString() : string {
		$uri = "scope:{$this->service}:{$this->privilege}";

		if (\count($this->params)) {
			$uri .= '?' . \http_build_query($this->params, '', '&');
		}

		return $uri;
	}
}
