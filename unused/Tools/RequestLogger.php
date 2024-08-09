<?php

namespace App\Tools;

class RequestLogger
	{
	public function __construct()
		{
	  if (isset($_SERVER['REQUEST_URI']))
			{
			$parts = \parse_url((string)$_SERVER['REQUEST_URI']);
			$server = $_SERVER;
			$server['REQUEST_URI'] = $parts['path'] ?? '';
			$server['_get'] = \serialize($_GET);
			$server['_post'] = \serialize($_POST);

			$httpRequest = new \App\Record\HttpRequest();
			$httpRequest->setFrom($server);
			$httpRequest->insert();
			}
		}
	}
