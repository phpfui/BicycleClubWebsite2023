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

namespace Twilio\Rest\Chat\V2;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\ListResource;
use Twilio\Options;
use Twilio\Rest\Chat\V2\Service\BindingList;
use Twilio\Rest\Chat\V2\Service\ChannelList;
use Twilio\Rest\Chat\V2\Service\RoleList;
use Twilio\Rest\Chat\V2\Service\UserList;
use Twilio\Serialize;
use Twilio\Values;
use Twilio\Version;

/**
 * @property ChannelList $channels
 * @property BindingList $bindings
 * @property RoleList $roles
 * @property UserList $users
 * @method \Twilio\Rest\Chat\V2\Service\BindingContext bindings(string $sid)
 * @method \Twilio\Rest\Chat\V2\Service\ChannelContext channels(string $sid)
 * @method \Twilio\Rest\Chat\V2\Service\RoleContext roles(string $sid)
 * @method \Twilio\Rest\Chat\V2\Service\UserContext users(string $sid)
 */
class ServiceContext extends InstanceContext
	{
	protected $_bindings;

	protected $_channels;

	protected $_roles;

	protected $_users;

	/**
	 * Initialize the ServiceContext
	 *
	 * @param Version $version Version that contains the resource
	 * @param string $sid The SID of the Service resource to delete.
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

		$this->uri = '/Services/' . \rawurlencode($sid)
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

		return '[Twilio.Chat.V2.ServiceContext ' . \implode(' ', $context) . ']';
	}

	/**
	 * Delete the ServiceInstance
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
	 * Fetch the ServiceInstance
	 *
	 * @throws TwilioException When an HTTP error occurs.
	 * @return ServiceInstance Fetched ServiceInstance
	 */
	public function fetch() : ServiceInstance
	{

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->fetch('GET', $this->uri, [], [], $headers);

		return new ServiceInstance(
			$this->version,
			$payload,
			$this->solution['sid']
		);
	}

	/**
	 * Update the ServiceInstance
	 *
	 * @param array|Options $options Optional Arguments
	 * @throws TwilioException When an HTTP error occurs.
	 * @return ServiceInstance Updated ServiceInstance
	 */
	public function update(array $options = []) : ServiceInstance
	{

		$options = new Values($options);

		$data = Values::of([
			'FriendlyName' => $options['friendlyName'],
			'DefaultServiceRoleSid' => $options['defaultServiceRoleSid'],
			'DefaultChannelRoleSid' => $options['defaultChannelRoleSid'],
			'DefaultChannelCreatorRoleSid' => $options['defaultChannelCreatorRoleSid'],
			'ReadStatusEnabled' => Serialize::booleanToString($options['readStatusEnabled']),
			'ReachabilityEnabled' => Serialize::booleanToString($options['reachabilityEnabled']),
			'TypingIndicatorTimeout' => $options['typingIndicatorTimeout'],
			'ConsumptionReportInterval' => $options['consumptionReportInterval'],
			'Notifications.NewMessage.Enabled' => Serialize::booleanToString($options['notificationsNewMessageEnabled']),
			'Notifications.NewMessage.Template' => $options['notificationsNewMessageTemplate'],
			'Notifications.NewMessage.Sound' => $options['notificationsNewMessageSound'],
			'Notifications.NewMessage.BadgeCountEnabled' => Serialize::booleanToString($options['notificationsNewMessageBadgeCountEnabled']),
			'Notifications.AddedToChannel.Enabled' => Serialize::booleanToString($options['notificationsAddedToChannelEnabled']),
			'Notifications.AddedToChannel.Template' => $options['notificationsAddedToChannelTemplate'],
			'Notifications.AddedToChannel.Sound' => $options['notificationsAddedToChannelSound'],
			'Notifications.RemovedFromChannel.Enabled' => Serialize::booleanToString($options['notificationsRemovedFromChannelEnabled']),
			'Notifications.RemovedFromChannel.Template' => $options['notificationsRemovedFromChannelTemplate'],
			'Notifications.RemovedFromChannel.Sound' => $options['notificationsRemovedFromChannelSound'],
			'Notifications.InvitedToChannel.Enabled' => Serialize::booleanToString($options['notificationsInvitedToChannelEnabled']),
			'Notifications.InvitedToChannel.Template' => $options['notificationsInvitedToChannelTemplate'],
			'Notifications.InvitedToChannel.Sound' => $options['notificationsInvitedToChannelSound'],
			'PreWebhookUrl' => $options['preWebhookUrl'],
			'PostWebhookUrl' => $options['postWebhookUrl'],
			'WebhookMethod' => $options['webhookMethod'],
			'WebhookFilters' => Serialize::map($options['webhookFilters'], static function($e) { return $e; }),
			'Limits.ChannelMembers' => $options['limitsChannelMembers'],
			'Limits.UserChannels' => $options['limitsUserChannels'],
			'Media.CompatibilityMessage' => $options['mediaCompatibilityMessage'],
			'PreWebhookRetryCount' => $options['preWebhookRetryCount'],
			'PostWebhookRetryCount' => $options['postWebhookRetryCount'],
			'Notifications.LogEnabled' => Serialize::booleanToString($options['notificationsLogEnabled']),
		]);

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->update('POST', $this->uri, [], $data, $headers);

		return new ServiceInstance(
			$this->version,
			$payload,
			$this->solution['sid']
		);
	}

	/**
	 * Access the bindings
	 */
	protected function getBindings() : BindingList
	{
		if (! $this->_bindings) {
			$this->_bindings = new BindingList(
				$this->version,
				$this->solution['sid']
			);
		}

		return $this->_bindings;
	}

	/**
	 * Access the channels
	 */
	protected function getChannels() : ChannelList
	{
		if (! $this->_channels) {
			$this->_channels = new ChannelList(
				$this->version,
				$this->solution['sid']
			);
		}

		return $this->_channels;
	}

	/**
	 * Access the roles
	 */
	protected function getRoles() : RoleList
	{
		if (! $this->_roles) {
			$this->_roles = new RoleList(
				$this->version,
				$this->solution['sid']
			);
		}

		return $this->_roles;
	}

	/**
	 * Access the users
	 */
	protected function getUsers() : UserList
	{
		if (! $this->_users) {
			$this->_users = new UserList(
				$this->version,
				$this->solution['sid']
			);
		}

		return $this->_users;
	}
}
