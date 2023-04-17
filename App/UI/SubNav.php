<?php

namespace App\UI;

/**
 * SubNav Foundation element
 */
class SubNav extends \PHPFUI\Menu
	{
	/**
	 * Construct a SubNav
	 *
	 * @param string $title optional title for the SubNav
	 */
	public function __construct(string $title = '')
		{
		parent::__construct();

		if ($title)
			{
			$this->addMenuItem(new \PHPFUI\MenuItem($title));
			}
		}

	/**
	 * 	 * Add a tab to the SubNav
	 * 	 *
	 *
	 * @param string $label displayed to user
	 * @param bool $active defaults to false
	 *
	 */
	public function addTab(string $link, string $label, bool $active = false) : static
		{
		$item = new \PHPFUI\MenuItem($label, $link);
		$item->setActive($active);

		$this->addMenuItem($item);

		return $this;
		}
	}
