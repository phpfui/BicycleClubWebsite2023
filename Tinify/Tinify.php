<?php

namespace Tinify;

const VERSION = '1.6.1';

class Tinify
{
	private static $appIdentifier = null;

	private static $client = null;

	private static $compressionCount = null;

	private static $key = null;

	private static $proxy = null;

	public static function getClient() {
		if (! self::$key) {
			throw new AccountException("Provide an API key with Tinify\setKey(...)");
		}

		if (! self::$client) {
			self::$client = new Client(self::$key, self::$appIdentifier, self::$proxy);
		}

		return self::$client;
	}

	public static function getCompressionCount() {
		return self::$compressionCount;
	}

	public static function setAppIdentifier($appIdentifier) : void {
		self::$appIdentifier = $appIdentifier;
		self::$client = null;
	}

	public static function setClient($client) : void {
		self::$client = $client;
	}

	public static function setCompressionCount($compressionCount) : void {
		self::$compressionCount = $compressionCount;
	}

	public static function setKey($key) : void {
		self::$key = $key;
		self::$client = null;
	}

	public static function setProxy($proxy) : void {
		self::$proxy = $proxy;
		self::$client = null;
	}
}

function setKey($key) {
	return Tinify::setKey($key);
}

function setAppIdentifier($appIdentifier) {
	return Tinify::setAppIdentifier($appIdentifier);
}

function setProxy($proxy) {
	return Tinify::setProxy($proxy);
}

function getCompressionCount() {
	return Tinify::getCompressionCount();
}

function compressionCount() {
	return Tinify::getCompressionCount();
}

function fromFile($path) {
	return Source::fromFile($path);
}

function fromBuffer($string) {
	return Source::fromBuffer($string);
}

function fromUrl($string) {
	return Source::fromUrl($string);
}

function validate() {
	try {
		Tinify::getClient()->request('post', '/shrink');
	} catch (AccountException $err) {
		if (429 == $err->status) return true;

		throw $err;
	} catch (ClientException $err) {
		return true;
	}
}
