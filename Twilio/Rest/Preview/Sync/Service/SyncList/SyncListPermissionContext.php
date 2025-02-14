<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Preview
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Preview\Sync\Service\SyncList;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\Serialize;
use Twilio\Values;
use Twilio\Version;

class SyncListPermissionContext extends InstanceContext
	{
	/**
	 * Initialize the SyncListPermissionContext
	 *
	 * @param Version $version Version that contains the resource
	 * @param string $serviceSid
	 * @param string $listSid Identifier of the Sync List. Either a SID or a unique name.
	 * @param string $identity Arbitrary string identifier representing a user associated with an FPA token, assigned by the developer.
	 */
	public function __construct(
		Version $version,
		$serviceSid,
		$listSid,
		$identity
	) {
		parent::__construct($version);

		// Path Solution
		$this->solution = [
			'serviceSid' => $serviceSid,
			'listSid' => $listSid,
			'identity' => $identity,
		];

		$this->uri = '/Services/' . \rawurlencode($serviceSid)
		. '/Lists/' . \rawurlencode($listSid)
		. '/Permissions/' . \rawurlencode($identity)
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

		return '[Twilio.Preview.Sync.SyncListPermissionContext ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the SyncListPermissionInstance
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
	 * Fetch the SyncListPermissionInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return SyncListPermissionInstance Fetched SyncListPermissionInstance
	 */
	public function fetch() : SyncListPermissionInstance
	{

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->fetch('GET', $this->uri, [], [], $headers);

		return new SyncListPermissionInstance(
			$this->version,
			$payload,
			$this->solution['serviceSid'],
			$this->solution['listSid'],
			$this->solution['identity']
		);
	}

	/**
	 * Update the SyncListPermissionInstance
	 *
	 * @param bool $read Boolean flag specifying whether the identity can read the Sync List.
	 * @param bool $write Boolean flag specifying whether the identity can create, update and delete Items of the Sync List.
	 * @param bool $manage Boolean flag specifying whether the identity can delete the Sync List.
	 * @throws TwilioException When an HTTP error occurs.
	 * @return SyncListPermissionInstance Updated SyncListPermissionInstance
	 */
	public function update(bool $read, bool $write, bool $manage) : SyncListPermissionInstance
	{

		$data = Values::of([
			'Read' => Serialize::booleanToString($read),
			'Write' => Serialize::booleanToString($write),
			'Manage' => Serialize::booleanToString($manage),
		]);

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->update('POST', $this->uri, [], $data, $headers);

		return new SyncListPermissionInstance(
			$this->version,
			$payload,
			$this->solution['serviceSid'],
			$this->solution['listSid'],
			$this->solution['identity']
		);
	}
}
