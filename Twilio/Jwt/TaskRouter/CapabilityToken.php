<?php

namespace Twilio\Jwt\TaskRouter;

use Twilio\Jwt\JWT;

/**
 * Twilio TaskRouter Capability assigner
 *
 * @author Justin Witz <justin.witz@twilio.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 */
class CapabilityToken
{
	protected $accountSid;

	protected $authToken;

	protected $baseUrl = 'https://taskrouter.twilio.com/v1';

	protected $baseWsUrl = 'https://event-bridge.twilio.com/v1/wschannels';

	protected $channelId;

	protected $optional = ['required' => false];

	protected $required = ['required' => true];

	protected $resourceUrl;

	protected $version = 'v1';

	protected $workspaceSid;

	private $friendlyName;

	/** @var Policy[] $policies */
	private $policies;

	public function __construct(
		string $accountSid,
		string $authToken,
		string $workspaceSid,
		string $channelId,
		?string $resourceUrl = null,
		?string $overrideBaseUrl = null,
		?string $overrideBaseWSUrl = null
	) {
		$this->accountSid = $accountSid;
		$this->authToken = $authToken;
		$this->friendlyName = $channelId;
		$this->policies = [];

		$this->workspaceSid = $workspaceSid;
		$this->channelId = $channelId;

		if (isset($overrideBaseUrl)) {
			$this->baseUrl = $overrideBaseUrl;
		}

		if (isset($overrideBaseWSUrl)) {
			$this->baseWsUrl = $overrideBaseWSUrl;
		}
		$this->baseUrl .= '/Workspaces/' . $workspaceSid;

		$this->validateJWT();

		if (! isset($resourceUrl)) {
			$this->setupResource();
		}

		//add permissions to GET and POST to the event-bridge channel
		$this->allow($this->baseWsUrl . '/' . $this->accountSid . '/' . $this->channelId, 'GET', null, null);
		$this->allow($this->baseWsUrl . '/' . $this->accountSid . '/' . $this->channelId, 'POST', null, null);

		//add permissions to fetch the instance resource
		$this->allow($this->resourceUrl, 'GET', null, null);
	}

	public function addPolicyDeconstructed(string $url, string $method, ?array $queryFilter = [], ?array $postFilter = [], bool $allow = true) : Policy {
		$policy = new Policy($url, $method, $queryFilter, $postFilter, $allow);
		$this->policies[] = $policy;

		return $policy;
	}

	public function allow(string $url, string $method, ?array $queryFilter = [], ?array $postFilter = []) : void {
		$this->addPolicyDeconstructed($url, $method, $queryFilter, $postFilter, true);
	}

	public function allowDelete() : void {
		$method = 'DELETE';
		$queryFilter = [];
		$postFilter = [];
		$this->allow($this->resourceUrl, $method, $queryFilter, $postFilter);
	}

	public function allowDeleteSubresources() : void {
		$method = 'DELETE';
		$queryFilter = [];
		$postFilter = [];
		$this->allow($this->resourceUrl . '/**', $method, $queryFilter, $postFilter);
	}

	public function allowFetchSubresources() : void {
		$method = 'GET';
		$queryFilter = [];
		$postFilter = [];
		$this->allow($this->resourceUrl . '/**', $method, $queryFilter, $postFilter);
	}

	public function allowUpdates() : void {
		$method = 'POST';
		$queryFilter = [];
		$postFilter = [];
		$this->allow($this->resourceUrl, $method, $queryFilter, $postFilter);
	}

	public function allowUpdatesSubresources() : void {
		$method = 'POST';
		$queryFilter = [];
		$postFilter = [];
		$this->allow($this->resourceUrl . '/**', $method, $queryFilter, $postFilter);
	}

	public function deny(string $url, string $method, array $queryFilter = [], array $postFilter = []) : void {
		$this->addPolicyDeconstructed($url, $method, $queryFilter, $postFilter, false);
	}

	public function generateToken(int $ttl = 3600, array $extraAttributes = []) : string {
		$payload = [
			'version' => $this->version,
			'friendly_name' => $this->friendlyName,
			'iss' => $this->accountSid,
			'exp' => \time() + $ttl,
			'account_sid' => $this->accountSid,
			'channel' => $this->channelId,
			'workspace_sid' => $this->workspaceSid
		];

		if (0 === \strpos($this->channelId, 'WK')) {
			$payload['worker_sid'] = $this->channelId;
		} elseif (0 === \strpos($this->channelId, 'WQ')) {
			$payload['taskqueue_sid'] = $this->channelId;
		}

		foreach ($extraAttributes as $key => $value) {
			$payload[$key] = $value;
		}

		$policyStrings = [];

		foreach ($this->policies as $policy) {
			$policyStrings[] = $policy->toArray();
		}

		$payload['policies'] = $policyStrings;

		return JWT::encode($payload, $this->authToken, 'HS256');
	}

	protected function setupResource() : void {
	}

	private function validateJWT() : void {
		if (! isset($this->accountSid) || 0 !== \strpos($this->accountSid, 'AC')) {
			throw new \Exception('Invalid AccountSid provided: ' . $this->accountSid);
		}

		if (! isset($this->workspaceSid) || 0 !== \strpos($this->workspaceSid, 'WS')) {
			throw new \Exception('Invalid WorkspaceSid provided: ' . $this->workspaceSid);
		}

		if (! isset($this->channelId)) {
			throw new \Exception('ChannelId not provided');
		}
		$prefix = \substr($this->channelId, 0, 2);

		if ('WS' !== $prefix && 'WK' !== $prefix && 'WQ' !== $prefix) {
			throw new \Exception('Invalid ChannelId provided: ' . $this->channelId);
		}
	}
}
