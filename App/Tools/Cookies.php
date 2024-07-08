<?php

namespace App\Tools;

class Cookies
	{
	/**
	 * @var array<string,mixed> $options
	 */
	private array $options = [];

	private readonly string $prefix;

	public function __construct()
		{
		$settings = new \App\Table\Setting();
		$this->prefix = $settings->value('clubAbbrev');
		$this->options = [
			'path' => '/',
			'domain' => $_SERVER['SERVER_NAME'],
			'secure' => true,
			'httponly' => true,
			'samesite' => 'Strict',
		];
		}

	public function delete(string $name) : static
		{
		$cookieName = $this->prefix . $name;
		unset($_COOKIE[$cookieName]);
		$this->options['expires'] = 1;
		\setcookie($cookieName, '', $this->options);
		unset($_COOKIE[$cookieName]);

		return $this;
		}

	public function get(string $name) : string
		{
		$cookieName = $this->prefix . $name;

		return $_COOKIE[$cookieName] ?? '';
		}

	public function set(string $name, string $value = '', bool $permanent = false) : static
		{
		$this->options['expires'] = $permanent ? \time() + 32_000_000 : 0; // expires in about a year if permanent
		\setcookie($this->prefix . $name, $value, $this->options);

		return $this;
		}
	}
