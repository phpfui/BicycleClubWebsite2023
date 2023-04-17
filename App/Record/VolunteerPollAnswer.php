<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class VolunteerPollAnswer extends \App\Record\Definition\VolunteerPollAnswer
	{
	public function clean() : static
		{
		$this->cleanProperName('answer');

		return $this;
		}
	}
