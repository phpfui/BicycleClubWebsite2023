<?php

namespace Twilio\Jwt\Grants;

class ChatGrant implements Grant
{
	private $deploymentRoleSid;

	private $endpointId;

	private $pushCredentialSid;

	private $serviceSid;

	/**
	 * Returns the deployment role sid of the grant
	 *
	 * @return string the deployment role sid
	 */
	public function getDeploymentRoleSid() : string {
		return $this->deploymentRoleSid;
	}

	/**
	 * Returns the endpoint id of the grant
	 *
	 * @return string the endpoint id
	 */
	public function getEndpointId() : string {
		return $this->endpointId;
	}

	/**
	 * Returns the grant type
	 *
	 * @return string type of the grant
	 */
	public function getGrantKey() : string {
		return 'chat';
	}

	/**
	 * Returns the grant data
	 *
	 * @return array data of the grant
	 */
	public function getPayload() : array {
		$payload = [];

		if ($this->serviceSid) {
			$payload['service_sid'] = $this->serviceSid;
		}

		if ($this->endpointId) {
			$payload['endpoint_id'] = $this->endpointId;
		}

		if ($this->deploymentRoleSid) {
			$payload['deployment_role_sid'] = $this->deploymentRoleSid;
		}

		if ($this->pushCredentialSid) {
			$payload['push_credential_sid'] = $this->pushCredentialSid;
		}

		return $payload;
	}

	/**
	 * Returns the push credential sid of the grant
	 *
	 * @return string the push credential sid
	 */
	public function getPushCredentialSid() : string {
		return $this->pushCredentialSid;
	}

	/**
	 * Returns the service sid
	 *
	 * @return string the service sid
	 */
	public function getServiceSid() : string {
		return $this->serviceSid;
	}

	/**
	 * Set the role sid of the grant
	 *
	 * @param string $deploymentRoleSid role sid of the grant
	 *
	 * @return $this updated grant
	 */
	public function setDeploymentRoleSid(string $deploymentRoleSid) : self {
		$this->deploymentRoleSid = $deploymentRoleSid;

		return $this;
	}

	/**
	 * Set the endpoint id of the grant
	 *
	 * @param string $endpointId endpoint id of the grant
	 *
	 * @return $this updated grant
	 */
	public function setEndpointId(string $endpointId) : self {
		$this->endpointId = $endpointId;

		return $this;
	}

	/**
	 * Set the credential sid of the grant
	 *
	 * @param string $pushCredentialSid push credential sid of the grant
	 *
	 * @return $this updated grant
	 */
	public function setPushCredentialSid(string $pushCredentialSid) : self {
		$this->pushCredentialSid = $pushCredentialSid;

		return $this;
	}

	/**
	 * Set the service sid of this grant
	 *
	 * @param string $serviceSid service sid of the grant
	 *
	 * @return $this updated grant
	 */
	public function setServiceSid(string $serviceSid) : self {
		$this->serviceSid = $serviceSid;

		return $this;
	}
}
