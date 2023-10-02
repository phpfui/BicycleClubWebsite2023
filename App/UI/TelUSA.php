<?php

namespace App\UI;

class TelUSA extends \PHPFUI\Input\Tel
	{
	/**
	 * Construct a Tel input for USA phone numbers
	 *
	 * @param \PHPFUI\Page $page to add javascript
	 * @param string $name of the field
	 * @param string $label defaults to empty
	 * @param ?string $value defaults to empty
	 */
	public function __construct(\PHPFUI\Page $page, string $name, string $label = '', ?string $value = '')
		{
		parent::__construct($page, $name, $label, $value);
		$this->setDataMask($page, '(000) 000-0000');
		$telUSAValidator = new \App\UI\TelUSAValidator();
		$this->setValidator($telUSAValidator);
		$page->addAbideValidator($telUSAValidator);
		}
	}
