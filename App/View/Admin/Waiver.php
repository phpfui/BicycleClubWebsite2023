<?php

namespace App\View\Admin;

class Waiver implements \Stringable
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		if (! empty($_POST['agreedToWaiver']) && \App\Model\Session::checkCSRF())
			{
			$memberModel = new \App\Model\Member();
			$memberModel->signWaiver(\App\Model\Session::signedInMemberRecord());
			}
		}

	public function __toString() : string
		{
		$submit = new \PHPFUI\Submit('Agree and Continue');
		$submit->setDisabled();
		$form = new \PHPFUI\Form($this->page);
		$settings = new \App\Table\Setting();
		$club = $settings->value('clubName');
		$form->add(new \PHPFUI\Header("{$club} Waiver"));
		$form->add($settings->value('WaiverHeader'));

		$fieldSet = new \PHPFUI\FieldSet('Please acknowledge by checking the box below');
		$waiver = $settings->value('WaiverText');
		$fieldSet->add($waiver);
		$form->add($fieldSet);
		$cb = new \PHPFUI\Input\CheckBox('agreedToWaiver', 'I agree to the above terms', 1);
		$cb->setChecked(false);
		$elementId = $submit->getId();
		$dollar = '$';
		$cb->setAttribute('onclick', "{$dollar}(\"#{$elementId}\").toggleClass(\"disabled\")");
		$form->add($cb);
		$form->add($submit);

		return $form;
		}

	public function edit() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$settings = new \App\Table\Setting();
		$waiverHeaderField = 'WaiverHeader';
		$waiverTextField = 'WaiverText';
		$nonMemberWaiverTextField = 'NonMemberWaiverText';
		$minorWaiverTextField = 'MinorWaiverText';

		if ($form->isMyCallback())
			{
			$settings->saveHtml($waiverHeaderField, $_POST[$waiverHeaderField]);
			$settings->saveHtml($waiverTextField, $_POST[$waiverTextField]);
			$settings->saveHtml($nonMemberWaiverTextField, $_POST[$nonMemberWaiverTextField]);
			$settings->saveHtml($minorWaiverTextField, $_POST[$minorWaiverTextField]);
			$this->page->setResponse('Saved');
			}
		else
			{
			$waiverHeader = $settings->value($waiverHeaderField);
			$fieldset = new \PHPFUI\FieldSet('Waiver Acceptance Page Header');
			$textArea = new \PHPFUI\Input\TextArea($waiverHeaderField, 'Text displayed on page, but not in Waiver', $waiverHeader);
			$textArea->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			$fieldset->add($textArea);
			$form->add($fieldset);

			$waiver = $settings->value($waiverTextField);
			$fieldset = new \PHPFUI\FieldSet('Waiver Text');
			$textArea = new \PHPFUI\Input\TextArea($waiverTextField, 'This text will be shown to all new members and existing members each year', $waiver);
			$textArea->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			$fieldset->add($textArea);
			$form->add($fieldset);

			$waiver = $settings->value($nonMemberWaiverTextField);
			$fieldset = new \PHPFUI\FieldSet('Non Member Waiver Text');
			$textArea = new \PHPFUI\Input\TextArea($nonMemberWaiverTextField, 'This text will be used for non-members', $waiver);
			$textArea->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			$fieldset->add($textArea);
			$form->add($fieldset);

			$waiver = $settings->value($minorWaiverTextField);
			$fieldset = new \PHPFUI\FieldSet('Minor Waiver Text');
			$textArea = new \PHPFUI\Input\TextArea($minorWaiverTextField, 'Minor release waiver text. Printed on the minor release form.', $waiver);
			$textArea->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			$fieldset->add($textArea);
			$form->add($fieldset);

			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$print = new \PHPFUI\Button('Print All Waivers', '/Admin/downloadWaivers');
			$print->addClass('success');
			$buttonGroup->addButton($print);
			$reset = new \PHPFUI\Button('Reset All Signed Waivers', '/Admin/resetWaivers');
			$reset->setConfirm('Are you sure you want to reset all signed waivers? Every member will have to resign the waiver. Previously signed waivers will still be downloadable.');
			$reset->addClass('alert');
			$buttonGroup->addButton($reset);

			$form->add($buttonGroup);
			}

		return $form;
		}
	}
