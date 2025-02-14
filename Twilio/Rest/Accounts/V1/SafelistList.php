<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Accounts
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Accounts\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\ListResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;

class SafelistList extends ListResource
	{
	/**
	 * Construct the SafelistList
	 *
	 * @param Version $version Version that contains the resource
	 */
	public function __construct(
		Version $version
	) {
		parent::__construct($version);

		// Path Solution
		$this->solution = [
		];

		$this->uri = '/SafeList/Numbers';
	}

	/**
	 * Provide a friendly representation
	 *
	 * @return string Machine friendly representation
	 */
	public function __toString() : string
	{
		return '[Twilio.Accounts.V1.SafelistList]';
	}

	/**
	 * Create the SafelistInstance
	 *
	 * @param string $phoneNumber The phone number to be added in SafeList. Phone numbers must be in [E.164 format](https://www.twilio.com/docs/glossary/what-e164).
	 * @throws TwilioException When an HTTP error occurs.
	 * @return SafelistInstance Created SafelistInstance
	 */
	public function create(string $phoneNumber) : SafelistInstance
	{

		$data = Values::of([
			'PhoneNumber' => $phoneNumber,
		]);

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->create('POST', $this->uri, [], $data, $headers);

		return new SafelistInstance(
			$this->version,
			$payload
		);
	}

	/**
	 * Delete the SafelistInstance
	 *
	 * @param array|Options $options Optional Arguments
	 * @throws TwilioException When an HTTP error occurs.
	 * @return bool True if delete succeeds, false otherwise
	 */
	public function delete(array $options = []) : bool
	{

		$options = new Values($options);

		$params = Values::of([
			'PhoneNumber' => $options['phoneNumber'],
		]);

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);

		return $this->version->delete('DELETE', $this->uri, $params, [], $headers);
	}

	/**
	 * Fetch the SafelistInstance
	 *
	 * @param array|Options $options Optional Arguments
	 * @throws TwilioException When an HTTP error occurs.
	 * @return SafelistInstance Fetched SafelistInstance
	 */
	public function fetch(array $options = []) : SafelistInstance
	{

		$options = new Values($options);

		$params = Values::of([
			'PhoneNumber' => $options['phoneNumber'],
		]);

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->fetch('GET', $this->uri, $params, [], $headers);

		return new SafelistInstance(
			$this->version,
			$payload
		);
	}
}
