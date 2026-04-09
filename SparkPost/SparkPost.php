<?php

namespace SparkPost;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Http\Client\HttpAsyncClient;   // Only if you still want async checks
use Exception;

class SparkPost
{
    /**
     * @var string Library version, used for setting User-Agent
     */
    private $version = '3.0.0';

    /**
     * @var ClientInterface (PSR-18) used to make requests
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface (PSR-17) to build request objects
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface (PSR-17) to build request bodies
     */
    private $streamFactory;

    /**
     * @var array Options for requests
     */
    private $options;

    /**
     * Default options for requests that can be overridden
     */
    private static $defaultOptions = [
        'host' => 'api.sparkpost.com',
        'protocol' => 'https',
        'port' => 443,
        'key' => '',
        'version' => 'v1',
        'async' => true,
        'debug' => false,
        'retries' => 0,
    ];

    /**
     * @var Transmission
     */
    public $transmissions;

    /**
     * Sets up the SparkPost instance.
     *
     * @param ClientInterface        $httpClient
     * @param array                  $options
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface  $streamFactory
     */
    public function __construct(
        ClientInterface $httpClient,
        array $options,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->requestFactory = $requestFactory ?? new \GuzzleHttp\Psr7\HttpFactory();
        $this->streamFactory  = $streamFactory ?? new \GuzzleHttp\Psr7\HttpFactory();
        $this->setOptions($options);
        $this->setHttpClient($httpClient);
        $this->setupEndpoints();
    }

    /**
     * Sends either sync or async request based on async option.
     *
     * @param string $method
     * @param string $uri
     * @param array  $payload - either used as request body or query params
     * @param array  $headers
     *
     * @return SparkPostResponse|SparkPostPromise
     */
    public function request($method = 'GET', $uri = '', $payload = [], $headers = [])
    {
        if ($this->options['async'] === true) {
            return $this->asyncRequest($method, $uri, $payload, $headers);
        } else {
            return $this->syncRequest($method, $uri, $payload, $headers);
        }
    }

    /**
     * Sends sync request to SparkPost API.
     *
     * @throws SparkPostException
     */
    public function syncRequest($method = 'GET', $uri = '', $payload = [], $headers = [])
    {
        $request = $this->buildRequest($method, $uri, $payload, $headers);

        $retries = $this->options['retries'];
        try {
            if ($retries > 0) {
                $resp = $this->syncReqWithRetry($request, $retries);
            } else {
                $resp = $this->httpClient->sendRequest($request);
            }
            return new SparkPostResponse($resp, $this->ifDebugRequest($method, $uri, $payload, $headers));
        } catch (Exception $exception) {
            throw new SparkPostException($exception, $this->ifDebugRequest($method, $uri, $payload, $headers));
        }
    }

    private function syncReqWithRetry(RequestInterface $request, int $retries)
    {
        $resp = $this->httpClient->sendRequest($request);
        $status = $resp->getStatusCode();
        if ($status >= 500 && $status <= 599 && $retries > 0) {
            return $this->syncReqWithRetry($request, $retries - 1);
        }
        return $resp;
    }

    /**
     * Sends async request to SparkPost API (HTTPlug async).
     * If you do not use HttpAsyncClient, remove this method.
     *
     * @return SparkPostPromise
     */
    public function asyncRequest($method = 'GET', $uri = '', $payload = [], $headers = [])
    {
        // Check if this is actually an async client (HTTPlug)
        if ($this->httpClient instanceof HttpAsyncClient) {
            $request = $this->buildRequest($method, $uri, $payload, $headers);

            $retries = $this->options['retries'];
            if ($retries > 0) {
                return new SparkPostPromise(
                    $this->asyncReqWithRetry($request, $retries),
                    $this->ifDebugRequest($method, $uri, $payload, $headers)
                );
            } else {
                return new SparkPostPromise(
                    $this->httpClient->sendAsyncRequest($request),
                    $this->ifDebugRequest($method, $uri, $payload, $headers)
                );
            }
        } else {
            throw new Exception('Your http client does not support asynchronous requests.');
        }
    }

    private function asyncReqWithRetry(RequestInterface $request, int $retries)
    {
        return $this->httpClient->sendAsyncRequest($request)->then(function ($response) use ($request, $retries) {
            $status = $response->getStatusCode();
            if ($status >= 500 && $status <= 599 && $retries > 0) {
                return $this->asyncReqWithRetry($request, $retries - 1);
            }
            return $response;
        });
    }

    /**
     * Builds a PSR-7 Request via PSR-17, returning RequestInterface.
     */
    public function buildRequest($method, $uri, array $payload = [], array $headers = [])
    {
        // Distinguish query params vs body
        $method = strtoupper(trim($method));
        if ($method === 'GET') {
            $params = $payload;
            $body   = null;
        } else {
            $params = [];
            $body   = $payload;
        }

        // Build final URL with query parameters
        $url = $this->getUrl($uri, $params);

        // Create base request
        $request = $this->requestFactory->createRequest($method, $url);

        // Merge in all headers (including Auth + Content-Type)
        $allHeaders = $this->getHttpHeaders($headers);
        foreach ($allHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // Encode body, attach as stream (PSR-17)
        if ($body !== null) {
            $encoded = json_encode($body);
            $stream  = $this->streamFactory->createStream($encoded);
            $request = $request->withBody($stream);
        }

        return $request;
    }

    /**
     * Builds the final URL from the options + path + query params.
     */
    public function getUrl($path, array $params = [])
    {
        $options = $this->options;

        $queryParts = [];
        foreach ($params as $key => $value) {
            // For arrays, flatten them with commas
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $queryParts[] = $key . '=' . $value;
        }

        $queryString = implode('&', $queryParts);
        $scheme = $options['protocol'];
        $host   = $options['host'];
        $port   = $options['port'] ? ':' . $options['port'] : '';
        $base   = "/api/{$options['version']}/";

        $fullPath = rtrim($base, '/') . '/' . ltrim($path, '/');
        $url = "{$scheme}://{$host}{$port}{$fullPath}";

        if ($queryString) {
            $url .= '?' . $queryString;
        }

        return $url;
    }

    /**
     * Return the final set of HTTP headers (injecting API key, JSON).
     */
    public function getHttpHeaders(array $headers = [])
    {
        $defaultHeaders = [
            'Authorization' => $this->options['key'],
            'Content-Type'  => 'application/json',
            'User-Agent'    => 'php-sparkpost/' . $this->version,
        ];

        // Merge user-supplied headers with defaults (defaults can override)
        return array_merge($headers, $defaultHeaders);
    }

    /**
     * Sets the PSR-18 client to be used for requests.
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Sets the options from the param and defaults for the SparkPost object.
     */
    public function setOptions(array $options)
    {
        // Validate API key
        if (!isset($this->options['key']) && (!isset($options['key']) || !trim($options['key']))) {
            throw new Exception('You must provide an API key');
        }

        // Merge user-provided options with defaults
        $defaults = isset($this->options) ? $this->options : self::$defaultOptions;
        foreach ($options as $option => $value) {
            $defaults[$option] = $value;
        }

        $this->options = $defaults;
        return $this;
    }

    /**
     * Returns debug info if enabled, or null otherwise.
     */
    private function ifDebugRequest($method, $uri, $payload, $headers)
    {
        if (!$this->options['debug']) {
            return null;
        }
        return [
            'method'  => $method,
            'uri'     => $uri,
            'payload' => $payload,
            'headers' => $headers
        ];
    }

    /**
     * Sets up child endpoints like transmissions, etc.
     */
    private function setupEndpoints()
    {
        $this->transmissions = new Transmission($this);
    }
}
