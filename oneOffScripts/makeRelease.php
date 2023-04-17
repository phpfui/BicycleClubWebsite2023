<?php

include 'common.php';

echo "Make a release (NOTE: lines starting with # in the release notes will be ignored by git)\n\n";

if (\in_array('-?', $argv) || \in_array('--help', $argv))
	{
	echo "Parameters:\n\n";
	echo "    Version (required)\n";
	echo "    --overwrite (overwrite existing tag)\n";
	echo "    -? or --help (this help)\n";

	exit;
	}

$version = $argv[1] ?? null;

if (empty($version))
	{
	echo "You must specify a release as the first parameter\n";

	exit;
	}

if (! \str_starts_with($version, \App\Model\ReleaseTag::VERSION_PREFIX))
	{
	echo 'The version must start with ' . \App\Model\ReleaseTag::VERSION_PREFIX . "\n";

	exit;
	}

$versionParts = \explode('.', \substr($version, \strlen(\App\Model\ReleaseTag::VERSION_PREFIX)));

if (\count($versionParts) < 2)
	{
	echo "The version must contain at least two segments separated by periods. Example: V2.8\n";

	exit;
	}

foreach ($versionParts as $index => $part)
	{
	if ($part != (int)$part)
		{
		echo "The version must contain only numbers.\n";

		exit;
		}
	$versionParts[$index] = \sprintf('%02d', (int)$part);
	}
$version = \App\Model\ReleaseTag::VERSION_PREFIX . \implode('.', $versionParts);

$repo = new \Gitonomy\Git\Repository(PROJECT_ROOT);

// $version should now be valid, check to see if it already exists if no overwrite flag
$sortedTags = new \App\Tools\SortedTags($repo);

$tagsForVersion = $sortedTags->getTags($version . '_');

if (! \in_array('--overwrite', $argv))
	{
	if ($tagsForVersion)
		{
		echo "The version {$version} already exists.  Use --overwrite to change.\n";

		exit;
		}
	}

// check for releaseNotes.md in current commit
$releaseNoteName = "files/releaseNotes/{$version}.md";

if (! \file_exists($releaseNoteName))
	{
	echo "The file {$releaseNoteName} was not found.\n";

	exit;
	}

// delete any existing tags
foreach ($tagsForVersion as $commit => $tag)
	{
	$tag->delete();
	}

// get current migration number
$migrator = new \PHPFUI\ORM\Migrator();
$mostRecentMigration = $migrator->count();

// make the full tag name
$fullTagName = $version . '_' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . $mostRecentMigration;

$currentCommit = $repo->getHeadCommit();
$repo->run('tag', ['-F', $releaseNoteName, $fullTagName, $currentCommit->getHash()]);

echo "{$version} has been tagged as {$fullTagName} and is ready to be pushed for release.\n";
