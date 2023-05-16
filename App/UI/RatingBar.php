<?php

namespace App\UI;

class RatingBar extends \PHPFUI\HTML5Element
	{
	public function __construct(\PHPFUI\Page $page, string $name, int $numberStars, int $value)
		{
		parent::__construct('div');
		$this->addClass('rate');

		for ($i = $numberStars; $i; --$i)
			{
			$input = new \PHPFUI\HTML5Element('input');
			$input->addAttribute('type', 'radio');
			$input->addAttribute('name', $name);
			$input->addAttribute('value', (string)$i);

			if ($value == $i)
				{
				$input->addAttribute('checked');
				}
			$label = new \PHPFUI\HTML5Element('label');
			$label->add($i . ' star' . ($i > 1 ? 's' : ''));
			$label->addAttribute('title', 'name');
			$label->addAttribute('for', $input->getId());
			$this->add($input);
			$this->add($label);
			}

		// compressed css
		$page->addCSS('.rate{float:left;height:5rem;padding:0 0.1rem;}.rate:not(:checked)>input{position:absolute;top:-9999px;}.rate:not(:checked)>label{float:right;width:1em;overflow:hidden;white-space:nowrap;cursor:pointer;font-size:3rem;color:gainsboro;// not selected}.rate:not(:checked)>label:before{content:"★ ";}.rate>input:checked~label{color:#ffc700;// previously selected, but not now}.rate:not(:checked)>label:hover,.rate:not(:checked)>label:hover~label{color:#deb217;// highlighted but not selected}.rate>input:checked+label:hover,.rate>input:checked+label:hover~label,.rate>input:checked~label:hover,.rate>input:checked~label:hover~label,.rate>label:hover~input:checked~label{color:#c59b08;// will be selected if clicked}');
		}
	}
