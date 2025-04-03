<?php

if ('WIN' === \strtoupper(\substr(PHP_OS, 0, 3)))
	{
	$php = 'php';
	$composer = 'composer';
	}
else
	{
	$php = '/usr/bin/php8.4-cli';
	$composer = $php . ' composer.phar';
	}

\exec($composer . ' self-update');

include 'commonbase.php';

$updater = new ComposerUpdate();

\exec($composer . ' update');

// Localize files
$updater->setIgnoredRepos([
	'components',
	'doctrine',
	'GPBMetadata',
	'Jean85',
	'OndraM',
	'PackageVersions',
	'phar-io',
	'sebastian',
	'phpunit',
	'phpspec',
	'ralouphie',
	'Symplify',
	'tecnickcom',
	'theseer',
	'twig',
]);

$updater->setBaseDirectory(PROJECT_ROOT . '/');
$updater->update();
$updater->copyDirectory(__DIR__ . '/vendor/maximebf/debugbar/src/DebugBar/Resources', __DIR__ . '/DebugBar/Resources');
$updater->deleteNamespace('Symfony\Polyfill');
$updater->deleteNamespace('HighlightUtilities');
$updater->deleteNamespace('Highlight\Highlight');
$updater->deleteNamespace('Highlight\HighlightUtilities');
$updater->deleteNamespace('HighlightUtilities');
$updater->deleteNamespace('Sample');
$updater->deleteFileInNamespace('NoNameSpace', 'fpdf.php');
$updater->deleteFileInNamespace('Laminas\ServiceManager', 'autoload.php');
$updater->deleteFileInNamespace('Laminas\ServiceManager', 'copyright.md');
$updater->deleteFileInNamespace('Laminas\ServiceManager', 'readme.md');
$updater->deleteFileInNamespace('Twilio', 'autoload.php');
$updater->deleteFileInNamespace('setasign\Fpdi', 'autoload.php');
$updater->deleteFileInNamespace('DeepCopy', 'deep_copy.php');
$updater->deleteFileInNamespace('GuzzleHttp', 'functions.php');
$updater->deleteFileInNamespace('GuzzleHttp', 'functions_include.php');
$updater->deleteFileInNamespace('Clue\StreamFilter', 'functions_include.php');
$updater->deleteFileInNamespace('Clue\StreamFilter', 'functions.php');

// update the public files
\exec($php . ' vendor/phpfui/phpfui/update.php www/PHPFUI');
\exec($php . ' vendor/phpfui/instadoc/install.php www/PHPFUI');

$source = __DIR__ . '/vendor/phpfui/orm/translations';
$dest = __DIR__ . '/languages/';

foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item)
	{
  if ($item->isDir())
		{
		$dir = $dest . $iterator->getSubPathName();

		if (! \is_dir($dir))
			{
			\mkdir($dir, 0777, true);
			}
		}
  else
		{
		\copy($item, $dest . $iterator->getSubPathName());
		}
	}
