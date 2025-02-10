<?php
/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.0.0-rc.1|configurator
 * you can change this configuration by importing this file.
 *
 */

$config = include 'vendor/phpfui/phpunit-syntax-coverage/PhpCsFixer.php';

return $config->setFinder(PhpCsFixer\Finder::create()
			->exclude('vendor')
			->in(__DIR__.'/App')
			->in(__DIR__.'/oneOffScripts')
			->in(__DIR__.'/www')
			->in(__DIR__.'/Tests')
			->in(__DIR__.'/NoNameSpace')
			->in(__DIR__.'/Twilio')
    );
