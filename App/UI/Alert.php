<?php

namespace App\UI;

/**
 * Simple wrapper for Alerts
 */
class Alert extends \PHPFUI\Callout
	{
	private ?bool $close = null;

	/**
	 * Constuct an Alert
	 *
	 * @param string $text of the alert
	 */
	public function __construct(string $text)
		{
		parent::__construct();
		$this->add($text);
		}

	/**
	 * 	 * Enable a close button
	 *
	 */
	public function setClose(bool $close = true) : static
		{
		$this->close = $close;

		return $this;
		}

	/**
	 * 	 * Set a fade out timout.
	 * 	 *
	 *
	 * @param \PHPFUI\Page $page since Alert requires JS
	 * @param int $timeout in thousands of a second, this is when it
	 *                    will start fading out, defaults to 3 seconds
	 * @param int $duration in thousands of a second, default to 1
	 *                    second
	 *
	 */
	public function setFadeout(\PHPFUI\Page $page, int $timeout = 3000, int $duration = 1000) : static
		{
		$page->addJavaScript("$('#{$this->getId()}').delay({$timeout}).fadeOut({$duration});");

		return $this;
		}

	protected function getStart() : string
		{
		if ($this->close)
			{
			$close = new \PHPFUI\CloseButton($this);
			$this->add($close);
			}

		return parent::getStart();
		}
	}
