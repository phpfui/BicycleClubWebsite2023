<?php

namespace Tinify;

class Exception extends \Exception
{
	public $status;

	public function __construct($message, $type = null, $status = null) {
		$this->status = $status;

		if ($status) {
			parent::__construct($message . ' (HTTP ' . $status . '/' . $type . ')');
		} else {
			parent::__construct($message);
		}
	}

	public static function create($message, $type, $status) {
		if (401 == $status || 429 == $status) {
			$klass = "Tinify\AccountException";
		} elseif($status >= 400 && $status <= 499) {
			$klass = "Tinify\ClientException";
		} elseif($status >= 500 && $status <= 599) {
			$klass = "Tinify\ServerException";
		} else {
			$klass = "Tinify\Exception";
		}

		if (empty($message)) $message = 'No message was provided';

		return new $klass($message, $type, $status);
	}
}
