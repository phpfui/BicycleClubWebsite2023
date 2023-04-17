<?php

namespace App\View\System;

class Redirects implements \Stringable
	{
	private readonly \App\Table\Redirect $redirectTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->redirectTable = new \App\Table\Redirect();
		}

	public function __toString() : string
		{
		$output = '';
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->redirectTable->updateFromTable($_POST);
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteRedirect':

					$redirect = new \App\Record\Redirect((int)$_POST['redirectId']);
					$redirect->delete();
					$this->page->setResponse($_POST['redirectId']);

					break;


				case 'Add':

					$redirect = new \App\Record\Redirect();
					$redirect->setFrom($_POST);
					$redirect->insert();
					$this->page->redirect();

					break;


				default:

					$this->page->redirect();

				}
			}
		else
			{
			$this->redirectTable->addOrderBy('originalUrl');
			$rowId = 'redirectId';
			$deleteRedirect = new \PHPFUI\AJAX('deleteRedirect', 'Permanently delete this redirect?');
			$deleteRedirect->addFunction('success', '$("#' . $rowId . '-"+data.response).css("background-color","red").hide("fast").remove();');
			$this->page->addJavaScript($deleteRedirect->getPageJS());
			$table = new \PHPFUI\Table();
			$table->setAttribute('width', '100%');
			$table->setRecordId($rowId);
			$table->addHeader('originalUrl', 'Original URL');
			$table->addHeader('redirectUrl', 'Redirected URL');
			$table->addHeader('delete', 'Del');
			$table->setWidths(['originalUrl' => '45%', 'redirectUrl' => '45%', 'delete' => '10%']);

			foreach ($this->redirectTable->getRecordCursor() as $redirect)
				{
				$row = $redirect->toArray();
				$id = $row[$rowId];
				$originalUrl = new \PHPFUI\Input\Text("originalUrl[{$id}]", '', $redirect->originalUrl);
				$hidden = new \PHPFUI\Input\Hidden("{$rowId}[{$id}]", $id);
				$row['originalUrl'] = $originalUrl . $hidden;
				$redirected = new \PHPFUI\Input\Text("redirectUrl[{$id}]", '', $redirect->redirectUrl);
				$row['redirectUrl'] = $redirected;
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $deleteRedirect->execute([$rowId => $id]));
				$row['delete'] = $icon;
				$table->addRow($row);
				}
			$form->add($table);

			$add = new \PHPFUI\Button('Add');
			$buttonGroup = new \App\UI\CancelButtonGroup();

			if (\count($this->redirectTable))
				{
				$form->saveOnClick($add);
				$buttonGroup->addButton($submit);
				}
			$this->addRedirectModal($add);
			$buttonGroup->addButton($add);
			$form->add($buttonGroup);
			$output = $form;
			}

		return (string)$output;
		}

	private function addRedirectModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Add Redirect');
		$original = new \PHPFUI\Input\Text('originalUrl', 'Original URL');
		$original->setRequired()->setToolTip('This is URL that used to exist, but no longer does. Assumed root relative (do not start with /)');
		$fieldSet->add($original);
		$redirect = new \PHPFUI\Input\Text('redirectUrl', 'Redirected URL');
		$redirect->setRequired()->setToolTip('The new URL that replaces the original ULR. Needs to exist. Assumed root relative (do not start with /)');
		$fieldSet->add($redirect);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
