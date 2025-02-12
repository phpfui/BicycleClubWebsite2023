<?php

namespace Twilio;

class VersionInfo
{
	public const MAJOR = '8';

	public const MINOR = '3';

	public const PATCH = '14';

	public static function string() {
		return \implode('.', [self::MAJOR, self::MINOR, self::PATCH]);
	}
}
