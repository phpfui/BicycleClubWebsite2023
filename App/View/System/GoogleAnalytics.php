<?php

namespace App\View\System;

class GoogleAnalytics
	{
	public function __construct(private readonly \PHPFUI\Page $page)
		{
		}

	public function editSettings() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$settingsSaver = new \App\Model\SettingsSaver();
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Google Analytics Settings');
		$link = new \PHPFUI\Link('https://analytics.google.com', 'Google Analytics');
		$fieldSet->add('<b>Google Analytics</b> is a free service that can help you track website usage. You will need to get the Tracking Code from ' . $link);
		$clubId = $settingsSaver->generateField('GoogleAnalyticsTrackingCode', 'Tracking Code (leave blank to turn off)', 'text', false);
		$fieldSet->add($clubId);
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$settingsSaver->save();
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add(new \App\UI\CancelButtonGroup($submit));
			}

		return $form;
		}
	}
