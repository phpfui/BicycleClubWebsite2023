<?php

namespace App\UI;

class SubstitutionFields
	{
	/**
	 * @param array<string,mixed> $fields
	 */
	public function __construct(private array $fields)
		{
		}

	public function __toString() : string
		{
		$container = new \PHPFUI\Container();
		$callout = new \PHPFUI\Callout('info');
		$callout->add('You can substitute member specific fields in the body of text. The following may be substituted for the member\'s value. They are <strong>CASE SENSITIVE</strong>, so copy them exactly as you see them.<p>');
		$container->add($callout);

		$multiColumn = new \PHPFUI\MultiColumn();

		foreach ($this->fields as $field => $value)
			{
			if (\count($multiColumn) >= 3)
				{
				$container->add($multiColumn);
				$multiColumn = new \PHPFUI\MultiColumn();
				}
			$multiColumn->add("~{$field}~");
			}

		while (\count($multiColumn) < 3)
			{
			$multiColumn->add('&nbsp;');
			}
		$container->add($multiColumn);

		return "{$container}";
		}
	}
