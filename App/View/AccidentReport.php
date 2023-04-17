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
			$settings->save(self::MAIL, \App\Tools\TextHelper::cleanUserHtml($_POST[self::MAIL]));
			$settings->save(self::FILE, $_POST[self::FILE] ?? '');
			$page->setResponse('Saved');
			}
		else
			{
			if (\App\Model\Session::checkCSRF() && isset($_POST['test']))
				{
				$settings->save(self::MAIL, \App\Tools\TextHelper::cleanUserHtml($_POST[self::MAIL]));
				$settings->save(self::FILE, $_POST[self::FILE]);
				\App\Model\AccidentReport::report(new \App\Record\Ride());
				$alert = new \App\UI\Alert('Test email sent. Check your email.');
				$alert->setFadeout($page);
				$form->add($alert);
				}
			$fieldSet = new \PHPFUI\FieldSet('Email to Leader Reporting an Crash');
			$textarea = new \PHPFUI\Input\TextArea(self::MAIL, '', $settings->value(self::MAIL));
			$textarea->htmlEditing($page, new \App\Model\TinyMCETextArea());
			$textarea->setRequired();
			$fieldSet->add($textarea);
			$form->add($fieldSet);
			$fieldSet = new \PHPFUI\FieldSet('File To Attach to email');
			$select = new \PHPFUI\Input\Select(self::FILE);
			$selectedFile = $settings->value(self::FILE);

			foreach (\glob(PUBLIC_ROOT . 'pdf/*.pdf') as $file)
				{
				$file = \basename((string)$file);
				$select->addOption($file, $file, $file == $selectedFile);
				}
			$fieldSet->add($select);
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
