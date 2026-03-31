<?php

namespace App\Cron;

class Display
  {
	public function __construct()
		{
		echo '<pre>';
		}

	public function debug(string $message) : void
		{
		echo $message . "\n";
		}
  }
