<?php

namespace App\Table;

class Customer extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\Customer::class;
}
