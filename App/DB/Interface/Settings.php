<?php

namespace App\DB\Interface;

interface Settings
	{
	public function getDB() : string;

	public function getHost() : string;

	public function getPassword() : string;

	public function getPort() : int;

	public function getUser() : string;
	}
