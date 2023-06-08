<?php

namespace App\View\Admin;

class SystemEmail implements \Stringable
	{
	private readonly \App\Table\SystemEmail $systemEmailTable;

	public function __construct(private readonly \PHPFUI\Page $page)
		{
		$this->systemEmailTable = new \App\Table\SystemEmail();
		}

	public function __toString() : string
		{
		$output = '';
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->systemEmailTable->updateFromTable($_POST);
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteEmail':

					$systemEmail = new \App\Record\SystemEmail((int)$_POST['systemEmailId']);
					$systemEmail->delete();
					$this->page->setResponse($_POST['systemEmailId']);

					break;


				case 'Add':

					$systemEmail = new \App\Record\SystemEmail();
					$systemEmail->setFrom($_POST);
					$systemEmail->insert();
					$this->page->redirect();

					break;

				default:

					$this->page->redirect();

				}
			}
		else
			{
			$this->systemEmailTable->addOrderBy('mailbox');
			$rowId = 'systemEmailId';
			$deleteEmail = new \PHPFUI\AJAX('deleteEmail', 'Permanently delete this email address?');
			$deleteEmail->addFunction('success', '$("#' . $rowId . '-"+data.response).css("background-color","red").hide("fast").remove();');
			$this->page->addJavaScript($deleteEmail->getPageJS());
			$table = new \PHPFUI\Table();
			$table->setRecordId($rowId);
			$table->addHeader('mailbox', 'Club email Address');
			$table->addHeader('email', 'Forward to email address');
			$table->addHeader('name', 'Name');
			$table->addHeader('delete', 'Del');

			foreach ($this->systemEmailTable->getRecordCursor() as $systemEmail)
				{
				$row = $systemEmail->toArray();
				$id = $row[$rowId];
				$mailbox = new \PHPFUI\Input\Text("mailbox[{$id}]", '', $systemEmail->mailbox);
				$hidden = new \PHPFUI\Input\Hidden("{$rowId}[{$id}]", $id);
				$row['mailbox'] = $mailbox . $hidden;
				$email = new \PHPFUI\Input\Email("email[{$id}]", '', $systemEmail->email);
				$row['email'] = $email;
				$name = new \PHPFUI\Input\Text("name[{$id}]", '', $systemEmail->name);
				$row['name'] = $name;
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $deleteEmail->execute([$rowId => $id]));
				$row['delete'] = $icon;
				$table->addRow($row);
				}
			$form->add($table);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$add = new \PHPFUI\Button('Add');
			$add->addClass('warning');

			if (\count($this->systemEmailTable))
				{
				$form->saveOnClick($add);
				$buttonGroup->addButton($submit);
				}
			$this->addEmailModal($add);
			$buttonGroup->addButton($add);
			$form->add($buttonGroup);
			$output = $form;
			}

		return (string)$output;
		}

	private function addEmailModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$settingTable = new \App\Table\Setting();
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Club Email');
		$mailbox = new \PHPFUI\Input\Text('mailbox', 'System email Address');
		$mailbox->setRequired()->setToolTip('This is the mailbox name, ie. whatever@' . $settingTable->value('domain'));
		$fieldSet->add($mailbox);
		$email = new \PHPFUI\Input\Email('email', 'Forwards to');
		$email->setRequired()->setToolTip('The email address that the club mailbox will be forwarded to');
		$fieldSet->add($email);
		$name = new \PHPFUI\Input\Text('name', 'Name');
		$name->setRequired()->setToolTip('The name or position of the person who owns this email address');
		$fieldSet->add($name);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
