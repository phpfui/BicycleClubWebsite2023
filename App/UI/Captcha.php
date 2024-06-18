<?php

namespace App\UI;

class Captcha extends \PHPFUI\Container
	{
	private ?\PHPFUI\ReCAPTCHA $captcha = null;

	private ?\PHPFUI\MathCaptcha $mathCaptcha = null;

	public function __construct(private \App\View\Page $page, private bool $active = true)
		{
		if ($this->active)
			{
			$fieldSet = new \PHPFUI\FieldSet('Please prove you are a human');

			$settingTable = new \App\Table\Setting();
			$this->captcha = new \PHPFUI\ReCAPTCHA($this->page, $settingTable->value('ReCAPTCHAPublicKey'), $settingTable->value('ReCAPTCHAPrivateKey'));
			$this->mathCaptcha = new \PHPFUI\MathCaptcha($this->page);
			$fieldSet->add($this->captcha);
			$fieldSet->add($this->mathCaptcha);
			$this->add($fieldSet);
			}
		}

	public function valid() : bool
		{
		if (! $this->active)
			{
			return true;
			}

		return $this->captcha->isValid() && $this->mathCaptcha->isValid();
		}
	}
