<?php

namespace Psr\Captcha;

/**
 * MUST be thrown from CaptchaVerifierInterface methods if Captcha test itself cannot be passed due to any reason that is not user-related - network problems, incorrect secret token, unable to parse request-response, etc.
 * MUST NOT be thrown if CAPTCHA was actually performed validation - even if it failed - instead CaptchaVerifierInterface MUST return CaptchaResponseInterface which ::isSuccess() method MUST return false
 */
class CaptchaException extends \RuntimeException
{
}
