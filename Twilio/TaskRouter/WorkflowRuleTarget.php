<?php

namespace Twilio\TaskRouter;

/**
 * Twilio TaskRouter Workflow Rule Target
 *
 * @author Justin Witz <jwitz@twilio.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 */
class WorkflowRuleTarget implements \JsonSerializable
{
	public $expression;

	public $priority;

	public $queue;

	public $timeout;

	public function __construct(string $queue, ?int $priority = null, ?int $timeout = null, ?string $expression = null) {
		$this->queue = $queue;
		$this->priority = $priority;
		$this->timeout = $timeout;
		$this->expression = $expression;
	}

	public function jsonSerialize() : array {
		$json = [];
		$json['queue'] = $this->queue;

		if (null !== $this->priority) {
			$json['priority'] = $this->priority;
		}

		if (null !== $this->timeout) {
			$json['timeout'] = $this->timeout;
		}

		if (null !== $this->expression) {
			$json['expression'] = $this->expression;
		}

		return $json;
	}
}
