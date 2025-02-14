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

namespace Twilio\Rest\Preview\Sync\Service\SyncMap;

use Twilio\Deserialize;
use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;

/**
 * @property string|null $key
 * @property string|null $accountSid
 * @property string|null $serviceSid
 * @property string|null $mapSid
 * @property string|null $url
 * @property string|null $revision
 * @property array|null $data
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateUpdated
 * @property string|null $createdBy
 */
class SyncMapItemInstance extends InstanceResource
{
	/**
	 * Initialize the SyncMapItemInstance
	 *
	 * @param Version $version Version that contains the resource
	 * @param mixed[] $payload The response payload
	 */
	public function __construct(Version $version, array $payload, string $serviceSid, string $mapSid, ?string $key = null)
	{
		parent::__construct($version);

		// Marshaled Properties
		$this->properties = [
			'key' => Values::array_get($payload, 'key'),
			'accountSid' => Values::array_get($payload, 'account_sid'),
			'serviceSid' => Values::array_get($payload, 'service_sid'),
			'mapSid' => Values::array_get($payload, 'map_sid'),
			'url' => Values::array_get($payload, 'url'),
			'revision' => Values::array_get($payload, 'revision'),
			'data' => Values::array_get($payload, 'data'),
			'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
			'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
			'createdBy' => Values::array_get($payload, 'created_by'),
		];

		$this->solution = ['serviceSid' => $serviceSid, 'mapSid' => $mapSid, 'key' => $key ?: $this->properties['key'], ];
	}

	/**
	 * Magic getter to access properties
	 *
	 * @param string $name Property to access
	 * @throws TwilioException For unknown properties
	 * @return mixed The requested property
	 */
	public function __get(string $name)
	{
		if (\array_key_exists($name, $this->properties)) {
			return $this->properties[$name];
		}

		if (\property_exists($this, '_' . $name)) {
			$method = 'get' . \ucfirst($name);

			return $this->{$method}();
		}

		throw new TwilioException('Unknown property: ' . $name);
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

		return '[Twilio.Preview.Sync.SyncMapItemInstance ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the SyncMapItemInstance
	 *
	 * @param array|Options $options Optional Arguments
	 * @throws TwilioException When an HTTP error occurs.
	 * @return bool True if delete succeeds, false otherwise
	 */
	public function delete(array $options = []) : bool
	{

		return $this->proxy()->delete($options);
	}

	/**
	 * Fetch the SyncMapItemInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return SyncMapItemInstance Fetched SyncMapItemInstance
	 */
	public function fetch() : SyncMapItemInstance
	{

		return $this->proxy()->fetch();
	}

	/**
	 * Update the SyncMapItemInstance
	 *
	 * @param array|Options $options Optional Arguments
	 * @throws TwilioException When an HTTP error occurs.
	 * @return SyncMapItemInstance Updated SyncMapItemInstance
	 */
	public function update(array $data, array $options = []) : SyncMapItemInstance
	{

		return $this->proxy()->update($data, $options);
	}

	/**
	 * Generate an instance context for the instance, the context is capable of
	 * performing various actions.  All instance actions are proxied to the context
	 *
	 * @return SyncMapItemContext Context for this SyncMapItemInstance
	 */
	protected function proxy() : SyncMapItemContext
	{
		if (! $this->context) {
			$this->context = new SyncMapItemContext(
				$this->version,
				$this->solution['serviceSid'],
				$this->solution['mapSid'],
				$this->solution['key']
			);
		}

		return $this->context;
	}
}
