# Google's reCAPTCHA PHP client library

[![Build Status](https://github.com/phpfui/recaptcha/actions/workflows/php.yml/badge.svg)](https://github.com/phpfui/recaptcha/actions)
[![Latest Stable Version](https://img.shields.io/packagist/v/phpfui/recaptcha.svg)](https://packagist.org/packages/phpfui/recaptcha)
![](https://img.shields.io/badge/PHPStan-level%205-brightgreen.svg?style=flat)

reCAPTCHA is a free CAPTCHA service that protects websites from spam and abuse. This is a PHP library that wraps up the server-side verification step required to process responses from the reCAPTCHA service. This client supports both V2 and V3.

- reCAPTCHA: https://www.google.com/recaptcha
- This repo: https://github.com/phpfui/recaptcha
- Hosted demo: https://recaptcha-demo.appspot.com/
- Version: 2.0
- License: BSD, see [LICENSE](LICENSE)

### Notice: This is not an officially supported version of the Google Recaptcha package

It appears that Google is no longer interested in supporting their PHP Open Source packages for newer versions of PHP (8.4 and up). This package is an updated version of the original Google package. It has been updated to GitHub Actions and modern PHP standards. Every effort has been made to not change logic or behavior other than conformating to modern PHP standards.

This package will be retired if Google decides to support modern PHP versions.

### Support for earlier versions of PHP

The 2.0 release allows for PHP 8.4. If you need only PHP 8.3 support and lower, you should continue to use the google/recaptcha package. For versions of PHP before 8.0, you will need to stay with the official Google 1.2 release on packagist (google/recaptcha).

The classes in the project are structured according to the [PSR-4](https://www.php-fig.org/psr/psr-4/) standard, so you can also use your own autoloader or require the needed files directly in your code.

## Usage

First obtain the appropriate keys for the type of reCAPTCHA you wish to integrate for v2 at https://www.google.com/recaptcha/admin or v3 at https://g.co/recaptcha/v3.

Then follow the [integration guide on the developer site](https://developers.google.com/recaptcha/intro) to add the reCAPTCHA functionality into your frontend.

This library comes in when you need to verify the user's response. On the PHP side you need the response from the reCAPTCHA service and secret key from your credentials. Instantiate the `ReCaptcha` class with your secret key, specify any additional validation rules, and then call `verify()` with the reCAPTCHA response (usually in `$_POST['g-recaptcha-response']` or the response from `grecaptcha.execute()` in JS which is in `$gRecaptchaResponse` in the example) and user's IP address. For example:

```php
$recaptcha = new \ReCaptcha\ReCaptcha($secret);
$resp = $recaptcha->setExpectedHostname('recaptcha-demo.appspot.com')
                  ->verify($gRecaptchaResponse, $remoteIp);
if ($resp->isSuccess()) {
    // Verified!
} else {
    $errors = $resp->getErrorCodes();
}
```

The following methods are available:

- `setExpectedHostname($hostname)`: ensures the hostname matches. You must do
  this if you have disabled "Domain/Package Name Validation" for your
  credentials.
- `setExpectedApkPackageName($apkPackageName)`: if you're verifying a response
  from an Android app. Again, you must do this if you have disabled
  "Domain/Package Name Validation" for your credentials.
- `setExpectedAction($action)`: ensures the action matches for the v3 API.
- `setScoreThreshold($threshold)`: set a score threshold for responses from the
  v3 API
- `setChallengeTimeout($timeoutSeconds)`: set a timeout between the user passing
  the reCAPTCHA and your server processing it.

Each of the `set`\*`()` methods return the `ReCaptcha` instance so you can chain them together. For example:

```php
$recaptcha = new \ReCaptcha\ReCaptcha($secret);
$resp = $recaptcha->setExpectedHostname('recaptcha-demo.appspot.com')
                  ->setExpectedAction('homepage')
                  ->setScoreThreshold(0.5)
                  ->verify($gRecaptchaResponse, $remoteIp);

if ($resp->isSuccess()) {
    // Verified!
} else {
    $errors = $resp->getErrorCodes();
}
```

You can find the constants for the libraries error codes in the `ReCaptcha` class constants, e.g. `ReCaptcha::E_HOSTNAME_MISMATCH`

For more details on usage and structure, see [ARCHITECTURE](ARCHITECTURE.md).

### Examples

You can see examples of each reCAPTCHA type in [examples/](examples/). You can run the examples locally by using the Composer script:

```sh
composer run-script serve-examples
```

This makes use of the in-built PHP dev server to host the examples at http://localhost:8080/

These are also hosted on Google AppEngine Flexible environment at https://recaptcha-demo.appspot.com/. This is configured by [`app.yaml`](./app.yaml) which you can also use to [deploy to your own AppEngine project](https://cloud.google.com/appengine/docs/flexible/php/download).

## Contributing

No one ever has enough engineers, so we're very happy to accept contributions via Pull Requests. Please run PHPCSFixer and PHPStan on all PRs.