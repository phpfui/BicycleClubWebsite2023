<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Bulkexports
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Bulkexports\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\Options;
use Twilio\Serialize;
use Twilio\Values;
use Twilio\Version;

class ExportConfigurationContext extends InstanceContext
	{
	/**
	 * Initialize the ExportConfigurationContext
	 *
	 * @param Version $version Version that contains the resource
	 * @param string $resourceType The type of communication – Messages, Calls, Conferences, and Participants
	 */
	public function __construct(
		Version $version,
		$resourceType
	) {
		parent::__construct($version);

		// Path Solution
		$this->solution = [
			'resourceType' => $resourceType,
		];

		$this->uri = '/Exports/' . \rawurlencode($resourceType)
		. '/Configuration';
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

		return '[Twilio.Bulkexports.V1.ExportConfigurationContext ' . \implode(' ', $context) . ']';
	}

	/**
	 * Fetch the ExportConfigurationInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return ExportConfigurationInstance Fetched ExportConfigurationInstance
	 */
	public function fetch() : ExportConfigurationInstance
	{

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->fetch('GET', $this->uri, [], [], $headers);

		return new ExportConfigurationInstance(
			$this->version,
			$payload,
			$this->solution['resourceType']
		);
	}

	/**
	 * Update the ExportConfigurationInstance
	 *
	 * @param array|Options $options Optional Arguments
	 * @throws TwilioException When an HTTP error occurs.
	 * @return ExportConfigurationInstance Updated ExportConfigurationInstance
	 */
	public function update(array $options = []) : ExportConfigurationInstance
	{

		$options = new Values($options);

		$data = Values::of([
			'Enabled' => Serialize::booleanToString($options['enabled']),
			'WebhookUrl' => $options['webhookUrl'],
			'WebhookMethod' => $options['webhookMethod'],
		]);

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->update('POST', $this->uri, [], $data, $headers);

		return new ExportConfigurationInstance(
			$this->version,
			$payload,
			$this->solution['resourceType']
		);
	}
}
