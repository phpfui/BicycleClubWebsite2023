<?php

namespace App\Record;

class SurveyQuestion extends \App\Record\Definition\SurveyQuestion
	{
	public function clean() : static
		{
		if (' ' === $this->separator)
			{
			$this->separator = null;
			}

		return $this;
		}
	}
