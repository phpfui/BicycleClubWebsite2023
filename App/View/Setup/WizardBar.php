<?php

namespace App\View\Setup;

class WizardBar extends \PHPFUI\Container
	{
	private readonly \PHPFUI\ButtonGroup $buttonGroup;

	private bool $nextAllowed = true;

	public function __construct(int $current, int $last)
		{
		$this->buttonGroup = new \PHPFUI\ButtonGroup();
		$bar = new \PHPFUI\ProgressBar($current ? 'Step ' . $current . ' of ' . $last : '');
		parent::__construct($bar, $this->buttonGroup);
		$bar->addClass('success');
		$bar->setPercent((int)\round((float)$current / (float)$last * 100.0));
		parent::__construct($bar, $this->buttonGroup);
		$this->buttonGroup->addClass('align-justify');
		$backButton = new \PHPFUI\Button('Back', '/Config/wizard/prev');

		if (! $current)
			{
			$backButton->setDisabled(true);
			}
		$this->buttonGroup->addButton($backButton);
		$this->add('<hr>');
		}

	public function __toString() : string
		{
		$next = new \PHPFUI\Button('Next', '/Config/wizard/next');
		$next->setDisabled(! $this->nextAllowed);
		$this->buttonGroup->addButton($next);

		// add in flash messages
		$callouts = ['success', 'primary', 'secondary', 'warning', 'alert'];

		foreach ($callouts as $calloutClass)
			{
			$message = \App\Model\Session::getFlash($calloutClass);

			if (! $message)
				{
				continue;
				}

			$callout = new \PHPFUI\Callout($calloutClass);
			$callout->addAttribute('data-closable');

			if (\is_array($message))
				{
				$ul = new \PHPFUI\UnorderedList();

				foreach ($message as $error)
					{
					$ul->addItem(new \PHPFUI\ListItem($error));
					}
				$callout->add($ul);
				}
			else
				{
				$callout->add($message);
				}
			$this->add($callout);
			}

		return parent::__toString();
		}

	/**
	 * Add a button to the group with optional class
	 */
	public function addButton(\PHPFUI\Button $button) : \PHPFUI\ButtonGroup
		{
		$this->buttonGroup->addButton($button);

		return $this->buttonGroup;
		}

	public function nextAllowed(bool $nextAllowed = true) : void
		{
		$this->nextAllowed = $nextAllowed;
		}
	}
