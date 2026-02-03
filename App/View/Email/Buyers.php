<?php

namespace App\View\Email;

class Buyers implements \Stringable
	{
	private string $emailText = 'Email All Buyers';

	private string $showText = 'Show Buyers';

	public function __construct(private readonly \App\View\Page $page)
		{
		$email = null;
		$invoiceModel = null;

		if (\App\Model\Session::checkCSRF())
			{
			\App\Model\Session::setFlash('post', $_POST);
			$message = $status = 'error';

			if ($_POST['submit'] == $this->emailText || $_POST['submit'] == $this->showText)
				{
				$member = \App\Model\Session::getSignedInMember();
				$name = \App\Tools\TextHelper::unhtmlentities($member['firstName'] . ' ' . $member['lastName']);
				$emailAddress = $member['email'];
				$phone = $member['phone'];
				$link = $page->value('homePage');
				$message = \App\Tools\TextHelper::cleanUserHtml($_POST['message']) . "<p>This email was sent from {$link} by {$name}\n{$emailAddress}\n{$phone}";

				$invoiceModel = new \App\Model\Invoice();
				$invoiceTable = $invoiceModel->getInvoiceTable($_POST);
				$cursor = $invoiceTable->getDataObjectCursor();

				if ($_POST['submit'] == $this->emailText)
					{
					foreach ($cursor as $buyer)
						{
						if (empty($buyer['email']))
							{
							continue;
							}

						$email = new \App\Tools\EMail();
						$email->setSubject($_POST['subject']);
						$email->setFromMember($member);
						$email->addToMember($buyer);
						$email->setHtml();
						$email->setBody($message);

						if (! empty($_POST['attach']))
							{
							$invoice = new \App\Record\Invoice($buyer->toArray());
							$pdf = $invoiceModel->generatePDF($invoice);
							$email->addAttachment($pdf->Output('', 'S'), $invoiceModel->getFileName($invoice));
							}
						$email->bulkSend();
						unset($email);
						}
					$message = 'Your email was sent to ' . \count($cursor) . ' buyers';
					}
				elseif ($_POST['submit'] == $this->showText)
					{
					$ul = new \PHPFUI\UnorderedList();

					foreach ($cursor as $buyer)
						{
						if (! empty($buyer['email']))
							{
							$ul->addItem(new \PHPFUI\ListItem(\PHPFUI\Link::email($buyer['email'], $buyer['firstName'] . ' ' . $buyer['lastName'])));
							}
						}
					$message = 'You would have emailed the following:<br>' . $ul;
					$status = 'success';
					}
				}
			\App\Model\Session::setFlash($status, $message);
			$this->page->redirect();
			}
		}

	public function __toString() : string
		{
		$form = new \PHPFUI\Form($this->page);
		$post = \App\Model\Session::getFlash('post');
		$storeView = new \App\View\Store($this->page);
		$storeView->getInvoiceRequest($form, false, 'Selection Criteria');
		$fieldSet = new \PHPFUI\FieldSet('Email');
		$subject = new \PHPFUI\Input\Text('subject', 'Subject', $post['subject'] ?? '');
		$subject->setRequired();
		$subject->addAttribute('placeholder', 'Email Subject');
		$fieldSet->add($subject);
		$message = new \App\UI\TextAreaImage('message', 'Message', $post['message'] ?? '');
		$message->addAttribute('placeholder', 'Message to buyer?');
		$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea(new \App\Record\MailItem()->getLength('body')));
		$message->setRequired();
		$fieldSet->add($message);
		$attachInvoice = new \PHPFUI\Input\CheckBoxBoolean('attach', 'Attach Invoice', $post['attach'] ?? false);
		$attachInvoice->setToolTip('If you check this, the buyer will be sent the matching invoice attached to the email');
		$fieldSet->add($attachInvoice);
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$emailButton = new \PHPFUI\Submit($this->emailText);
		$emailButton->addClass('warning');
		$emailButton->setConfirm('Email all buyers now?');
		$buttonGroup->addButton($emailButton);
		$showButton = new \PHPFUI\Submit($this->showText);
		$showButton->addClass('success');
		$buttonGroup->addButton($showButton);
		$form->add($buttonGroup);
		$output = $form;

		return (string)$output;
		}
	}
