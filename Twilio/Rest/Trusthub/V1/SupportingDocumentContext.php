<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Trusthub
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Trusthub\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\Options;
use Twilio\Serialize;
use Twilio\Values;
use Twilio\Version;

class SupportingDocumentContext extends InstanceContext
	{
	/**
	 * Initialize the SupportingDocumentContext
	 *
	 * @param Version $version Version that contains the resource
	 * @param string $sid The unique string created by Twilio to identify the Supporting Document resource.
	 */
	public function __construct(
		Version $version,
		$sid
	) {
		parent::__construct($version);

		// Path Solution
		$this->solution = [
			'sid' => $sid,
		];

		$this->uri = '/SupportingDocuments/' . \rawurlencode($sid)
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

		return '[Twilio.Trusthub.V1.SupportingDocumentContext ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the SupportingDocumentInstance
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
	 * Fetch the SupportingDocumentInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return SupportingDocumentInstance Fetched SupportingDocumentInstance
	 */
	public function fetch() : SupportingDocumentInstance
	{

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->fetch('GET', $this->uri, [], [], $headers);

		return new SupportingDocumentInstance(
			$this->version,
			$payload,
			$this->solution['sid']
		);
	}

	/**
	 * Update the SupportingDocumentInstance
	 *
	 * @param array|Options $options Optional Arguments
	 * @throws TwilioException When an HTTP error occurs.
	 * @return SupportingDocumentInstance Updated SupportingDocumentInstance
	 */
	public function update(array $options = []) : SupportingDocumentInstance
	{

		$options = new Values($options);

		$data = Values::of([
			'FriendlyName' => $options['friendlyName'],
			'Attributes' => Serialize::jsonObject($options['attributes']),
		]);

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->update('POST', $this->uri, [], $data, $headers);

		return new SupportingDocumentInstance(
			$this->version,
			$payload,
			$this->solution['sid']
		);
	}
}
