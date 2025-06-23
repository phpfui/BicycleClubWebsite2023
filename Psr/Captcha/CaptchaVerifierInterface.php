<?php

namespace Psr\Captcha;

/**
 * Interface of Captcha verification service itself.
 * MUST decide whether user passed the Captcha or not and return corresponding response.
 * SHOULD contain method to configure SCORING threshold (if applicable by PROVIDER)
 * SHOULD throw a CaptchaException as soon as possible if appears any non-user related error that prevents correct Captcha solving (e.g. network problems, incorrect secret token, e.g.)
 */
interface CaptchaVerifierInterface
{
  /**
   * Verifies client token and decides whether verification was successful or not (is user a bot or not).
   *
   * @param string $token
   *
   * @throws CaptchaException if Captcha cannot be validated because of non-user problems (e.g. due to network problems, incorrect secret token, etc.)
   * @return CaptchaResponseInterface
   */
  public function verify(string $token): CaptchaResponseInterface;
}
