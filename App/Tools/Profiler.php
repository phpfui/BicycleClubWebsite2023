<?php

namespace App\Tools;

class Profiler
	{
	protected \SplStack $callstack;

	protected array $memory = [];

	protected array $times = [];

	private readonly \App\Tools\Logger $logger;

	public function __construct()
		{
		$this->logger = new \App\Tools\Logger();
		$this->callstack = new \SplStack();
		}

	public function __destruct()
		{
		$this->stop();
		$this->outputNow();
		}

	public function outputNow() : void
		{
		$this->logger->debug($this->times);
		$this->logger->debug($this->memory);
		$this->logger->outputNow();
		}

	public function start(string $file) : void
		{
		if (\count($this->callstack))
			{
			foreach ($this->callstack as $timer)
				{
				$timer->pause();
				}
			}
		$this->callstack->push(new \App\Tools\Timer($file, true));
		}

	public function stop() : void
		{
		if (\count($this->callstack))
			{
			$timer = $this->callstack->pop();
			$time = $timer->get();
			$name = $timer->getName();

			if (! isset($this->times[$name]))
				{
				$this->times[$name] = 0;
				}
			$this->memory[$name] = $timer->getMemory();
			$this->times[$name] += $time;

			if (\count($this->callstack))
				{
				$this->callstack->top()->resume();
				}
			}
		}
	}
