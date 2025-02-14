<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Api
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Api\V2010\Account\Sip\IpAccessControlList;

use Twilio\Deserialize;
use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;

/**
 * @property string|null $sid
 * @property string|null $accountSid
 * @property string|null $friendlyName
 * @property string|null $ipAddress
 * @property int $cidrPrefixLength
 * @property string|null $ipAccessControlListSid
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateUpdated
 * @property string|null $uri
 */
class IpAddressInstance extends InstanceResource
{
	/**
	 * Initialize the IpAddressInstance
	 *
	 * @param Version $version Version that contains the resource
	 * @param mixed[] $payload The response payload
	 * @param string $accountSid The unique id of the [Account](https://www.twilio.com/docs/iam/api/account) responsible for this resource.
	 * @param string $ipAccessControlListSid The IpAccessControlList Sid with which to associate the created IpAddress resource.
	 * @param string $sid A 34 character string that uniquely identifies the resource to delete.
	 */
	public function __construct(Version $version, array $payload, string $accountSid, string $ipAccessControlListSid, ?string $sid = null)
	{
		parent::__construct($version);

		// Marshaled Properties
		$this->properties = [
			'sid' => Values::array_get($payload, 'sid'),
			'accountSid' => Values::array_get($payload, 'account_sid'),
			'friendlyName' => Values::array_get($payload, 'friendly_name'),
			'ipAddress' => Values::array_get($payload, 'ip_address'),
			'cidrPrefixLength' => Values::array_get($payload, 'cidr_prefix_length'),
			'ipAccessControlListSid' => Values::array_get($payload, 'ip_access_control_list_sid'),
			'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
			'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
			'uri' => Values::array_get($payload, 'uri'),
		];

		$this->solution = ['accountSid' => $accountSid, 'ipAccessControlListSid' => $ipAccessControlListSid, 'sid' => $sid ?: $this->properties['sid'], ];
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

		return '[Twilio.Api.V2010.IpAddressInstance ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the IpAddressInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return bool True if delete succeeds, false otherwise
	 */
	public function delete() : bool
	{

		return $this->proxy()->delete();
	}

	/**
	 * Fetch the IpAddressInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return IpAddressInstance Fetched IpAddressInstance
	 */
	public function fetch() : IpAddressInstance
	{

		return $this->proxy()->fetch();
	}

	/**
	 * Update the IpAddressInstance
	 *
	 * @param array|Options $options Optional Arguments
	 * @throws TwilioException When an HTTP error occurs.
	 * @return IpAddressInstance Updated IpAddressInstance
	 */
	public function update(array $options = []) : IpAddressInstance
	{

		return $this->proxy()->update($options);
	}

	/**
	 * Generate an instance context for the instance, the context is capable of
	 * performing various actions.  All instance actions are proxied to the context
	 *
	 * @return IpAddressContext Context for this IpAddressInstance
	 */
	protected function proxy() : IpAddressContext
	{
		if (! $this->context) {
			$this->context = new IpAddressContext(
				$this->version,
				$this->solution['accountSid'],
				$this->solution['ipAccessControlListSid'],
				$this->solution['sid']
			);
		}

		return $this->context;
	}
}
