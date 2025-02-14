<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Messaging
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Messaging\V1\Service;

use Twilio\Deserialize;
use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Values;
use Twilio\Version;

/**
 * @property string|null $sid
 * @property string|null $accountSid
 * @property string|null $serviceSid
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateUpdated
 * @property string|null $phoneNumber
 * @property string|null $countryCode
 * @property string[]|null $capabilities
 * @property string|null $url
 */
class PhoneNumberInstance extends InstanceResource
{
	/**
	 * Initialize the PhoneNumberInstance
	 *
	 * @param Version $version Version that contains the resource
	 * @param mixed[] $payload The response payload
	 * @param string $serviceSid The SID of the [Service](https://www.twilio.com/docs/chat/rest/service-resource) to create the resource under.
	 * @param string $sid The SID of the PhoneNumber resource to delete.
	 */
	public function __construct(Version $version, array $payload, string $serviceSid, ?string $sid = null)
	{
		parent::__construct($version);

		// Marshaled Properties
		$this->properties = [
			'sid' => Values::array_get($payload, 'sid'),
			'accountSid' => Values::array_get($payload, 'account_sid'),
			'serviceSid' => Values::array_get($payload, 'service_sid'),
			'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
			'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
			'phoneNumber' => Values::array_get($payload, 'phone_number'),
			'countryCode' => Values::array_get($payload, 'country_code'),
			'capabilities' => Values::array_get($payload, 'capabilities'),
			'url' => Values::array_get($payload, 'url'),
		];

		$this->solution = ['serviceSid' => $serviceSid, 'sid' => $sid ?: $this->properties['sid'], ];
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

		return '[Twilio.Messaging.V1.PhoneNumberInstance ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the PhoneNumberInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return bool True if delete succeeds, false otherwise
	 */
	public function delete() : bool
	{

		return $this->proxy()->delete();
	}

	/**
	 * Fetch the PhoneNumberInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return PhoneNumberInstance Fetched PhoneNumberInstance
	 */
	public function fetch() : PhoneNumberInstance
	{

		return $this->proxy()->fetch();
	}

	/**
	 * Generate an instance context for the instance, the context is capable of
	 * performing various actions.  All instance actions are proxied to the context
	 *
	 * @return PhoneNumberContext Context for this PhoneNumberInstance
	 */
	protected function proxy() : PhoneNumberContext
	{
		if (! $this->context) {
			$this->context = new PhoneNumberContext(
				$this->version,
				$this->solution['serviceSid'],
				$this->solution['sid']
			);
		}

		return $this->context;
	}
}
