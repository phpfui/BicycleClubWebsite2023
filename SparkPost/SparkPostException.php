<?php

namespace SparkPost;

use Http\Client\Exception\HttpException as HttpException;

class SparkPostException extends \Exception
{
    /**
     * Variable to hold json decoded body from http response.
     */
    private ?array $body = null;

    /**
     * Sets up the custom exception and copies over original exception values.
     *
	 * @param Exception $exception - the exception to be wrapped
	 * @param ?array $request Array with the request values sent.
     */
    public function __construct(private \Exception $exception, private ?array $request = null)
    {
        $message = $exception->getMessage();
        $code = $exception->getCode();
        if ($exception instanceof HttpException) {
            $message = $exception->getResponse()->getBody()->__toString();
            $this->body = json_decode($message, true);
            $code = $exception->getResponse()->getStatusCode();
        }

        parent::__construct($message, $code, $exception->getPrevious());
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
     * Returns the body.
     *
     * @return ?array $body - the json decoded body from the http response
     */
    public function getBody() : ?array
    {
        return $this->body;
    }
}
