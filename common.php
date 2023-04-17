<?php
include 'commonbase.php';

function trans(string $text, array $parameters = []) : string
	{
	return \PHPFUI\Translation\Translator::trans($text, $parameters);
	}

\App\Tools\SessionManager::start();

\PHPFUI\Translation\Translator::setTranslationDirectory(PROJECT_ROOT . '/languages/installed');

$dbSettings = new \App\Settings\DB();
$pdo = $dbSettings->getPDO();
if ($pdo)
	{
	\PHPFUI\ORM::addConnection($pdo);
	\PHPFUI\ORM::setLogger(new \PHPFUI\ORM\StandardErrorLogger());
	}

\PHPFUI\Translation\Translator::setTranslationDirectory(__DIR__ . '/languages');
\PHPFUI\Translation\Translator::setLocale('EnglishUS');
