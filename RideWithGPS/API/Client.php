<?php

namespace RideWithGPS\API;

class Client
	{
	public const TIMEOUT = 10;

	public bool $verify_ssl = true;

	private string $apiEndpoint = 'https://ridewithgps.com';

	private string $last_error = '';

	private array $last_request = [];

	private array $last_response = [];

	private bool $request_successful = false;

	private int $version = 2;

	/**
	 * Create a new instance
	 */
	public function __construct(private string $apiKey, private string $authToken)
		{
		}

	/**
	 * Make an HTTP DELETE request - for deleting data
	 *
	 * @param   string $method URL of the API request method
	 * @param   array $args Assoc array of arguments (if any)
	 * @param   int $timeout Timeout limit for request in seconds
	 *
	 * @return  array   Assoc array of API response, decoded from JSON
	 */
	public function delete(string $method, array $args = [], int $timeout = self::TIMEOUT) : array
		{
		return $this->makeRequest('delete', $method, $args, $timeout);
		}

	/**
	 * Make an HTTP GET request - for retrieving data
	 *
	 * @param   string $method URL of the API request method
	 * @param   array $args Assoc array of arguments (usually your data)
	 * @param   int $timeout Timeout limit for request in seconds
	 *
	 * @return  array   Assoc array of API response, decoded from JSON
	 */
	public function get(string $method, array $args = [], int $timeout = self::TIMEOUT) : array
		{
		return $this->makeRequest('get', $method, $args, $timeout);
		}

	/**
	 * @return string The url to the API endpoint
	 */
	public function getApiEndpoint() : string
		{
		return $this->apiEndpoint;
		}

	/**
	 * Get the last error returned by either the network transport, or by the API.
	 * If something didn't work, this should contain the string describing the problem.
	 *
	 * @return  string  describing the error
	 */
	public function getLastError() : string
		{
		return $this->last_error;
		}

	/**
	 * Get an array containing the HTTP headers and the body of the API request.
	 */
	public function getLastRequest() : array
		{
		return $this->last_request;
		}

	/**
	 * Get an array containing the HTTP headers and the body of the API response.
	 *
	 * @return array  Assoc array with keys 'headers' and 'body'
	 */
	public function getLastResponse() : array
		{
		return $this->last_response;
		}

	/**
	 * Make an HTTP PATCH request - for performing partial updates
	 *
	 * @param   string $method URL of the API request method
	 * @param   array $args Assoc array of arguments (usually your data)
	 * @param   int $timeout Timeout limit for request in seconds
	 *
	 * @return  array   Assoc array of API response, decoded from JSON
	 */
	public function patch(string $method, array $args = [], int $timeout = self::TIMEOUT) : array
		{
		return $this->makeRequest('patch', $method, $args, $timeout);
		}

	/**
	 * Make an HTTP POST request - for creating and updating items
	 *
	 * @param   string $method URL of the API request method
	 * @param   array $args Assoc array of arguments (usually your data)
	 * @param   int $timeout Timeout limit for request in seconds
	 *
	 * @return  array   Assoc array of API response, decoded from JSON
	 */
	public function post(string $method, array $args = [], int $timeout = self::TIMEOUT) : array
		{
		return $this->makeRequest('post', $method, $args, $timeout);
		}

	/**
	 * Make an HTTP PUT request - for creating new items
	 *
	 * @param   string $method URL of the API request method
	 * @param   array $args Assoc array of arguments (usually your data)
	 * @param   int $timeout Timeout limit for request in seconds
	 *
	 * @return  array   Assoc array of API response, decoded from JSON
	 */
	public function put(string $method, array $args = [], int $timeout = self::TIMEOUT) : array
		{
		return $this->makeRequest('put', $method, $args, $timeout);
		}

	/**
	 * Was the last request successful?
	 */
	public function success() : bool
		{
		return $this->request_successful;
		}

	/**
	 * Encode the data and attach it to the request
	 *
	 * @param \CurlHandle $ch cURL session handle, used by reference
	 * @param array $data Assoc array of data to attach
	 */
	private function attachRequestPayload(\CurlHandle &$ch, array $data) : void
		{
		$encoded = \json_encode($data);
		$this->last_request['body'] = $encoded;
		\curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
		}

	/**
	 * Check if the response was successful or a failure. If it failed, store the error.
	 *
	 * @param array $response The response from the curl request
	 * @param array $formattedResponse The response body payload from the curl request
	 * @param int $timeout The timeout supplied to the curl request.
	 *
	 * @return bool     If the request was successful
	 */
	private function determineSuccess(array $response, array $formattedResponse, int $timeout) : bool
		{
		$status = $this->findHTTPStatus($response, $formattedResponse);

		if ($status >= 200 && $status <= 299)
			{
			$this->request_successful = true;

			return true;
			}

		if (isset($formattedResponse['detail']))
			{
			$this->last_error = \sprintf('%d: %s', $formattedResponse['status'], $formattedResponse['detail']);

			return false;
			}

		if ($timeout > 0 && $response['headers'] && $response['headers']['total_time'] >= $timeout)
			{
			$this->last_error = \sprintf('Request timed out after %f seconds.', $response['headers']['total_time']);

			return false;
			}
		$this->last_error = 'Unknown error, call getLastResponse() to find out what happened.';

		return false;
		}

	/**
	 * Find the HTTP status code from the headers or API response body
	 *
	 * @param array $response The response from the curl request
	 * @param array $formattedResponse The response body payload from the curl request
	 *
	 * @return int  HTTP status code
	 */
	private function findHTTPStatus(array $response, array $formattedResponse) : int
		{
		if (! empty($response['headers']) && isset($response['headers']['http_code']))
			{
			return (int)$response['headers']['http_code'];
			}

		if (! empty($response['body']) && isset($formattedResponse['status']))
			{
			return (int)$formattedResponse['status'];
			}

		return 418;
		}

	/**
	 * Decode the response and format any error messages for debugging
	 *
	 * @param array $response The response from the curl request
	 *
	 * @return array    The JSON decoded into an array
	 */
	private function formatResponse(array $response) : array
		{
		$this->last_response = $response;

		if (! empty($response['body']))
			{
			return \json_decode($response['body'], true);
			}

		return [];
		}

	/**
	 * Get the HTTP headers as an array of header-name => header-value pairs.
	 *
	 * The "Link" header is parsed into an associative array based on the
	 * rel names it contains. The original value is available under
	 * the "_raw" key.
	 */
	private function getHeadersAsArray(string $headersAsString) : array
		{
		$headers = [];

		foreach (\explode("\r\n", $headersAsString) as $i => $line)
			{
			if (0 === $i)
				{ // HTTP code
				continue;
				}
			$line = \trim($line);

			if (empty($line))
				{
				continue;
				}
			[$key, $value] = \explode(': ', $line);

			if ('Link' == $key)
				{
				$value = \array_merge(
					['_raw' => $value],
					$this->getLinkHeaderAsArray($value)
				);
				}
			$headers[$key] = $value;
			}

		return $headers;
		}

	/**
	 * Extract all rel => URL pairs from the provided Link header value
	 */
	private function getLinkHeaderAsArray(string $linkHeaderAsString) : array
		{
		$urls = [];

		if (\preg_match_all('/<(.*?)>\s*;\s*rel="(.*?)"\s*/', $linkHeaderAsString, $matches))
			{
			foreach ($matches[2] as $i => $relName)
				{
				$urls[$relName] = $matches[1][$i];
				}
			}

		return $urls;
		}

	/**
	 * Performs the underlying HTTP request. Not very exciting.
	 *
	 * @param  string $http_verb The HTTP verb to use: get, post, put, patch, delete
	 * @param  string $method The API method to be called
	 * @param  array $args Assoc array of parameters to be passed
	 *
	 * @throws \Exception
	 * @return array Assoc array of decoded result, empty for false
	 */
	private function makeRequest(string $http_verb, string $method, array $args = [], int $timeout = self::TIMEOUT) : array
		{
		if (! \function_exists('curl_init') || ! \function_exists('curl_setopt'))
			{
			throw new \Exception("cURL support is required, but can't be found.");
			}
		$url = $this->apiEndpoint . '/' . $method . '.json';
		$response = $this->prepareStateForRequest($http_verb, $method, $url, $timeout);
		$ch = \curl_init();
		\curl_setopt($ch, CURLOPT_URL, $url);
		\curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Accept: application/vnd.api+json',
			'Content-Type: application/vnd.api+json',
		]);
		\curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		\curl_setopt($ch, CURLOPT_VERBOSE, true);
		\curl_setopt($ch, CURLOPT_HEADER, true);
		\curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		\curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
		\curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		\curl_setopt($ch, CURLOPT_ENCODING, '');
		\curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		$args['apikey'] = $this->apiKey;
		$args['auth_token'] = $this->authToken;
		$args['version'] = $this->version;

		switch ($http_verb)
			{
			case 'post':
				\curl_setopt($ch, CURLOPT_POST, true);
				$this->attachRequestPayload($ch, $args);

				break;

			case 'get':
				$query = \http_build_query($args, '', '&');
				\curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);

				break;

			case 'delete':
				\curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

				break;

			case 'patch':
				\curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
				$this->attachRequestPayload($ch, $args);

				break;

			case 'put':
				\curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				$this->attachRequestPayload($ch, $args);

				break;
			}
		$responseContent = \curl_exec($ch);
		$response['headers'] = \curl_getinfo($ch);
		$response = $this->setResponseState($response, $responseContent, $ch);
		\curl_close($ch);
		$formattedResponse = $this->formatResponse($response);
		$this->determineSuccess($response, $formattedResponse, $timeout);

		return $formattedResponse;
		}

	private function prepareStateForRequest(string $http_verb, string $method, string $url, int $timeout) : array
		{
		$this->last_error = '';
		$this->request_successful = false;
		$this->last_response = [
			'headers' => null, // array of details from curl_getinfo()
			'httpHeaders' => null, // array of HTTP headers
			'body' => null // content of the response
		];
		$this->last_request = [
			'method' => $http_verb,
			'path' => $method,
			'url' => $url,
			'body' => '',
			'timeout' => $timeout,
		];

		return $this->last_response;
		}

	/**
	 * Do post-request formatting and setting state from the response
	 *
	 * @param array $response The response from the curl request
	 * @param string $responseContent The body of the response from the curl request
	 * @param \CurlHandle $ch cURL session handle, used by reference
	 *
	 * @return array    The modified response
	 */
	private function setResponseState(array $response, string $responseContent, \CurlHandle $ch) : array
		{
		if (! $responseContent)
			{
			$this->last_error = \curl_error($ch);
			}
		else
			{
			$headerSize = $response['headers']['header_size'];
			$response['httpHeaders'] = $this->getHeadersAsArray(\substr($responseContent, 0, $headerSize));
			$response['body'] = \substr($responseContent, $headerSize);

			if (isset($response['headers']['request_header']))
				{
				$this->last_request['headers'] = $response['headers']['request_header'];
				}
			}

		return $response;
		}
	}
