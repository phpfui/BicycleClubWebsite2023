<?php

namespace App\Table;

class PointHistory extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PointHistory::class;

	public function find(array $parameters) : \PHPFUI\ORM\DataObjectCursor
	 {
	 $fields = $this->getFields();
	 $fields['time_min'] = 0;
	 $fields['time_max'] = 0;

	 foreach ($fields as $field => $value)
		 {
		 if (empty($parameters[$field]))
			 {
			 unset($parameters[$field]);
			 }
		 }

	 return parent::find($parameters);
	 }
	}
