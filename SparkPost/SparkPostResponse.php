<?php

namespace SparkPost;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class SparkPostResponse implements ResponseInterface
{
    /**
     * ResponseInterface to be wrapped by SparkPostResponse.
     */
    private $response;

    /**
     * Array with the request values sent.
     */
    private $request;

    /**
     * set the response to be wrapped.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response, $request = null)
    {
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * Returns the request values sent.
     *
     * @return array $request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the body stream.
     *
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    /**
     * Returns the body as a decoded JSON array.
     *
     * @return array|null
     */
    public function getBodyAsJson(): ?array
    {
        $body = $this->response->getBody();
        $body_string = $body->__toString();

        return json_decode($body_string, true);
    }

    /**
     * pass these down to the response given in the constructor.
     */
    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        return $this->response->withProtocolVersion($version);
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        return $this->response->withHeader($name, $value);
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        return $this->response->withAddedHeader($name, $value);
    }

    public function withoutHeader(string $name): MessageInterface
    {
        return $this->response->withoutHeader($name);
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this->response->withBody($body);
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->response->withStatus($code, $reasonPhrase);
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }
}
