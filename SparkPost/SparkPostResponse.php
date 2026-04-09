<?php

namespace SparkPost;

use \Psr\Http\Message\ResponseInterface as ResponseInterface;
use \Psr\Http\Message\StreamInterface as StreamInterface;

class SparkPostResponse implements ResponseInterface
{
    /**
     * set the response to be wrapped.
     *
     * @param ResponseInterface $response
     */
    public function __construct(private ResponseInterface $response, private ?array $request = null)
    {
    }

    /**
     * Returns the request values sent.
     *
     * @return array $request
     */
    public function getRequest() : array
    {
        return $this->request;
    }

    /**
     * Gets the body in json format.
     *
     * @return array Decoded body.
     */
    public function getBodyAsJson() : array
	{
        return json_decode($this->getBody()->getContents(), true);
    }

    public function getBody() : \Psr\Http\Message\StreamInterface
    {
        return $this->response->getBody();
    }

    /**
     * pass these down to the response given in the constructor.
     */
    public function getProtocolVersion() : string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion(string $version) : \Psr\Http\Message\MessageInterface
    {
        return $this->response->withProtocolVersion($version);
    }

    public function getHeaders() : array
    {
        return $this->response->getHeaders();
    }

    public function hasHeader(string $name) : bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader(string $name) : array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine(string $name) : string
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader(string $name, $value) : \Psr\Http\Message\MessageInterface
	{
        return $this->response->withHeader($name, $value);
    }

    public function withAddedHeader(string $name, $value) : \Psr\Http\Message\MessageInterface
    {
        return $this->response->withAddedHeader($name, $value);
    }

    public function withoutHeader(string $name) : \Psr\Http\Message\MessageInterface
	{
        return $this->response->withoutHeader($name);
    }

    public function withBody(\Psr\Http\Message\StreamInterface $body) : \Psr\Http\Message\MessageInterface
	{
        return $this->response->withBody($body);
    }

    public function getStatusCode() : int
    {
        return $this->response->getStatusCode();
    }

    public function withStatus(int $code, string $reasonPhrase = '') : \Psr\Http\Message\ResponseInterface
    {
        return $this->response->withStatus($code, $reasonPhrase);
    }

    public function getReasonPhrase() : string
    {
        return $this->response->getReasonPhrase();
    }
}
