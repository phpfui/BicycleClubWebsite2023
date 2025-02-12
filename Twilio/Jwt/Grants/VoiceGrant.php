<?php

namespace Twilio\Jwt\Grants;

class VoiceGrant implements Grant
{
	private $endpointId;

	private $incomingAllow;

	private $outgoingApplicationParams;

	private $outgoingApplicationSid;

	private $pushCredentialSid;

	/**
	 * Returns the endpoint id
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
		return 'voice';
	}

	/**
	 * Returns whether incoming is allowed
	 *
	 * @return bool whether incoming is allowed
	 */
	public function getIncomingAllow() : bool {
		return $this->incomingAllow;
	}

	/**
	 * Returns the outgoing application params
	 *
	 * @return array the outgoing application params
	 */
	public function getOutgoingApplicationParams() : array {
		return $this->outgoingApplicationParams;
	}

	/**
	 * Returns the outgoing application sid
	 *
	 * @return string the outgoing application sid
	 */
	public function getOutgoingApplicationSid() : string {
		return $this->outgoingApplicationSid;
	}

	/**
	 * Returns the grant data
	 *
	 * @return array data of the grant
	 */
	public function getPayload() : array {
		$payload = [];

		if (true === $this->incomingAllow) {
			$incoming = [];
			$incoming['allow'] = true;
			$payload['incoming'] = $incoming;
		}

		if ($this->outgoingApplicationSid) {
			$outgoing = [];
			$outgoing['application_sid'] = $this->outgoingApplicationSid;

			if ($this->outgoingApplicationParams) {
				$outgoing['params'] = $this->outgoingApplicationParams;
			}

			$payload['outgoing'] = $outgoing;
		}

		if ($this->pushCredentialSid) {
			$payload['push_credential_sid'] = $this->pushCredentialSid;
		}

		if ($this->endpointId) {
			$payload['endpoint_id'] = $this->endpointId;
		}

		return $payload;
	}

	/**
	 * Returns the push credential sid
	 *
	 * @return string the push credential sid
	 */
	public function getPushCredentialSid() : string {
		return $this->pushCredentialSid;
	}

	/**
	 * Set the endpoint id
	 *
	 * @param string $endpointId endpoint id
	 *
	 * @return $this updated grant
	 */
	public function setEndpointId(string $endpointId) : self {
		$this->endpointId = $endpointId;

		return $this;
	}

	/**
	 * Set whether incoming is allowed
	 *
	 * @param bool $incomingAllow whether incoming is allowed
	 *
	 * @return $this updated grant
	 */
	public function setIncomingAllow(bool $incomingAllow) : self {
		$this->incomingAllow = $incomingAllow;

		return $this;
	}

	/**
	 * Set the outgoing application of the the grant
	 *
	 * @param string $sid outgoing application sid of the grant
	 * @param array $params params to pass the the application
	 *
	 * @return $this updated grant
	 */
	public function setOutgoingApplication(string $sid, array $params) : self {
		$this->outgoingApplicationSid = $sid;
		$this->outgoingApplicationParams = $params;

		return $this;
	}

	/**
	 * Set the outgoing application sid of the grant
	 *
	 * @param string $outgoingApplicationSid outgoing application sid of grant
	 *
	 * @return $this updated grant
	 */
	public function setOutgoingApplicationSid(string $outgoingApplicationSid) : self {
		$this->outgoingApplicationSid = $outgoingApplicationSid;

		return $this;
	}

	/**
	 * Set the push credential sid
	 *
	 *
	 * @return $this updated grant
	 */
	public function setPushCredentialSid(string $pushCredentialSid) : self {
		$this->pushCredentialSid = $pushCredentialSid;

		return $this;
	}
}
