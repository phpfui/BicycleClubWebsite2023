<?php

namespace SparkPost;

/**
 * Class ResourceBase.
 */
class ResourceBase
{
    /**
     * Sets up the Resource.
     *
     * @param SparkPost $sparkpost - the sparkpost instance that this resource is attached to
     * @param string    $endpoint  - the endpoint that this resource wraps
     */
    public function __construct(protected SparkPost $sparkpost, protected string $endpoint)
    {
    }

    /**
     * Sends get request to API at the set endpoint.
     *
     * @see SparkPost->request()
     */
    public function get(string | array $uri = '', array $payload = [], array $headers = []): SparkPostResponse | SparkPostPromise
    {
        return $this->request('GET', $uri, $payload, $headers);
    }

    /**
     * Sends put request to API at the set endpoint.
     *
     * @see SparkPost->request()
     */
    public function put(string | array $uri = '', array $payload = [], array $headers = []): SparkPostResponse | SparkPostPromise
    {
        return $this->request('PUT', $uri, $payload, $headers);
    }

    /**
     * Sends post request to API at the set endpoint.
     *
     * @see SparkPost->request()
     */
    public function post(array $payload = [], array $headers = []): SparkPostResponse | SparkPostPromise
    {
        return $this->request('POST', '', $payload, $headers);
    }

    /**
     * Sends delete request to API at the set endpoint.
     *
     * @see SparkPost->request()
     */
    public function delete(string | array $uri = '', array $payload = [], array $headers = []): SparkPostResponse | SparkPostPromise
    {
        return $this->request('DELETE', $uri, $payload, $headers);
    }

    /**
     * Sends requests to SparkPost object to the resource endpoint.
     *
     * @see SparkPost->request()
     *
     * @return SparkPostPromise | SparkPostResponse depending on sync or async request
     */
    public function request(string $method = 'GET', string | array $uri = '', array $payload = [], array $headers = []): SparkPostResponse | SparkPostPromise
    {
        if (is_array($uri)) {
            $headers = $payload;
            $payload = $uri;
            $uri = '';
        }

        $uri = rtrim($this->endpoint.'/'.$uri, '/');

        return $this->sparkpost->request($method, $uri, $payload, $headers);
    }
}
