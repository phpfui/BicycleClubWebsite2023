<?php

namespace PHPFUI\Translation;

class Tree
	{
	private string $name;

	/**
	 * The Tree class emulates a multi-dimentional array.  There is no easy way to have an arbitrary depth on a PHP multi-dimentional and be able to add to it.
	 *
	 * @var array<string,mixed> $translations
	 */
	private array $translations = [];

	private ?string $value = null;

	/**
	 * Make a new Tree.  Each tree has a name and potential value.
	 *
	 * @param string $directory of where to load any found files from
	 */
	public function __construct(string $name, string $directory, ?string $value = null)
		{
		$this->name = $name;
		$this->translations = \PHPFUI\Translation\Translator::load($directory, $name);
		$this->value = $this->translations[$name] ?? $value;
		}

	/**
	 * Look up a section of the tree.
	 *
	 * @param  array<mixed> $parts     the sections to continue navigating for
	 * @param  string       $directory of where to load any found files from
	 *
	 * @return string                  the looked up translated string
	 */
	public function lookup(array $parts, string $directory) : ?string
		{
		$count = \count($parts);

		if (! $count)
			{
			return null;
			}

		if (1 == $count)
			{
			// must be at end, should be index in current array
			if ($parts[0] === $this->name)
				{
				return $this->value;
				}

			if (\is_object($this->translations[$parts[0]] ?? null))
				{
				return $this->translations[$parts[0]]->value;
				}

			return $this->translations[$parts[0]] ?? null;
			}

		$name = \array_shift($parts);
		$value = null;

		if (\is_string($this->translations[$name] ?? null))
			{
			$value = $this->translations[$name];
			}

		if (! (($this->translations[$name] ?? null) instanceof self))
			{
			$this->translations[$name] = new self($name, $directory, $value);
			}
		$directory .= '/' . $name;

		return $this->translations[$name]->lookup($parts, $directory);
		}
	}
