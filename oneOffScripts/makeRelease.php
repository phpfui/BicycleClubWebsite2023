<?php

include 'common.php';

echo "Make a release (NOTE: lines starting with # in the release notes will be ignored by git)\n\n";

if (\in_array('-?', $argv) || \in_array('--help', $argv))
	{
	echo "Parameters:\n\n";
	echo "    --overwrite (overwrite existing tag)\n";
	echo "    -? or --help (this help)\n";

	exit;
	}

$releaseNotes = new \App\Model\ReleaseNotes();
$highestVersion = $releaseNotes->getHighestRelease();

$version = \App\Model\ReleaseTag::VERSION_PREFIX . $highestVersion;

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

$releaseNoteName = "files/releaseNotes/{$version}.md";

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
