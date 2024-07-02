<?php

namespace App\Migration;

class Migration_49 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'timestamps to datetime for Y2038';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		foreach (\PHPFUI\ORM::getTables() as $table)
			{
			foreach (\PHPFUI\ORM::describeTable($table) as $fieldInfo)
				{
				if ('timestamp' == $fieldInfo->type)
					{
					$parameters = 'datetime';

					if (! $fieldInfo->nullable)
						{
						$parameters .= ' not';
						}

					$parameters .= ' null';

					if ($fieldInfo->defaultValue)
						{
						$defaultValue = $fieldInfo->defaultValue;

						if ('0000-00-00 00:00:00' == $defaultValue)
							{
							$defaultValue = '"0000-00-00 00:00:00"';
							}
						$parameters .= ' default ' . $defaultValue;
						}

					$extra = \str_replace('DEFAULT_GENERATED', '', $fieldInfo->extra);
					$parameters .= $extra;

					$this->alterColumn($table, $fieldInfo->name, $parameters);
					}
				}
			}

		return true;
		}
	}
