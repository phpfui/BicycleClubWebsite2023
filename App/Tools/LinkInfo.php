<?php

namespace App\Tools;

class LinkInfo
	{
	protected array $references = [];

	protected bool $scanned = false;

	public function addReference(string $reference) : static
		{
		$this->references[$reference] = true;

		return $this;
		}

	public function beenScanned() : bool
		{
		return $this->scanned;
		}

	/**
	 * @return (int|string)[]
	 *
	 * @psalm-return list<array-key>
	 */
	public function getReferences() : array
		{
		return \array_keys($this->references);
		}

	public function scanned() : static
		{
		$this->scanned = true;

		return $this;
		}
	}
