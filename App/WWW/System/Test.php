<?php

namespace App\WWW\System;

class Test extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\Setting $settingTable;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->settingTable = new \App\Table\Setting();
		}

	public function captchaMath() : void
		{
		$this->page->addHeader('Math Captcha Test');
		$captcha = new \PHPFUI\MathCaptcha($this->page);
		$this->page->addPageContent($this->getForm($captcha));
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
		$this->page->addHeader('Emoji Test');
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Enter Emojis and press Send');
		$fieldSet->add(new \PHPFUI\Input\Text('emojiText', 'Normal Text Box', $this->page->value('emojiText')));
		$fieldSet->add(new \PHPFUI\Input\TextArea('emojiTextArea', 'Normal Text Area', $this->page->value('emojiTextArea')));
		$html = new \App\UI\TextAreaImage('emojiHtmlEditor', 'HTML Editor Area', $this->page->value('emojiHtmlEditor'));
		$html->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$fieldSet->add($html);

		$form->add($fieldSet);
		$form->add(new \PHPFUI\Submit('Send'));
		$this->page->addPageContent($form);
		}

	public function flash() : void
		{
		if ($this->page->addHeader('Flash Test'))
			{
			$form = new \PHPFUI\Form($this->page);

			if (! empty($_POST))
				{
				\App\Model\Session::setFlash($_POST['flashType'], $_POST['flashText'] ?? 'Test message');
				$this->page->redirect();

				return;
				}
			$fieldSet = new \PHPFUI\FieldSet('Flash Test');
			$flashType = new \PHPFUI\Input\Select('flashType', 'Flash Type');

			foreach (['success', 'primary', 'secondary', 'warning', 'alert'] as $type)
				{
				$flashType->addOption($type);
				}
			$fieldSet->add($flashType);

			$fieldSet->add(new \PHPFUI\Input\Text('flashText', 'What do you want to flash', $_POST['flashText'] ?? '')->setRequired()); // @phpstan-ignore-line
			$form->add($fieldSet);

			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton(new \PHPFUI\Submit('Test'));
			$backButon = new \PHPFUI\Button('Back', '/System');
			$backButon->addClass('hollow')->addClass('secondary');
			$buttonGroup->addButton($backButon);
			$form->add($buttonGroup);
			$this->page->addPageContent("{$form}");
			}
		}

	public function inputNormal() : void
		{
		if ($this->page->addHeader('Input Test'))
			{
			$form = new \PHPFUI\Form($this->page);

			if (! empty($_POST))
				{
				$debug = new \PHPFUI\Debug($_POST);
				$callout = new \PHPFUI\Callout('info');
				$callout->add($debug);
				$form->add($callout);
				}
			$fieldSet = new \PHPFUI\FieldSet('Input Testing');
			$multiColumn = new \PHPFUI\MultiColumn();
			$multiColumn->add(new \PHPFUI\Input\Time($this->page, 'time', 'Time Android', $_POST['time'] ?? '12:30 PM'));
			$multiColumn->add(new \PHPFUI\Input\TimeDigital($this->page, 'timeDigital', 'Time Digital', $_POST['timeDigital'] ?? '4:45 PM'));
			$fieldSet->add($multiColumn);
			$fieldSet->add(new \PHPFUI\Input\Date($this->page, 'date', 'Date', $_POST['date'] ?? ''));
			$fieldSet->add(new \PHPFUI\Input\DateTime($this->page, 'string', 'Date Time', $_POST['datetime'] ?? ''));
			$fieldSet->add(new \PHPFUI\Input\Number('number', 'Number', (float)($_POST['number'] ?? '')));
			$fieldSet->add(new \PHPFUI\Input\TextArea('textarea', 'TextArea', $_POST['textarea'] ?? ''));

			$form->add($fieldSet);
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton(new \PHPFUI\Submit('Test'));
			$backButon = new \PHPFUI\Button('Back', '/System');
			$backButon->addClass('hollow')->addClass('secondary');
			$buttonGroup->addButton($backButon);
			$form->add($buttonGroup);
			$form->add(\PHPFUI\Link::phone('1-914-361-9059', 'Call Web Master'));
			$this->page->addPageContent("{$form}");
			}
		}

	public function inputTest() : void
		{
		if ($this->page->addHeader('HTML Input Test'))
			{
			$page = new \PHPFUI\VanillaPage();
			$form = new \PHPFUI\Form($this->page);

			if (! empty($_POST))
				{
				$debug = new \PHPFUI\Debug($_POST);
				$callout = new \PHPFUI\Callout('info');
				$callout->add($debug);
				$form->add($callout);
				}
			$fields = ['time', 'date', 'string', 'number'];
			$attributes = ['type', 'name', 'placeholder'];
			$fieldSet = new \PHPFUI\FieldSet('Input Testing');

			foreach ($fields as $field)
				{
				$input = new \PHPFUI\HTML5Element('input');

				foreach ($attributes as $attribute)
					{
					$input->addAttribute($attribute, $field);
					}

				if (isset($_POST[$field]))
					{
					$input->addAttribute('value', $_POST[$field]);
					}

				if ('time' == $field)
					{
					$input->addAttribute('step', (string)900);
					}
				$display = new \App\UI\Display(\ucwords($field), $input);
				$fieldSet->add($display);
				}

			$form->add($fieldSet);
			$form->add(new \PHPFUI\Submit('Test'));
			$form->add('<br>');
			$form->add(new \PHPFUI\Button('Back', '/System'));
			$form->add('<br>');
			$form->add(\PHPFUI\Link::phone('914-361-9059', 'Call Web Master'));
			$page->add($form);

			echo $page;

			exit;
			}
		}

	public function recaptcha() : void
		{
		$this->page->addHeader('Google ReCaptcha Test');
		$captcha = new \PHPFUI\ReCAPTCHA($this->page, $this->settingTable->value('ReCAPTCHAPublicKey'), $this->settingTable->value('ReCAPTCHAPrivateKey'));
		$this->page->addPageContent($this->getForm($captcha));
		}

	public function text() : void
		{
		if ($this->page->addHeader('Texting Test'))
			{
			$form = new \PHPFUI\Form($this->page);
			$member = \App\Model\Session::signedInMemberRecord();
			$form->add(new \App\UI\TelUSA($this->page, 'From', 'From Phone Number', $member->cellPhone));
			$form->add(new \PHPFUI\Input\TextArea('Body', 'Text Body'));
			$form->setAttribute('action', '/SMS/receive');
			$submit = new \PHPFUI\Submit('Text');
			$form->add($submit);
			$this->page->addPageContent($form);
			}
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
