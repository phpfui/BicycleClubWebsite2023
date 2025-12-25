<?php

namespace App\UI;

class Captcha extends \PHPFUI\Container
	{
	private ?\PHPFUI\ReCAPTCHA $captcha = null;

	private bool $local = false;

	private ?\PHPFUI\MathCaptcha $mathCaptcha = null;

	public function __construct(private \App\View\Page $page, private bool $active = true)
		{
		$this->mathCaptcha = new \PHPFUI\MathCaptcha($this->page);

		if ($this->active)
			{
			$fieldSet = new \PHPFUI\FieldSet('Please prove you are a human');

			$_SERVER['SERVER_ADDR'] ??= '::1';

			if ('127.0.0.1' != $_SERVER['SERVER_ADDR'] && '::1' != $_SERVER['SERVER_ADDR'])
				{
				$settingTable = new \App\Table\Setting();
				$this->captcha = new \PHPFUI\ReCAPTCHA($this->page, $settingTable->value('ReCAPTCHAPublicKey'), $settingTable->value('ReCAPTCHAPrivateKey'));
				$fieldSet->add($this->captcha);
				}
			else
				{
				$this->local = true;
				}
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

		return ($this->local || $this->captcha->isValid()) && $this->mathCaptcha->isValid();
		}
	}
