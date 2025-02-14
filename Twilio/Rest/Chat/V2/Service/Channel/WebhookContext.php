<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Chat
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Chat\V2\Service\Channel;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\Options;
use Twilio\Serialize;
use Twilio\Values;
use Twilio\Version;

class WebhookContext extends InstanceContext
	{
	/**
	 * Initialize the WebhookContext
	 *
	 * @param Version $version Version that contains the resource
	 * @param string $serviceSid The SID of the [Service](https://www.twilio.com/docs/chat/rest/service-resource) with the Channel to create the Webhook resource under.
	 * @param string $channelSid The SID of the [Channel](https://www.twilio.com/docs/chat/channels) the new Channel Webhook resource belongs to. This value can be the Channel resource's `sid` or `unique_name`.
	 * @param string $sid The SID of the Channel Webhook resource to delete.
	 */
	public function __construct(
		Version $version,
		$serviceSid,
		$channelSid,
		$sid
	) {
		parent::__construct($version);

		// Path Solution
		$this->solution = [
			'serviceSid' => $serviceSid,
			'channelSid' => $channelSid,
			'sid' => $sid,
		];

		$this->uri = '/Services/' . \rawurlencode($serviceSid)
		. '/Channels/' . \rawurlencode($channelSid)
		. '/Webhooks/' . \rawurlencode($sid)
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

		return '[Twilio.Chat.V2.WebhookContext ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the WebhookInstance
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
	 * Fetch the WebhookInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return WebhookInstance Fetched WebhookInstance
	 */
	public function fetch() : WebhookInstance
	{

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->fetch('GET', $this->uri, [], [], $headers);

		return new WebhookInstance(
			$this->version,
			$payload,
			$this->solution['serviceSid'],
			$this->solution['channelSid'],
			$this->solution['sid']
		);
	}

	/**
	 * Update the WebhookInstance
	 *
	 * @param array|Options $options Optional Arguments
	 * @throws TwilioException When an HTTP error occurs.
	 * @return WebhookInstance Updated WebhookInstance
	 */
	public function update(array $options = []) : WebhookInstance
	{

		$options = new Values($options);

		$data = Values::of([
			'Configuration.Url' => $options['configurationUrl'],
			'Configuration.Method' => $options['configurationMethod'],
			'Configuration.Filters' => Serialize::map($options['configurationFilters'], static function($e) { return $e; }),
			'Configuration.Triggers' => Serialize::map($options['configurationTriggers'], static function($e) { return $e; }),
			'Configuration.FlowSid' => $options['configurationFlowSid'],
			'Configuration.RetryCount' => $options['configurationRetryCount'],
		]);

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->update('POST', $this->uri, [], $data, $headers);

		return new WebhookInstance(
			$this->version,
			$payload,
			$this->solution['serviceSid'],
			$this->solution['channelSid'],
			$this->solution['sid']
		);
	}
}
