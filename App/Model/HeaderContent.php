<?php

namespace App\Model;

class HeaderContent
	{
	private ?\App\Record\HeaderContent $testContent = null;

	private int $today;

	private string $todayString;

	public function __construct(private string $url)
		{
		$this->url = \strtolower($url);
		$this->today = \App\Tools\Date::today();
		$this->todayString = \App\Tools\Date::todayString();
		}

	public function getActiveHeaderContent() : ?\App\Record\HeaderContent
		{
		if ($this->testContent)
			{
			return $this->testContent;
			}

		$table = new \App\Table\HeaderContent();
		$condition = new \PHPFUI\ORM\Condition('active', 1);
		$month = \App\Tools\Date::month($this->today);
		$day = \App\Tools\Date::day($this->today);
		$condition->and($this->getFieldCondition('showDay', $day));
		$condition->and($this->getFieldCondition('showMonth', $month));
		$condition->and($this->getFieldCondition('startDate', $this->todayString, new \PHPFUI\ORM\Operator\LessThanEqual()));
		$condition->and($this->getFieldCondition('endDate', $this->todayString, new \PHPFUI\ORM\Operator\GreaterThanEqual()));
		$table->setWhere($condition);
		$table->addOrderBy('showDay', 'desc');
		$table->addOrderBy('showMonth', 'desc');
		$table->addOrderBy('startDate', 'desc');
		$table->addOrderBy('endDate', 'desc');

		$headers = $table->getRecordCursor();

		foreach ($headers as $header)
			{
			if ($this->isValid($header))
				{
				return $header;
				}
			}

		return null;
		}

	public function setHeaderContent(\App\Record\HeaderContent $testContent) : static
		{
		$this->testContent = $testContent;

		return $this;
		}

	private function getFieldCondition(string $field, string | int $value, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal()) : \PHPFUI\ORM\Condition
		{
		$condition = new \PHPFUI\ORM\Condition($field, $value, $operator);
		$condition->or($field, null, new \PHPFUI\ORM\Operator\IsNull());

		return $condition;
		}

	private function isValid(\App\Record\HeaderContent $header) : bool
		{
		if (! \str_contains($this->url, \strtolower($header->urlPath)))
			{
			return false;
			}

		if ($header->startDate && $header->startDate > $this->todayString)
			{
			return false;
			}

		return ! ($header->endDate && $header->endDate < $this->todayString);
		}
	}
