<?php

namespace App\UI;

class CancelButtonGroup extends \PHPFUI\ButtonGroup
	{
	private string $cancelUrl = '';

	public function __construct(\PHPFUI\Button ...$buttons)
		{
		parent::__construct();

		foreach ($buttons as $button)
			{
			$this->addButton($button);
			}
		}

	public function setCancelButtonURL(string $url) : static
		{
		$this->cancelUrl = $url;

		return $this;
		}

	protected function getStart() : string
		{
		if (! $this->cancelUrl)
			{
			try
				{
				$uri = new \Uri\Rfc3986\Uri((string)$_SERVER['REQUEST_URI']);
				$parts = \explode('/', $uri->getPath());
				$last = \array_pop($parts);

				// if last one was a number, then go another one up
				if ((int)$last > 0)
					{
					\array_pop($parts);
					}
				}
			catch (\Uri\InvalidUriException $e)
				{
				$parts = ["Invalid uri: {$_SERVER['REQUEST_URI']}"];
				}
			$url = \implode('/', $parts);
			}
		else
			{
			$url = $this->cancelUrl;
			}

		$cancelButton = new \PHPFUI\Button('Cancel', $url);
		$cancelButton->addClass('hollow')->addClass('alert');
		$this->addButton($cancelButton);

		return parent::getStart();
		}
	}
