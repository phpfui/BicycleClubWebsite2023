<?php

namespace App\View\System;

class Releases
	{
	public function __construct(private readonly \Gitonomy\Git\Repository $model)
		{
		}

	/**
	 * @param array<string,\Gitonomy\Git\Reference\Tag> $releases
	 */
	public function list(array $releases) : \PHPFUI\Container
		{
		$queryParameters = [\PHPFUI\Session::csrfField() => \PHPFUI\Session::csrf()];

		$parser = new \PHPFUI\InstaDoc\MarkDownParser();

		$head = $this->model->getHeadCommit();
		$currentHash = $head->getHash();

		$accordion = new \App\UI\Accordion();

		foreach ($releases as $sha1 => $tag)
			{
			$releaseTag = new \App\Model\ReleaseTag($tag->getName());

			// Is it a release tag?, if not skip it.
			if (! $releaseTag->isTag())
				{
				continue;
				}

			$installed = $sha1 == $currentHash ? 'Currently Installed' : '';

			$container = new \PHPFUI\Container();
			$queryParameters['sha1'] = $sha1;
			$queryParameters['tag'] = $tag->getName();

			$markdown = $tag->getMessage();
			// git considers lines in tags that start with # to be comments, so they need a space in front of them, but then markdown does not see it,
			// so replace leading spaces followed by # with just #
			$markdown = \str_replace(' #', '#', (string)$markdown);
			$displayText = $parser->text($markdown);
			$container->add($displayText);

			if (! $releaseTag->isValid())
				{
				$container->add('<hr>');
				$callout = new \PHPFUI\Callout('alert');
				$callout->add($releaseTag->getError());
				$container->add($callout);
				$installed = 'This release is incompatible with your server';
				}

			if (! $installed)
				{
				$container->add('<hr>');
				$uri = '/System/Releases/releases?' . \http_build_query($queryParameters);
				$button = new \PHPFUI\Button('Deploy', $uri);
				$button->setConfirm('Deploy this release?');
				$container->add($button);
				}

			$label = new \PHPFUI\MultiColumn();
			$label->add($releaseTag->getMarketingVersion());
			$label->add($tag->getTaggerDate()->format('Y-m-d H:i:s'));
			$label->add($installed);

			$accordion->addTab($label, $container);
			}
		$container = new \PHPFUI\Container();
		$container->add($accordion);

		if (! \count($accordion))
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add('No Releases Found');
			$container->add($callout);
			}

		return $container;
		}
	}
