<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Taskrouter
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Taskrouter\V1\Workspace;

use Twilio\Deserialize;
use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;

/**
 * @property string|null $accountSid
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateUpdated
 * @property string|null $friendlyName
 * @property string|null $sid
 * @property string|null $uniqueName
 * @property string|null $workspaceSid
 * @property bool|null $channelOptimizedRouting
 * @property string|null $url
 * @property array|null $links
 */
class TaskChannelInstance extends InstanceResource
{
	/**
	 * Initialize the TaskChannelInstance
	 *
	 * @param Version $version Version that contains the resource
	 * @param mixed[] $payload The response payload
	 * @param string $workspaceSid The SID of the Workspace that the new Task Channel belongs to.
	 * @param string $sid The SID of the Task Channel resource to delete.
	 */
	public function __construct(Version $version, array $payload, string $workspaceSid, ?string $sid = null)
	{
		parent::__construct($version);

		// Marshaled Properties
		$this->properties = [
			'accountSid' => Values::array_get($payload, 'account_sid'),
			'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
			'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
			'friendlyName' => Values::array_get($payload, 'friendly_name'),
			'sid' => Values::array_get($payload, 'sid'),
			'uniqueName' => Values::array_get($payload, 'unique_name'),
			'workspaceSid' => Values::array_get($payload, 'workspace_sid'),
			'channelOptimizedRouting' => Values::array_get($payload, 'channel_optimized_routing'),
			'url' => Values::array_get($payload, 'url'),
			'links' => Values::array_get($payload, 'links'),
		];

		$this->solution = ['workspaceSid' => $workspaceSid, 'sid' => $sid ?: $this->properties['sid'], ];
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

		return '[Twilio.Taskrouter.V1.TaskChannelInstance ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the TaskChannelInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return bool True if delete succeeds, false otherwise
	 */
	public function delete() : bool
	{

		return $this->proxy()->delete();
	}

	/**
	 * Fetch the TaskChannelInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return TaskChannelInstance Fetched TaskChannelInstance
	 */
	public function fetch() : TaskChannelInstance
	{

		return $this->proxy()->fetch();
	}

	/**
	 * Update the TaskChannelInstance
	 *
	 * @param array|Options $options Optional Arguments
	 * @throws TwilioException When an HTTP error occurs.
	 * @return TaskChannelInstance Updated TaskChannelInstance
	 */
	public function update(array $options = []) : TaskChannelInstance
	{

		return $this->proxy()->update($options);
	}

	/**
	 * Generate an instance context for the instance, the context is capable of
	 * performing various actions.  All instance actions are proxied to the context
	 *
	 * @return TaskChannelContext Context for this TaskChannelInstance
	 */
	protected function proxy() : TaskChannelContext
	{
		if (! $this->context) {
			$this->context = new TaskChannelContext(
				$this->version,
				$this->solution['workspaceSid'],
				$this->solution['sid']
			);
		}

		return $this->context;
	}
}
