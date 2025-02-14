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

namespace Twilio\Rest\Preview\Sync\Service;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\ListResource;
use Twilio\Rest\Preview\Sync\Service\SyncMap\SyncMapItemList;
use Twilio\Rest\Preview\Sync\Service\SyncMap\SyncMapPermissionList;
use Twilio\Values;
use Twilio\Version;

/**
 * @property SyncMapItemList $syncMapItems
 * @property SyncMapPermissionList $syncMapPermissions
 * @method \Twilio\Rest\Preview\Sync\Service\SyncMap\SyncMapItemContext syncMapItems(string $key)
 * @method \Twilio\Rest\Preview\Sync\Service\SyncMap\SyncMapPermissionContext syncMapPermissions(string $identity)
 */
class SyncMapContext extends InstanceContext
	{
	protected $_syncMapItems;

	protected $_syncMapPermissions;

	/**
	 * Initialize the SyncMapContext
	 *
	 * @param Version $version Version that contains the resource
	 * @param string $serviceSid
	 * @param string $sid
	 */
	public function __construct(
		Version $version,
		$serviceSid,
		$sid
	) {
		parent::__construct($version);

		// Path Solution
		$this->solution = [
			'serviceSid' => $serviceSid,
			'sid' => $sid,
		];

		$this->uri = '/Services/' . \rawurlencode($serviceSid)
		. '/Maps/' . \rawurlencode($sid)
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

		return '[Twilio.Preview.Sync.SyncMapContext ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the SyncMapInstance
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
	 * Fetch the SyncMapInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return SyncMapInstance Fetched SyncMapInstance
	 */
	public function fetch() : SyncMapInstance
	{

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->fetch('GET', $this->uri, [], [], $headers);

		return new SyncMapInstance(
			$this->version,
			$payload,
			$this->solution['serviceSid'],
			$this->solution['sid']
		);
	}

	/**
	 * Access the syncMapItems
	 */
	protected function getSyncMapItems() : SyncMapItemList
	{
		if (! $this->_syncMapItems) {
			$this->_syncMapItems = new SyncMapItemList(
				$this->version,
				$this->solution['serviceSid'],
				$this->solution['sid']
			);
		}

		return $this->_syncMapItems;
	}

	/**
	 * Access the syncMapPermissions
	 */
	protected function getSyncMapPermissions() : SyncMapPermissionList
	{
		if (! $this->_syncMapPermissions) {
			$this->_syncMapPermissions = new SyncMapPermissionList(
				$this->version,
				$this->solution['serviceSid'],
				$this->solution['sid']
			);
		}

		return $this->_syncMapPermissions;
	}
}
