<?php

namespace App\UI;

class SearchField extends \PHPFUI\InputGroup
	{
	public function __construct(string $name, string $value = '')
		{
		parent::__construct();
		$input = new \PHPFUI\Input\Text($name, '', $value);
		$this->addClass('searchInput');
		$inputId = $input->getId();
		$dollar = '$';
		$uri = new \Laminas\Uri\Uri($_SERVER['REQUEST_URI']);
		$parameters = $uri->getQueryAsArray();
		$url = '';

		if ($value)
			{
			$input->setAttribute('disabled');
			$icon = new \PHPFUI\IconBase('fa-times');
			$icon->setToolTip('Click to remove search criteria');
			$iconId = $icon->getId();
			unset($parameters[$name]);
			$uri->setQuery($parameters);
			$js = "window.location='{$uri}';";
			}
		else
			{
			$icon = new \PHPFUI\IconBase('fa-plus');
			$icon->setToolTip('Click to add search criteria');
			$iconId = $icon->getId();
			$url = "{$uri}";

			if (! \str_contains((string)$url, '?'))
				{
				$url .= '?';
				}
			else
				{
				$url .= '&';
				}
			$js = "var input={$dollar}('#{$inputId}').val();if(input.length)window.location='{$url}{$name}='+encodeURIComponent(input);";
			}
		$input->setAttribute('onkeypress', 'if(event.keyCode==13){' . \str_replace("'", '"', (string)$js) . '};return true;');
		$icon->addClass('fas');
		$this->addInput($input);
		$this->addLabel($icon)->setAttribute('onclick', \str_replace("'", '"', (string)$js));
		}
	}
