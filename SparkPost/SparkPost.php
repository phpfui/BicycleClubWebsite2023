<?php

namespace SparkPost;

use Http\Client\HttpClient;
use Http\Client\HttpAsyncClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;
use Psr\Http\Message\RequestInterface;

class SparkPost
{
    /**
     * @var string Library version, used for setting User-Agent
     */
    private string $version = '2.3.0';

    /**
     * @var RequestFactory
     */
    private RequestFactory $messageFactory;

    private array $options = [];

    /**
     * Default options for requests that can be overridden with the setOptions function.
     */
    private static array $defaultOptions = [
        'host' => 'api.sparkpost.com',
        'protocol' => 'https',
        'port' => 443,
        'key' => '',
        'version' => 'v1',
        'async' => true,
        'debug' => false,
        'retries' => 0
    ];

    public Transmission $transmissions;

    /**
     * Sets up the SparkPost instance.
     *
     * @param HttpClient $httpClient - An httplug client or adapter
     * @param array      $options    - An array to overide default options or a string to be used as an API key
     */
    public function __construct(private HttpClient | HttpAsyncClient $httpClient, array $options)
    {
        $this->setOptions($options);
        $this->setHttpClient($httpClient);
        $this->setupEndpoints();
    }

    /**
     * Sends either sync or async request based on async option.
     *
     * @return SparkPostPromise|SparkPostResponse Promise or Response depending on sync or async request
     */
    public function request(string $method = 'GET', string $uri = '', array $payload = [], array $headers = []): SparkPostPromise | SparkPostResponse
    {
        if ($this->options['async'] === true)
		{
            return $this->asyncRequest($method, $uri, $payload, $headers);
        }

		return $this->syncRequest($method, $uri, $payload, $headers);
    }

    /**
     * Sends sync request to SparkPost API.
     *
     * @throws SparkPostException
     */
    public function syncRequest(string $method = 'GET', string $uri = '', array $payload = [], array $headers = []): SparkPostResponse
    {
        $requestValues = $this->buildRequestValues($method, $uri, $payload, $headers);
        $request = call_user_func_array(array($this, 'buildRequestInstance'), $requestValues);

        $retries = $this->options['retries'];
        try {
            if ($retries > 0) {
              $resp = $this->syncReqWithRetry($request, $retries);
            } else {
              $resp = $this->httpClient->sendRequest($request);
            }
            return new SparkPostResponse($resp, $this->ifDebug($requestValues));
        } catch (\Exception $exception) {
            throw new SparkPostException($exception, $this->ifDebug($requestValues));
        }
    }

    private function syncReqWithRetry($request, $retries)
    {
        $resp = $this->httpClient->sendRequest($request);
        $status = $resp->getStatusCode();
        if ($status >= 500 && $status <= 599 && $retries > 0) {
          return $this->syncReqWithRetry($request, $retries-1);
        }
        return $resp;
    }

    /**
     * Sends async request to SparkPost API.
     */
    public function asyncRequest(string $method = 'GET', string $uri = '', array $payload = [], array $headers = []): SparkPostPromise
    {
        if ($this->httpClient instanceof HttpAsyncClient) {
            $requestValues = $this->buildRequestValues($method, $uri, $payload, $headers);
            $request = call_user_func_array(array($this, 'buildRequestInstance'), $requestValues);

            $retries = $this->options['retries'];
            if ($retries > 0) {
                return new SparkPostPromise($this->asyncReqWithRetry($request, $retries), $this->ifDebug($requestValues));
            } else {
                return new SparkPostPromise($this->httpClient->sendAsyncRequest($request), $this->ifDebug($requestValues));
            }
        } else {
            throw new \Exception('Your http client does not support asynchronous requests. Please use a different client or use synchronous requests.');
        }
    }

    private function asyncReqWithRetry($request, int $retries)
    {
        return $this->httpClient->sendAsyncRequest($request)->then(function($response) use ($request, $retries) {
            $status = $response->getStatusCode();
            if ($status >= 500 && $status <= 599 && $retries > 0) {
                return $this->asyncReqWithRetry($request, $retries-1);
            }
            return $response;
        });
    }

    /**
     * Builds request values from given params.
     */
    public function buildRequestValues(string $method, string $uri, array $payload, array $headers): array
    {
        $method = trim(strtoupper($method));

        if ($method === 'GET') {
            $params = $payload;
            $body = [];
        } else {
            $params = [];
            $body = $payload;
        }

        $url = $this->getUrl($uri, $params);
        $headers = $this->getHttpHeaders($headers);

        // old form-feed workaround now removed
        $body = json_encode($body);
        return [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    /**
     * Build RequestInterface from given params.
     */
    public function buildRequestInstance(string $method, string $url, array $headers, array $body): RequestInterface
    {
        return $this->getMessageFactory()->createRequest($method, $url, $headers, $body);
    }

    /**
     * Build RequestInterface from given params.
     */
    public function buildRequest(string $method, string $uri, array $payload, array $headers): RequestInterface
    {
        $requestValues = $this->buildRequestValues($method, $uri, $payload, $headers);
        return call_user_func_array(array($this, 'buildRequestInstance'), $requestValues);
    }

    /**
     * Returns an array for the request headers.
     *
     * @param array $headers - any custom headers for the request
     *
     * @return array $headers - headers for the request
     */
    public function getHttpHeaders(array $headers = []): array
    {
        $constantHeaders = [
            'Authorization' => $this->options['key'],
            'Content-Type' => 'application/json',
            'User-Agent' => 'php-sparkpost/'.$this->version,
        ];

        foreach ($constantHeaders as $key => $value) {
            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * Builds the request url from the options and given params.
     *
     * @param string $path   - the path in the url to hit
     * @param array  $params - query parameters to be encoded into the url
     *
     * @return string $url - the url to send the desired request to
     */
    public function getUrl(string $path, array $params = []): string
    {
        $options = $this->options;

        $paramsArray = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            array_push($paramsArray, $key.'='.$value);
        }

        $paramsString = implode('&', $paramsArray);

        return $options['protocol'].'://'.$options['host'].($options['port'] ? ':'.$options['port'] : '').'/api/'.$options['version'].'/'.$path.($paramsString ? '?'.$paramsString : '');
    }

    /**
     * Sets $httpClient to be used for request.
     *
     * @param HttpClient|HttpAsyncClient $httpClient - the client to be used for request
     *
     * @return SparkPost
     */
    public function setHttpClient(HttpClient | HttpAsyncClient $httpClient): static
    {
        if (!($httpClient instanceof HttpAsyncClient || $httpClient instanceof HttpClient)) {
            throw new \LogicException(sprintf('Parameter to SparkPost::setHttpClient must be instance of "%s" or "%s"', HttpClient::class, HttpAsyncClient::class));
        }

        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Sets the options from the param and defaults for the SparkPost object.
     *
     * @param array $options - either an string API key or an array of options
     *
     * @return SparkPost
     */
    public function setOptions(array $options): static
    {
        // if the options map is a string we should assume that its an api key
        if (is_string($options)) {
            $options = ['key' => $options];
        }

        // Validate API key because its required
        if (!isset($this->options['key']) && (!isset($options['key']) || !preg_match('/\S/', $options['key']))) {
            throw new \Exception('You must provide an API key');
        }

        $this->options = isset($this->options) ? $this->options : self::$defaultOptions;

        // set options, overriding defaults
        foreach ($options as $option => $value) {
            if (key_exists($option, $this->options)) {
                $this->options[$option] = $value;
            }
        }

        return $this;
    }

    /**
     * Returns the given value if debugging, an empty instance otherwise.
     */
    private function ifDebug(mixed $param): mixed
    {
        return $this->options['debug'] ? $param : null;
    }

    /**
     * Sets up any endpoints to custom classes e.g. $this->transmissions.
     */
    private function setupEndpoints(): void
    {
        $this->transmissions = new Transmission($this);
    }

    /**
     * @return RequestFactory
     */
    private function getMessageFactory(): RequestFactory
    {
        if (!$this->messageFactory) {
            $this->messageFactory = MessageFactoryDiscovery::find();
        }

        return $this->messageFactory;
    }

    /**
     * @param RequestFactory $messageFactory
     *
     * @return SparkPost
     */
    public function setMessageFactory(RequestFactory $messageFactory): static
    {
        $this->messageFactory = $messageFactory;

        return $this;
    }
}
