<?php

namespace App\Table;

class PollAnswer extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PollAnswer::class;

	/**
	 * @param array<string> $answers
	 */
	public function saveAnswers(int $pollId, array $answers) : void
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('pollId', $pollId));
		$this->delete();
		$answerId = 0;

		foreach ($answers as $answer)
			{
			$insertAnswer = new \App\Record\PollAnswer();
			$insertAnswer->setFrom(['answer' => $answer, 'pollId' => $pollId, 'pollAnswerId' => ++$answerId]);
			$insertAnswer->insert();
			}
		}
	}
