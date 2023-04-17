<?php

namespace App\Model;

class PayPalLogo extends \App\Model\TinifyImage
	{
	public function __construct()
		{
		parent::__construct('/images/paypal');
		}

	public function processFile(string | int $file) : string
		{
		$this->resizeTo($file, 190, 60);

		return '';
		}
	}
