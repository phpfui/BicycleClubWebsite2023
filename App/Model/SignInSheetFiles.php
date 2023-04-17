<?php

namespace App\Model;

class SignInSheetFiles extends \App\Model\TinifyImage
	{
	public function __construct()
		{
		parent::__construct('../files/signinsheets');
		}

	public function processFile(string | int $file) : string
		{
		$this->autoRotate($file);

		return '';
		}
	}
