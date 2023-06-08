<?php

namespace App\View\System;

class SlackSettings
	{
	private readonly \App\Model\Errors $model;

	public function __construct(private readonly \PHPFUI\Page $page)
		{
		$this->model = new \App\Model\Errors();
		}

	public function edit() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Slack Settings');

		$link = new \PHPFUI\Link('https://slack.com/help/articles/115005265063-Incoming-webhooks-for-Slack', 'Slack webhook');
		$fieldSet->add("Website errors can be posted to a <b>Slack</b> webhook. Set up a free {$link} and add it here.");
		$webhook = new \PHPFUI\Input\Text('SlackErrorWebhook', 'Slack Webhook (leave blank for no error reporting)', $this->model->getSlackUrl());
		$fieldSet->add($webhook);
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$this->model->setSlackUrl($_POST['SlackErrorWebhook']);
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add(new \App\UI\CancelButtonGroup($submit));
			}

		return $form;
		}
	}
