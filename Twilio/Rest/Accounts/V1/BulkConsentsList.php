<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Accounts
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Accounts\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\ListResource;
use Twilio\Serialize;
use Twilio\Values;
use Twilio\Version;

class BulkConsentsList extends ListResource
	{
	/**
	 * Construct the BulkConsentsList
	 *
	 * @param Version $version Version that contains the resource
	 */
	public function __construct(
		Version $version
	) {
		parent::__construct($version);

		// Path Solution
		$this->solution = [
		];

		$this->uri = '/Consents/Bulk';
	}

	/**
	 * Provide a friendly representation
	 *
	 * @return string Machine friendly representation
	 */
	public function __toString() : string
	{
		return '[Twilio.Accounts.V1.BulkConsentsList]';
	}

	/**
	 * Create the BulkConsentsInstance
	 *
	 * @param array[] $items This is a list of objects that describes a contact's opt-in status. Each object contains the following fields: `contact_id`, which must be a string representing phone number in [E.164 format](https://www.twilio.com/docs/glossary/what-e164); `correlation_id`, a unique 32-character UUID used to uniquely map the request item with the response item; `sender_id`, which can be either a valid messaging service SID or a from phone number; `status`, a string representing the consent status. Can be one of [`opt-in`, `opt-out`]; and `source`, a string indicating the medium through which the consent was collected. Can be one of [`website`, `offline`, `opt-in-message`, `opt-out-message`, `others`].
	 * @throws TwilioException When an HTTP error occurs.
	 * @return BulkConsentsInstance Created BulkConsentsInstance
	 */
	public function create(array $items) : BulkConsentsInstance
	{

		$data = Values::of([
			'Items' => Serialize::map($items, static function($e) { return Serialize::jsonObject($e); }),
		]);

		$headers = Values::of(['Content-Type' => 'application/x-www-form-urlencoded']);
		$payload = $this->version->create('POST', $this->uri, [], $data, $headers);

		return new BulkConsentsInstance(
			$this->version,
			$payload
		);
	}
}
