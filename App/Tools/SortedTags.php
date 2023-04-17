<?php

namespace App\Tools;

class SortedTags
	{
	private array $tags = [];

	public function __construct(\Gitonomy\Git\Repository $repo)
		{
		$tags = $repo->getReferences()->getTags();
		\usort($tags, $this->cmp(...));

		foreach ($tags as $tag)
			{
			$this->tags[$tag->getCommit()->getHash()] = $tag;
			}
		}

	public function getTag(string $sha1) : ?\Gitonomy\Git\Reference\Tag
		{
		return $this->tags[$sha1] ?? null;
		}

	public function getTags(string $tagPrefix = '') : array
		{
		if (! $tagPrefix)
			{
			return $this->tags;
			}

		$tags = [];
		$len = \strlen($tagPrefix);

		foreach ($this->tags as $sha1 => $tag)
			{
			if (0 == \substr_compare($tagPrefix, (string)$tag->getName(), 0, $len))
				{
				$tags[$sha1] = $tag;
				}
			}

		return $tags;
		}

	protected function cmp(\Gitonomy\Git\Reference\Tag $lhs, \Gitonomy\Git\Reference\Tag $rhs) : int
		{
		$lhsValues = \explode('.', (string)$lhs->getName());
		$rhsValues = \explode('.', (string)$rhs->getName());
		$index = 1;

		if ($lhsValues[$index] == $rhsValues[$index])
			{
			++$index;
			}

		return (int)$rhsValues[$index] <=> (int)$lhsValues[$index];
		}
	}
