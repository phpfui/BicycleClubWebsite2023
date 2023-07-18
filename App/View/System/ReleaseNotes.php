<?php

namespace App\View\System;

class ReleaseNotes implements \Countable
	{
	private readonly \App\Model\ReleaseNotes $model;

	private readonly \PHPFUI\InstaDoc\MarkDownParser $parser;

	/** @var array<string> */
	private array $versions;

	public function __construct()
		{
		$this->parser = new \PHPFUI\InstaDoc\MarkDownParser();
		$this->model = new \App\Model\ReleaseNotes();
		$this->versions = $this->model->getAll();
		\rsort($this->versions);
		}

	public function count() : int
		{
		return \count($this->versions);
		}

	public function getNotes(string $filename) : string
		{
		if (\file_exists($filename))
			{
			// git considers lines in tags that start with # to be comments, so they need a space in front of them, but then markdown does not see it,
			// so replace leading spaces followed by # with just #
			$markdown = \str_replace(' #', '#', \file_get_contents($filename));

			return $this->parser->text($markdown);
			}

		return "File {$filename} was not found.";
		}

	public function show() : \PHPFUI\Accordion
		{
		$accordion = new \PHPFUI\Accordion();
		$first = true;

		foreach ($this->versions as $version)
			{
			$versionName = \str_replace('.md', '', (string)$version);
			$accordion->addTab($versionName, $this->getNotes($this->model->get($version)), $first);
			$first = false;
			}

		return $accordion;
		}
	}
