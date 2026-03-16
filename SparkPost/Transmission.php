<?php

namespace SparkPost;

class Transmission extends ResourceBase
{
    public function __construct(SparkPost $sparkpost, string $endpoint = 'transmissions')
    {
        parent::__construct($sparkpost, $endpoint);
    }

    /**
     * Send post request to transmission endpoint after formatting cc, bcc, and expanding the shorthand emails.
     *
     * @return SparkPostPromise or SparkPostResponse depending on sync or async request
     */
    public function post(array $payload = [], array $headers = []): SparkPostResponse | SparkPostPromise
    {
        if (isset($payload['recipients']) && !isset($payload['recipients']['list_id']))
        {
            $payload = $this->formatPayload($payload);
        }

        return parent::post($payload, $headers);
    }

    /**
     * Runs the given payload through the formatting functions.
     *
     * @param array $payload - the request body
     *
     * @return array - the modified request body
     */
    public function formatPayload(array $payload) : array
    {
        $payload = $this->formatBlindCarbonCopy($payload); //Fixes BCCs into payload
        $payload = $this->formatCarbonCopy($payload); //Fixes CCs into payload
        $payload = $this->formatShorthandRecipients($payload); //Fixes shorthand recipients format

        return $payload;
    }

    /**
     * Formats bcc list into recipients list.
     *
     * @param array $payload - the request body
     *
     * @return array - the modified request body
     */
    private function formatBlindCarbonCopy(array $payload) : array
    {

        //If there's a list of BCC recipients, move them into the correct format
        if (isset($payload['bcc'])) {
            $payload = $this->addListToRecipients($payload, 'bcc');
        }

        return $payload;
    }

    /**
     * Formats cc list into recipients list and adds the CC header to the content.
     *
     * @param array $payload - the request body
     *
     * @return array - the modified request body
     */
    private function formatCarbonCopy(array $payload) : array
    {
        if (isset($payload['cc'])) {
            $ccAddresses = [];
            for ($i = 0; $i < count($payload['cc']); ++$i) {
                array_push($ccAddresses, $this->toAddressString($payload['cc'][$i]['address']));
            }

            // set up the content headers as either what it was before or an empty array
            $payload['content']['headers'] = isset($payload['content']['headers']) ? $payload['content']['headers'] : [];
            // add cc header
            $payload['content']['headers']['CC'] = implode(',', $ccAddresses);

            $payload = $this->addListToRecipients($payload, 'cc');
        }

        return $payload;
    }

    /**
     * Formats all recipients into the long form of [ "name" => "John", "email" => "john@exmmple.com" ].
     *
     * @param array $payload - the request body
     *
     * @return array - the modified request body
     */
    private function formatShorthandRecipients(array $payload) : array
    {
        if (isset($payload['content']['from'])) {
            $payload['content']['from'] = $this->toAddressObject($payload['content']['from']);
        }

        for ($i = 0; $i < count($payload['recipients']); ++$i) {
            $payload['recipients'][$i]['address'] = $this->toAddressObject($payload['recipients'][$i]['address']);
        }

        return $payload;
    }

    /**
     * Loops through the given listName in the payload and adds all the recipients to the recipients list after removing their names.
     *
     * @param array $payload  - the request body
     * @param array $listName - the name of the array in the payload to be moved to the recipients list
     *
     * @return array - the modified request body
     */
    private function addListToRecipients(array $payload, array $listName) : array
    {
        $originalAddress = $this->toAddressString($payload['recipients'][0]['address']);
        foreach ($payload[$listName] as $recipient) {
            $recipient['address'] = $this->toAddressObject($recipient['address']);
            $recipient['address']['header_to'] = $originalAddress;

            // remove name from address - name is only put in the header for cc and not at all for bcc
            if (isset($recipient['address']['name'])) {
                unset($recipient['address']['name']);
            }

            array_push($payload['recipients'], $recipient);
        }

        //Delete the original object from the payload.
        unset($payload[$listName]);

        return $payload;
    }

    /**
     * Takes the shorthand form of an email address and converts it to the long form.
     *
     * @param $address - the shorthand form of an email address "Name <Email address>"
     *
     * @return array - the longhand form of an email address [ "name" => "John", "email" => "john@exmmple.com" ]
     */
    private function toAddressObject(string $address) : array
    {
        $formatted = $address;
        if (is_string($formatted)) {
            $formatted = [];

            if ($this->isEmail($address)) {
                $formatted['email'] = $address;
            } elseif (preg_match('/"?(.[^"]*)?"?\s*<(.+)>/', $address, $matches)) {
                $name = trim($matches[1]);
                $formatted['name'] = $matches[1];
                $formatted['email'] = $matches[2];
            } else {
                throw new \Exception('Invalid address format: '.$address);
            }
        }

        return $formatted;
    }

    /**
     * Takes the longhand form of an email address and converts it to the shorthand form.
     *
     * @param $address - the longhand form of an email address [ "name" => "John", "email" => "john@exmmple.com" ]
     * @param string - the shorthand form of an email address "Name <Email address>"
     */
    private function toAddressString(array $address) : string
    {
        // convert object to string
        if (!is_string($address)) {
            if (isset($address['name'])) {
                $address = '"'.$address['name'].'" <'.$address['email'].'>';
            } else {
                $address = $address['email'];
            }
        }

        return $address;
    }

    /**
     * Checks if a string is an email.
     */
    private function isEmail(string $email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            return true;
        }

        return false;
    }
}
