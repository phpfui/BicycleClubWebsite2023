<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Ip_messaging
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\IpMessaging\V1\Service;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\Serialize;
use Twilio\Values;
use Twilio\Version;

class RoleContext extends InstanceContext
	{
	/**
	 * Initialize the RoleContext
	 *
	 * @param Version $version Version that contains the resource
	 * @param string $serviceSid
	 * @param string $sid
	 */
	public function __construct(
		Version $version,
		$serviceSid,
		$sid
	) {
		parent::__construct($version);

		// Path Solution
		$this->solution = [
			'serviceSid' => $serviceSid,
			'sid' => $sid,
		];

		$this->uri = '/Services/' . \rawurlencode($serviceSid)
		. '/Roles/' . \rawurlencode($sid)
		. '';
	}

	/**
	 * Provide a friendly representation
	 *
	 * @return string Machine friendly representation
	 */
	public function __toString() : string
	{
		$context = [];

		foreach ($this->solution as $key => $value) {
			$context[] = "{$key}={$value}";
		}

		return '[Twilio.IpMessaging.V1.RoleContext ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the RoleInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return bool True if delete succeeds, false otherwise
	 */
	public function delete() : bool
	{

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);

		return $this->version->delete('DELETE', $this->uri, [], [], $headers);
	}

	/**
	 * Fetch the RoleInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return RoleInstance Fetched RoleInstance
	 */
	public function fetch() : RoleInstance
	{

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->fetch('GET', $this->uri, [], [], $headers);

		return new RoleInstance(
			$this->version,
			$payload,
			$this->solution['serviceSid'],
			$this->solution['sid']
		);
	}

	/**
	 * Update the RoleInstance
	 *
	 * @param string[] $permission
	 * @throws TwilioException When an HTTP error occurs.
	 * @return RoleInstance Updated RoleInstance
	 */
	public function update(array $permission) : RoleInstance
	{

		$data = Values::of([
			'Permission' => Serialize::map($permission, static function($e) { return $e; }),
		]);

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->update('POST', $this->uri, [], $data, $headers);

		return new RoleInstance(
			$this->version,
			$payload,
			$this->solution['serviceSid'],
			$this->solution['sid']
		);
	}
}
