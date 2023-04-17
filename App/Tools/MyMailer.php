<?php

namespace App\Tools;

class MyMailer extends \PHPMailer\PHPMailer\PHPMailer
	{
	public function __construct($exceptions = null)
		{
		parent::__construct($exceptions);
		// add in click tracking
		$this->mailHeader = 'X-MSYS-API: {"options":{"open_tracking":true,"click_tracking":true}}' . static::$LE;
		}
	}
