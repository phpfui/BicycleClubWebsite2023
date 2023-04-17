<?php

namespace App\WWW;

class Test extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\Setting $settingTable;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->settingTable = new \App\Table\Setting();
		}

	public function emoji() : void
		{
		if ($_POST)
			{
			unset($_POST['csrf'], $_POST['submit']);

			\App\Model\Session::setFlash('success', '<pre>' . \print_r($_POST, true) . '</pre>');
			$settingTable = new \App\Table\Setting();

			foreach ($_POST as $name => $value)
				{
				$settingTable->save($name, $value);
				}
			$this->page->redirect();

			return;
			}
		$this->page->addHeader('Test Emojis');
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Enter Emojis and press Send');
		$fieldSet->add(new \PHPFUI\Input\Text('emojiText', 'Normal Text Box', $this->page->value('emojiText')));
		$fieldSet->add(new \PHPFUI\Input\TextArea('emojiTextArea', 'Normal Text Area', $this->page->value('emojiTextArea')));
		$html = new \PHPFUI\Input\TextArea('emojiHtmlEditor', 'HTML Editor Area', $this->page->value('emojiHtmlEditor'));
		$html->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$fieldSet->add($html);

		$form->add($fieldSet);
		$form->add(new \PHPFUI\Submit('Send'));
		$this->page->addPageContent($form);
		}

	public function recaptcha() : void
		{
		$this->page->addHeader('Google ReCaptcha with Library');
		$captcha = new \PHPFUI\ReCAPTCHA($this->page, $this->settingTable->value('ReCAPTCHAPublicKey'), $this->settingTable->value('ReCAPTCHAPrivateKey'));
		$this->page->addPageContent($this->getForm($captcha));
		}

//	public function recaptchaInvisible() : void
//		{
//		$this->page->addHeader('Google ReCaptcha V2 Invisible');
//		$this->page->addPageContent($this->getForm());
//		}

	public function recaptchaMath() : void
		{
		$this->page->addHeader('Simple Math Captcha');
		$captcha = new \PHPFUI\MathCaptcha($this->page);
		$this->page->addPageContent($this->getForm($captcha));
		}

	public function recaptchaOld() : void
		{
		$this->page->addHeader('Google ReCaptcha');
		$captcha = new \PHPFUI\ReCAPTCHA($this->page, $this->settingTable->value('ReCAPTCHAPublicKey'), $this->settingTable->value('ReCAPTCHAPrivateKey'));
		$this->page->addPageContent($this->getForm($captcha));
		}

	public function recaptchaThree() : void
		{
		$this->page->addHeader('Google ReCaptcha V3');
		$captcha = new \PHPFUI\ReCAPTCHA($this->page, $this->settingTable->value('ReCAPTCHAPublicKey'), $this->settingTable->value('ReCAPTCHAPrivateKey'));
		$this->page->addPageContent($this->getForm($captcha));
		}

	private function getForm(\PHPFUI\Interfaces\Captcha $captcha) : \PHPFUI\Form
		{
		$post = \App\Model\Session::getFlash('post');

		$form = new \PHPFUI\Form($this->page);

		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
			{
			\App\Model\Session::setFlash('post', $_POST);

			if ($captcha->isValid())
				{
				\App\Model\Session::setFlash('success', 'You are not a robot!');
				}
			else
				{
				\App\Model\Session::setFlash('alert', 'You appear to be a robot! Please confirm you are not.');
				}
			$this->page->redirect();

			return $form;
			}

		$fieldSet = new \PHPFUI\FieldSet('Contact Us');
		$title = new \PHPFUI\Input\Text('subject', 'Subject', $post['subject'] ?? '');
		$fieldSet->add($title);
		$message = new \PHPFUI\Input\TextArea('message', 'Message', $post['message'] ?? '');
		$fieldSet->add($message);
		$form->add($fieldSet);
		$form->add($captcha);
		$form->add(new \PHPFUI\Submit('Send!'));

		return $form;
		}
	}
