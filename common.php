<?php
include 'commonbase.php';

function trans(string $text, array $parameters = []) : string
	{
	return \PHPFUI\Translation\Translator::trans($text, $parameters);
	}

\App\Tools\SessionManager::start();

$dbSettings = new \App\Settings\DB();
date_default_timezone_set($dbSettings->timeZone ?? 'America/New_York');
$pdo = $dbSettings->getPDO();
\PHPFUI\ORM::setLogger(new \PHPFUI\ORM\StandardErrorLogger());
if (! $pdo)
	{
	\PHPFUI\ORM::log(\Psr\Log\LogLevel::EMERGENCY, $dbSettings->getError());
	exit;
	}
\PHPFUI\ORM::addConnection($pdo);
\PHPFUI\ORM::setTranslationCallback([\PHPFUI\Translation\Translator::class, 'trans']);
\PHPFUI\Translation\Translator::setTranslationDirectory(PROJECT_ROOT . '/languages');
\PHPFUI\Translation\Translator::setLocale('en_US');
