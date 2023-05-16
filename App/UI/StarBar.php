<?php

namespace App\UI;

class StarBar extends \PHPFUI\HTML5Element
	{
	public function __construct(int $numberStars, float $value)
		{
		parent::__construct('span');
		$value = \round($value, 1);
		// .3 .4 .5 .6 ,7 are half stars
		for ($i = 0.0; $i < $numberStars; $i += 1.0)
			{
			$i = \round($i, 1);

			if ($value < \round($i + 0.3, 1))
				{
				$this->add(new \PHPFUI\FAIcon('far', 'star'));
				}
			elseif ($value >= \round($i + 0.8, 1))
				{
				$icon = new \PHPFUI\FAIcon('fas', 'star');
				$this->add($icon);
				}
			else
				{
				$icon = new \PHPFUI\FAIcon('far', 'star-half-stroke');
				$this->add($icon);
				}
			}
		}
	}
