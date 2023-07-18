<?php

namespace App\View;

class ClusterDuck extends \PHPFUI\Page
	{
	public function __construct(string $message = 'We should have this fixed already. Try hitting refresh.')
		{
		parent::__construct();

		$this->setPageName('Cluster *$#&!');
		$server = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$address = $_SERVER['SERVER_ADDR'] ?? '::1';

		if ('localhost' != $server && '::1' != $address)
			{
			$this->addCSS('h1, h3, a {color:white;} body {background-color:black; text-align: center; font-family: Helvetica,Roboto,Arial,sans-serif;');
			}
		$this->add('<h1>Well that is a real cluster *$#&!</h1>');
		$this->add("<h3>{$message}</h3>");
		$this->add('<img style="margin:0 auto;display:block;" src="/images/clusterDuck.jpg"/>');
		}

	public function addMessage(string $message) : static
		{
		$this->add("\n");
		$this->add($message);

		return $this;
		}
	}
