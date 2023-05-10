<?php

namespace App\Model;

class Deploy
	{
	private readonly \PHPFUI\ORM\Migrator $migrationModel;

	private readonly \App\Tools\SortedTags $sortedTags;

	public function __construct(private readonly \Gitonomy\Git\Repository $repo)
		{
		$this->repo->run('fetch');
		$this->sortedTags = new \App\Tools\SortedTags($this->repo);
		$this->migrationModel = new \PHPFUI\ORM\Migrator();
		}

	/**
	 * @return array of migration errors
	 */
	public function deployTarget(string $target) : array
		{
		$releaseName = 'Release';
		$currentMigration = $this->migrationModel->getCurrentMigrationId();
		$newMigration = 0;
		$tag = $this->sortedTags->getTag($target);

		if ($tag)
			{
			// if we are deploying a tag, set the target to the tag name, so we can see what migration they are on.
			$target = $tag->getName();
			}

		// See if we are on a tag, and if so, get the migration number from it (middle part)
		if (0 == \substr_compare((string)$target, $releaseName, 0, \strlen($releaseName)))
			{
			$versions = \explode('.', (string)$target);

			if (3 == \count($versions))
				{
				$newMigration = (int)$versions[1];
				}
			}
		else
			{
			// going with the latest migration
			$newMigration = 0;
			}

		// are we downgrading?
		if ($newMigration && $newMigration < $currentMigration)
			{
			// go to that version first, before we lose the source
			$this->migrationModel->migrateTo($newMigration);
			}

		$this->repo->run('prune');         // remove old versions and branches
		$this->repo->run('stash');         // stash any changed files, user can get back stash if needed so we won't delete it
		$this->repo->run('clean', ['-f']); // remove unstaged files
		$wc = $this->repo->getWorkingCopy();
		$wc->checkout($target);
		// allow the documentation to refresh next time it is used
		foreach (\glob(PROJECT_ROOT . '/*.serial') as $file)
			{
			@\unlink($file);
			}
		$this->migrationModel->migrate();

		// if we are targeting a migration, then we are on a release, so update standard permissions
		if ($newMigration)
			{
			$permissionModel = new \App\Model\Permission();
			$permissionModel->loadStandardPermissions();
			}

		// delete errors since we could get a deploy error and have to rerun
		$errors = new \App\Model\Errors();
		$errors->deleteAll();

		return $this->migrationModel->getErrors();
		}

	public function getReleaseTags() : array
		{
		return $this->sortedTags->getTags(\App\Model\ReleaseTag::VERSION_PREFIX);
		}

	public function sendUpgradeEmail(\Gitonomy\Git\Reference\Tag $tag, string $version, string $errorMessage, array $errors) : void
		{
		$email = new \App\Tools\EMail();
		$email->setHtml();
		$memberTable = new \App\Table\Member();
		$memberTable->getMembersWithPermission('Super User');
		foreach($memberTable->getArrayCursor() as $member)
			{
			$email->addToMember($member);
			}

		if ($errorMessage || $errors)
			{
			$email->setSubject($_SERVER['SERVER_NAME'] . ' failed to update to release ' . $version);
			$email->setBody($errorMessage . '<hr><pre>' . \print_r($errors, true) . '</pre>');
			}
		else
			{
			$email->setSubject($_SERVER['SERVER_NAME'] . ' updated to release ' . $version);
			$markdown = $tag->getMessage();
			// git considers lines in tags that start with # to be comments, so they need a space in front of them, but then markdown does not see it,
			// so replace leading spaces followed by # with just #
			$markdown = \str_replace(' #', '#', $markdown);
			$parser = new \PHPFUI\InstaDoc\MarkDownParser();
			$displayText = $parser->text($markdown);
			$settingTable = new \App\Table\Setting();

			$dom = new \voku\helper\HtmlDomParser($displayText);

			foreach ($dom->find('a') as $node)
				{
				$link = $node->getAttribute('href');

				if (! \str_contains($link, 'http'))
					{
					$link = $settingTable->value('homePage') . $link;
					$node->setAttribute('href', $link);
					}
				}
			$email->setBody("{$dom}");
			}
		$email->bulkSend();
		}

	public function updateToLatest() : void
		{
		$tags = $this->getReleaseTags();

		if (! \count($tags))
			{
			// No releases found, abort
			return;
			}
		// get the most recent tag
		$tag = \reset($tags);
		// are we on an existing tag and not ourselves
		$currentHash = $this->repo->getHeadCommit()->getHash();
		$latestHash = $tag->getCommit()->getHash();

		if ($currentHash == $latestHash)
			{
			// same hash, we are already on the latest
			return;
			}

		if (! isset($tags[$currentHash]))
			{
			// tag not found, must not be on a release commit
			return;
			}
		$releaseTag = new \App\Model\ReleaseTag($tag->getName());



		$errors = [];

		if ($releaseTag->isTag() && $releaseTag->isValid())
			{
			$errors = $this->deployTarget($latestHash);
			}
		$this->sendUpgradeEmail($tag, $releaseTag->getMarketingVersion(), $releaseTag->getError(), $errors);
		}
	}
