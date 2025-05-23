<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DebugBar\DataCollector;

use DebugBar\DebugBarException;

/**
 * Collects info about the request duration as well as providing
 * a way to log duration of any operations
 */
class TimeDataCollector extends DataCollector implements Renderable
{
    /**
     * @var float
     */
    protected $requestStartTime;

    /**
     * @var float
     */
    protected $requestEndTime;

    /**
     * @var array
     */
    protected $startedMeasures = array();

    /**
     * @var array
     */
    protected $measures = array();

    /**
     * @var bool
     */
    protected $memoryMeasure = false;

    /**
     * @param float $requestStartTime
     */
    public function __construct($requestStartTime = null)
    {
        if ($requestStartTime === null) {
            if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
                $requestStartTime = $_SERVER['REQUEST_TIME_FLOAT'];
            } else {
                $requestStartTime = microtime(true);
            }
        }
        $this->requestStartTime = (float)$requestStartTime;
        static::getDefaultDataFormatter(); // initializes formatter for lineal timeline
    }

    /**
     * Starts memory measuring
     */
    public function showMemoryUsage()
    {
        $this->memoryMeasure = true;
    }

    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string|null $label Public name
     * @param string|null $collector The source of the collector
     * @param string|null $group The group for aggregates
     */
    public function startMeasure($name, $label = null, $collector = null, $group = null)
    {
        $start = microtime(true);
        $this->startedMeasures[$name] = array(
            'label' => $label ?: $name,
            'start' => $start,
            'memory' => $this->memoryMeasure ? memory_get_usage(false) : null,
            'collector' => $collector,
            'group' => $group,
        );
    }

    /**
     * Check a measure exists
     *
     * @param string $name
     * @return bool
     */
    public function hasStartedMeasure($name)
    {
        return isset($this->startedMeasures[$name]);
    }

    /**
     * Stops a measure
     *
     * @param string $name
     * @param array $params
     * @throws DebugBarException
     */
    public function stopMeasure($name, $params = array())
    {
        $end = microtime(true);
        if (!$this->hasStartedMeasure($name)) {
            throw new DebugBarException("Failed stopping measure '$name' because it hasn't been started");
        }
        if (! is_null($this->startedMeasures[$name]['memory'])) {
            $params['memoryUsage'] = memory_get_usage(false) - $this->startedMeasures[$name]['memory'];
        }
        $this->addMeasure(
            $this->startedMeasures[$name]['label'],
            $this->startedMeasures[$name]['start'],
            $end,
            $params,
            $this->startedMeasures[$name]['collector'],
            $this->startedMeasures[$name]['group']
        );
        unset($this->startedMeasures[$name]);
    }

    /**
     * Adds a measure
     *
     * @param string $label
     * @param float $start
     * @param float $end
     * @param array $params
     * @param string|null $collector
     * @param string|null $group
     */
    public function addMeasure($label, $start, $end, $params = array(), $collector = null, $group = null)
    {
        if (isset($params['memoryUsage'])) {
            $memory = $this->memoryMeasure ? $params['memoryUsage'] : 0;
            unset($params['memoryUsage']);
        }

        $this->measures[] = array(
            'label' => $label,
            'start' => $start,
            'relative_start' => $start - $this->requestStartTime,
            'end' => $end,
            'relative_end' => $end - $this->requestEndTime,
            'duration' => $end - $start,
            'duration_str' => $this->getDataFormatter()->formatDuration($end - $start),
            'memory' => $memory ?? 0,
            'memory_str' => $this->getDataFormatter()->formatBytes($memory ?? 0),
            'params' => $params,
            'collector' => $collector,
            'group' => $group,
        );
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string $label
     * @param \Closure $closure
     * @param string|null $collector
     * @param string|null $group
     * @return mixed
     */
    public function measure($label, \Closure $closure, $collector = null, $group = null)
    {
        $name = spl_object_hash($closure);
        $this->startMeasure($name, $label, $collector, $group);
        $result = $closure();
        $params = is_array($result) ? $result : array();
        $this->stopMeasure($name, $params);
        return $result;
    }

    /**
     * Returns an array of all measures
     *
     * @return array
     */
    public function getMeasures()
    {
        return $this->measures;
    }

    /**
     * Returns the request start time
     *
     * @return float
     */
    public function getRequestStartTime()
    {
        return $this->requestStartTime;
    }

    /**
     * Returns the request end time
     *
     * @return float
     */
    public function getRequestEndTime()
    {
        return $this->requestEndTime;
    }

    /**
     * Returns the duration of a request
     *
     * @return float
     */
    public function getRequestDuration()
    {
        if ($this->requestEndTime !== null) {
            return $this->requestEndTime - $this->requestStartTime;
        }
        return microtime(true) - $this->requestStartTime;
    }

    /**
     * @return array
     * @throws DebugBarException
     */
    public function collect()
    {
        $this->requestEndTime = microtime(true);
        foreach (array_keys($this->startedMeasures) as $name) {
            $this->stopMeasure($name);
        }

        usort($this->measures, function($a, $b) {
            if ($a['start'] == $b['start']) {
                return 0;
            }
            return $a['start'] < $b['start'] ? -1 : 1;
        });

        return array(
            'count' => count($this->measures),
            'start' => $this->requestStartTime,
            'end' => $this->requestEndTime,
            'duration' => $this->getRequestDuration(),
            'duration_str' => $this->getDataFormatter()->formatDuration($this->getRequestDuration()),
            'measures' => array_values($this->measures)
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'time';
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        return array(
            "time" => array(
                "icon" => "clock-o",
                "tooltip" => "Request Duration",
                "map" => "time.duration_str",
                'link' => 'timeline',
                "default" => "'0ms'"
            ),
            "timeline" => array(
                "icon" => "tasks",
                "widget" => "PhpDebugBar.Widgets.TimelineWidget",
                "map" => "time",
                "default" => "{}"
            )
        );
    }
}
