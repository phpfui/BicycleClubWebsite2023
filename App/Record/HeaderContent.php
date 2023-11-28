<?php

namespace App\Record;

class HeaderContent extends \App\Record\Definition\HeaderContent
	{
	public function clean() : static
		{
		if ($this->startDate <= '0000-00-00')
			{
			$this->startDate = null;
			}

		if ($this->endDate <= '0000-00-00')
			{
			$this->endDate = null;
			}

		if (! $this->showMonth)
			{
			$this->showMonth = null;
			}

		if (! $this->showDay)
			{
			$this->showDay = null;
			}

		return $this;
		}
	}
