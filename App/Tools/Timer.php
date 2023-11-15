<?php

namespace App\Tools;

class Timer
	{
	/**
	 * @var array<int,array<string,string>>
	 */
	private array $laps = [];

	private float $lastLap = 0;

	private int $memory = 0;

	private float $pause_time = 0;

	private bool $running = false;

	private float $start = 0;

	public function __construct(private readonly string $name = '', bool $run = false)
		{
		$this->memory = \memory_get_usage();

		if ($run)
			{
			$this->start();
			}
		}

	public function get(int $decimals = 8) : string
		{
		return \number_format(($this->get_time() - $this->start), $decimals);
		}

	/**
	 * @return array<int,array<string,string>>
	 */
	public function getLaps() : array
		{
		return $this->laps;
		}

	public function getMemory() : int
		{
		return $this->memory;
		}

	public function getName() : string
		{
		return $this->name;
		}

	public function isRunning() : bool
		{
		return $this->running;
		}

	public function lap(string $label, int $decimals = 8) : void
		{
		$time = $this->get_time();
		$this->laps[] = ['time' => \number_format($time - $this->lastLap, $decimals),
			'label' => $label, ];
		$this->lastLap = $time;
		}

	public function pause() : void
		{
		if ($this->running)
			{
			$this->pause_time = $this->get_time();
			$this->running = false;
			}
		}

	public function resume() : void
		{
		$this->start += ($this->get_time() - $this->pause_time);
		$this->running = true;
		$this->pause_time = 0;
		}

	public function start() : void
		{
		$this->pause_time = 0;
		$this->running = true;
		$this->start = $this->lastLap = $this->get_time();
		}

	private function get_time() : float
		{
		[$usec, $sec] = \explode(' ', \microtime());

		return (float)$usec + (float)$sec;
		}
	}
