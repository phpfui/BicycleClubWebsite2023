<?php

namespace Psr\Captcha;

/**
 * Interface of the object that CaptchaVerifierInterface MUST return on ::verify() method.
 * MUST contain enough information to consistently say whether user successfully passed Captcha or not.
 * SHOULD contain actual user's SCORING
 * MAY contain additional information (e.g., gathered from it's captcha-vendor service's verification endpoint) (i.e. message, errors, etc.)
 */
interface CaptchaResponseInterface
{
  /**
   * MUST return true/false depends on whether verification was successful or not (is user a bot or not).
   *
   * @return bool
   */
  public function isSuccess(): bool;
}
