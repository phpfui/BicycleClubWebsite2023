<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Studio
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Studio\V1\Flow;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\ListResource;
use Twilio\Rest\Studio\V1\Flow\Engagement\EngagementContextList;
use Twilio\Rest\Studio\V1\Flow\Engagement\StepList;
use Twilio\Values;
use Twilio\Version;

/**
 * @property StepList $steps
 * @property EngagementContextList $engagementContext
 * @method \Twilio\Rest\Studio\V1\Flow\Engagement\StepContext steps(string $sid)
 * @method \Twilio\Rest\Studio\V1\Flow\Engagement\EngagementContextContext engagementContext()
 */
class EngagementContext extends InstanceContext
	{
	protected $_engagementContext;

	protected $_steps;

	/**
	 * Initialize the EngagementContext
	 *
	 * @param Version $version Version that contains the resource
	 * @param string $flowSid The SID of the Flow.
	 * @param string $sid The SID of the Engagement resource to delete.
	 */
	public function __construct(
		Version $version,
		$flowSid,
		$sid
	) {
		parent::__construct($version);

		// Path Solution
		$this->solution = [
			'flowSid' => $flowSid,
			'sid' => $sid,
		];

		$this->uri = '/Flows/' . \rawurlencode($flowSid)
		. '/Engagements/' . \rawurlencode($sid)
		. '';
	}

	/**
	 * Magic caller to get resource contexts
	 *
	 * @param string $name Resource to return
	 * @param array $arguments Context parameters
	 * @throws TwilioException For unknown resource
	 * @return InstanceContext The requested resource context
	 */
	public function __call(string $name, array $arguments) : InstanceContext
	{
		$property = $this->{$name};

		if (\method_exists($property, 'getContext')) {
			return \call_user_func_array([$property, 'getContext'], $arguments);
		}

		throw new TwilioException('Resource does not have a context');
	}

	/**
	 * Magic getter to lazy load subresources
	 *
	 * @param string $name Subresource to return
	 * @throws TwilioException For unknown subresources
	 * @return ListResource The requested subresource
	 */
	public function __get(string $name) : ListResource
	{
		if (\property_exists($this, '_' . $name)) {
			$method = 'get' . \ucfirst($name);

			return $this->{$method}();
		}

		throw new TwilioException('Unknown subresource ' . $name);
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

		return '[Twilio.Studio.V1.EngagementContext ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the EngagementInstance
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
	 * Fetch the EngagementInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return EngagementInstance Fetched EngagementInstance
	 */
	public function fetch() : EngagementInstance
	{

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->fetch('GET', $this->uri, [], [], $headers);

		return new EngagementInstance(
			$this->version,
			$payload,
			$this->solution['flowSid'],
			$this->solution['sid']
		);
	}

	/**
	 * Access the engagementContext
	 */
	protected function getEngagementContext() : EngagementContextList
	{
		if (! $this->_engagementContext) {
			$this->_engagementContext = new EngagementContextList(
				$this->version,
				$this->solution['flowSid'],
				$this->solution['sid']
			);
		}

		return $this->_engagementContext;
	}

	/**
	 * Access the steps
	 */
	protected function getSteps() : StepList
	{
		if (! $this->_steps) {
			$this->_steps = new StepList(
				$this->version,
				$this->solution['flowSid'],
				$this->solution['sid']
			);
		}

		return $this->_steps;
	}
}
