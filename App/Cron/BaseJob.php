<?php

namespace App\Cron;

abstract class BaseJob
	{
	public function __construct(protected \App\Cron\Controller $controller)
		{
		$this->controller = $controller;
		}

	abstract public function getDescription() : string;

	public function getDisabledKey() : string
		{
		$key = 'CronDisabled' . $this->getName();

		return \substr($key, 0, 30);
		}

	public function getName() : string
		{
		$class = static::class;
		$class = \substr($class, \strrpos($class, '\\') + 1);

		return $class;
		}

	public function isDisabled() : bool
		{
		return $this->controller->isDisabled($this);
		}

	/** @param array<string, string> $parameters */
	abstract public function run(array $parameters = []) : void;

	abstract public function willRun() : bool;
	}
