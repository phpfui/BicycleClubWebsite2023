<?php

namespace App\View;

class AccidentReport
	{
	final public const FILE = 'accidefile';

	final public const MAIL = 'accidemail';

	public static function output(\App\View\Page $page) : string
		{
		$output = '';
		$settings = new \App\Table\Setting();
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($page, $submit);

		if ($form->isMyCallback())
			{
			$settings->saveHtml(self::MAIL, $_POST[self::MAIL]);
			$settings->save(self::FILE, $_POST[self::FILE] ?? '');
			$page->setResponse('Saved');
			}
		else
			{
			if (\App\Model\Session::checkCSRF() && isset($_POST['test']))
				{
				$settings->saveHtml(self::MAIL, $_POST[self::MAIL]);
				$settings->save(self::FILE, $_POST[self::FILE]);
				\App\Model\AccidentReport::report(new \App\Record\Ride());
				$alert = new \App\UI\Alert('Test email sent. Check your email.');
				$alert->setFadeout($page);
				$form->add($alert);
				}
			$fieldSet = new \PHPFUI\FieldSet('Email to Ride Leader Reporting an Crash');
			$textarea = new \App\UI\TextAreaImage(self::MAIL, '', $settings->value(self::MAIL));
			$textarea->htmlEditing($page, new \App\Model\TinyMCETextArea(new \App\Record\Setting()->getLength('value')));
			$textarea->setRequired();
			$fieldSet->add($textarea);
			$form->add($fieldSet);
			$fieldSet = new \PHPFUI\FieldSet('File To Attach to email');
			$fieldSet->add(new \App\UI\PublicFilePicker(self::FILE));
			$form->add($fieldSet);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$test = new \PHPFUI\Submit('Test Email', 'test');
			$test->addClass('warning');
			$buttonGroup->addButton($test);
			$form->add($buttonGroup);
			$output = $form;
			}

		return $output;
		}
	}
