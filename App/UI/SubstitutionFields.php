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

		$count = \count($this->fields);
		$perColumn = \floor($count / 3);
		$extra = $count % 3;
		$addedExtra = $extra-- > 0 ? 1 : 0;

		$ul = new \PHPFUI\UnorderedList();
		$multiColumn = new \PHPFUI\MultiColumn();
		$count = 0;

		foreach ($this->fields as $field => $value)
			{
			$ul->addItem(new \PHPFUI\ListItem("~{$field}~"));

			if (++$count >= $perColumn + $addedExtra)
				{
				$addedExtra = $extra-- > 0 ? 1 : 0;
				$multiColumn->add($ul);
				$count = 0;
				$ul = new \PHPFUI\UnorderedList();
				}
			}

		if (\count($ul))
			{
			$multiColumn->add($ul);
			}
		$container->add($multiColumn);

		return "{$container}";
		}
	}
