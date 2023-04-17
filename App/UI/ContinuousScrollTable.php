<?php

namespace App\UI;

class ContinuousScrollTable extends \App\UI\PaginatedTable
	{
	public function __construct(?\PHPFUI\Interfaces\Page $page, readonly \PHPFUI\ORM\Table $dataTable)
		{
		parent::__construct($page, $dataTable);
		$this->setContinuousScroll(true);
		}
	}
