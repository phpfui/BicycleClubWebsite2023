<?php

namespace App\WWW;

class Tinify implements \PHPFUI\Interfaces\NanoClass
	{
	private \App\Tools\Logger $logger;

	private \PHPFUI\VanillaPage $page;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		$this->logger = \App\Tools\Logger::get();
		$this->page = new \PHPFUI\VanillaPage();
		}

	public function __toString() : string
		{
		return '';
		}

	public function image() : void
		{
		$jsonPayload = \file_get_contents('php://input');

		// Decode the JSON data (if it's JSON)
		$data = \json_decode($jsonPayload, true);

		// Check if decoding was successful
		if (JSON_ERROR_NONE !== \json_last_error())
			{
			$this->logger->debug(__METHOD__ . ': Error decoding JSON: ' . \json_last_error_msg());
			\header('HTTP/1.1 400 Bad Request');

			return;
			}
		\header('HTTP/1.1 200 Ok');

		$this->logger->debug($data);

		$response = ['url' => '/images/pissingCyclist.jpg'];
		$this->logger->debug($_POST, __METHOD__);
		$this->page->setRawResponse(\json_encode($response, JSON_PRETTY_PRINT));
		}
	}
