<?php

namespace App\Model;

class SMS
	{
	private string $body = '';

	private ?\Twilio\Rest\Client $client = null;

	private readonly string $defaultAreaCode;

	private ?\App\Record\Member $fromMember = null;

	private ?float $latitude = null;

	private ?float $longitude = null;

	private string $media = '';

	private readonly string $number;

	private readonly \App\Table\RideSignup $rideSignupTable;

	public function __construct(string $body = '')
		{
		$settingsSaver = new \App\Model\SettingsSaver('Twilio');

		if ($settingsSaver->getValue('TwilioSID'))
			{
			$this->client = new \Twilio\Rest\Client($settingsSaver->getValue('TwilioSID'), $settingsSaver->getValue('TwilioToken'));
			}
		$this->number = $settingsSaver->getValue('TwilioNumber');
		$this->defaultAreaCode = $settingsSaver->getValue('TwilioDefaultAreaCode');
		$this->rideSignupTable = new \App\Table\RideSignup();
		$this->setBody($body);
		}

	public function cleanPhone(string $phone) : string
		{
		$returnValue = $this->stripPhone($phone);

		if ($returnValue)
			{
			$returnValue = '+1' . $returnValue;
			}

		return $returnValue;
		}

	public function enabled() : bool
		{
		return (bool)$this->client;
		}

	public function formatPhone(string $phone) : string
		{
		$returnValue = $this->stripPhone($phone);

		if ($returnValue)
			{
			$returnValue = \substr($returnValue, 0, 3) . '-' . \substr($returnValue, 3, 3) . '-' . \substr($returnValue, 6);
			}

		return $returnValue;
		}

	public function setBody(string $body) : static
		{
		$this->body = \substr($body, 0, 1600);

		return $this;
		}

	public function setFromMember(\App\Record\Member $member) : static
		{
		$this->fromMember = $member;

		return $this;
		}

	/**
	 * @param array<string,string> $parameters
	 */
	public function setGeoLocation(array $parameters) : static
		{
		if (isset($parameters['latitude']) && $parameters['latitude'])
			{
			$this->latitude = (float)$parameters['latitude'];
			}

		if (isset($parameters['longitude']) && $parameters['longitude'])
			{
			$this->longitude = (float)$parameters['longitude'];
			}

		return $this;
		}

	public function setMediaLink(string $mediaLink) : static
		{
		$this->media = $mediaLink;

		return $this;
		}

	public function stripPhone(string $phone) : string
	 {
	 $returnValue = '';
	 $length = \strlen($phone);

	 for ($i = 0; $i < $length; ++$i)
		 {
		 $char = $phone[$i];

		 if (\ctype_digit($char))
			 {
			 $returnValue .= $char;
			 }
		 }

	 if (7 == \strlen($returnValue))
		 {
		 $returnValue = $this->defaultAreaCode . $returnValue;
		 }

	 if (10 != \strlen($returnValue))
		 {
		 $returnValue = '';
		 }

	 return $returnValue;
	 }

	public function textMember(\PHPFUI\ORM\DataObject $member) : static
		{
		if (! $this->enabled())
			{
			\App\Tools\Logger::get()->debug('SMS is not set up');

			return $this;
			}

		$textToNumber = $this->cleanPhone($member->cellPhone ?? '');

		if ($textToNumber && $member->allowTexting)
			{
			$data = ['from' => $this->number];
			$header = '';

			if ($this->fromMember && $this->fromMember->loaded())
				{
				$replyNumber = $this->cleanPhone($this->fromMember->cellPhone ?? '');
				$geoLink = \App\Model\RideWithGPS::getMapPinLink(['latitude' => $this->latitude, 'longitude' => $this->longitude]);

				if ($geoLink)
					{
					$geoLink = "Location: {$geoLink}";
					}
				$header = "From: {$this->fromMember->fullName()} {$replyNumber} {$geoLink}\n";
				}

			if ($this->body)
				{
				$data['body'] = $header . $this->body;
				}

			if ($this->media)
				{
				$data['mediaUrl'] = $this->media;
				}

			$this->client->messages->create($textToNumber, $data);
			}

		return $this;
		}

	public function textRide(\App\Record\Ride $ride) : static
		{
		$comment = 'Via Text: ' . $this->body;

		// was it already sent?
		$rideComment = new \App\Record\RideComment(['rideId' => $ride->rideId, 'comment' => $comment]);

		if ($rideComment->loaded())	// was it already sent?
			{
			return $this;
			}

		// add to ride comment table for posterity
		$rideComment->comment = $comment;
		$rideComment->time = \date('Y-m-d H:i:s');
		$rideComment->latitude = $this->latitude;
		$rideComment->longitude = $this->longitude;
		$rideComment->ride = $ride;
		$rideComment->member = $this->fromMember;
		$rideComment->insert();

		foreach ($this->rideSignupTable->getAllSignedUpRiders($ride) as $rider)
			{
			if (\App\Enum\RideSignup\Attended::NO_SHOW->value != $rider['attended'])
				{
				$this->textMember($rider);
				}
			}

		return $this;
		}
	}
