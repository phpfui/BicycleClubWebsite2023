<?php

namespace App\View\System;

class IMAPSettings
	{
	private readonly \App\Model\SettingsSaver $settingsSaver;

	public function __construct(private readonly \PHPFUI\Page $page)
		{
		$this->settingsSaver = new \App\Model\SettingsSaver();
		}

	public function edit() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$buttonGroup = new \App\UI\CancelButtonGroup($submit);
		$test = new \PHPFUI\Submit('Test', 'action');
		$test->addClass('warning');
		$buttonGroup->addButton($test);
		$defaults = new \PHPFUI\Submit('IONOS Defaults', 'action');
		$defaults->addClass('secondary');
		$defaults->setConfirm('Are you sure you want to reset to the IONOS defaults?');
		$buttonGroup->addButton($defaults);
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Email Processor Settings');
		$fieldSet->add('The website needs to read emails to the club. You must provide <b>IMAP credentials</b> for a global account that receives all emails going to your domain. The website will direct those to the correct people.');
		$server = $this->settingsSaver->generateField('IMAPServer', 'IMAP Server Name (leave blank to turn off)', 'text', false);
		$server->setToolTip('Get the server name from your ISP.  Generally domain:port enclosed in {}.');
		$fieldSet->add($server);
		$port = $this->settingsSaver->generateField('IMAPPort', 'IMAP Port Number', 'number', false);
		$port->setToolTip('Port number is generally 143	(default for no encryption), 993 (TLS/SSL encryption) or 465 (implicit SSL encryption)');
		$fieldSet->add($port);
		$encryption = $this->settingsSaver->generateField('IMAPEncryption', 'IMAP Encryption', 'text', false);
		$encryption->setToolTip('Generally ssl or tls, leave blank for none.');
		$fieldSet->add($encryption);
		$folder = $this->settingsSaver->generateField('IMAPFolder', 'IMAP Folder', 'text', false);
		$folder->setToolTip('Folder to read emails from (leave blank for none)');
		$fieldSet->add($folder);
		$mailbox = $this->settingsSaver->generateField('IMAPMailBox', 'IMAP Mail Box');
		$mailbox->setToolTip('This should be a catch all mailbox.');
		$fieldSet->add($mailbox);
		$fieldSet->add($this->settingsSaver->generateField('IMAPPassword', 'IMAP Mail Box Password', 'PasswordEye'));
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$this->settingsSaver->save($_POST);
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'Test':
					$imap = new \App\Model\IMAP();
					$errors = $imap->getErrors();

					if ($errors)
						{
						\App\Model\Session::setFlash('alert', $errors);
						}
					else
						{
						$messages = \count($imap);
						\App\Model\Session::setFlash('success', "IMAP Server appears to be correctly configured. There are {$messages} message to be read.");
						}

					break;

				case 'IONOS Defaults':
					$settingTable = new \App\Table\Setting();
					$settingTable->save('IMAPServer', 'imap.ionos.com');
					$settingTable->save('IMAPPort', '993');
					$settingTable->save('IMAPEncryption', 'ssl');
					$settingTable->save('IMAPMailBox', '*@' . $_SERVER['SERVER_NAME']);
					\App\Model\Session::setFlash('success', 'Reset to IONOS defaults');

					break;

				}
			$this->page->redirect();
			}
		else
			{
			$form->add($buttonGroup);
			}

		return $form;
		}
	}
