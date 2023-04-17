<?php

namespace App\View\Admin;

class JournalQueue
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function getQueue() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$journalItemTable = new \App\Table\JournalItem();

		if (\count($journalItemTable))
			{
			$view = new \App\UI\ContinuousScrollTable($this->page, $journalItemTable);
			$record = $journalItemTable->getRecord();

			$record->addDisplayTransform('memberId', $this->getMember(...));

			$deleter = new \App\Model\DeleteRecord($this->page, $view, $journalItemTable, 'Permanently delete this email from the next journal email?');
			$view->addCustomColumn('del', $deleter->columnCallback(...));
			$view->addCustomColumn('edit', $this->getEditItemModal(...));

			$headers = [
				'memberId' => 'Sender',
				'title' => 'Title',
				'timeSent' => 'Sent At',
			];

			$view->setHeaders(\array_merge($headers, ['edit', 'del']));
			unset($headers['memberId']);
			$view->setSortableColumns(\array_keys($headers));
//			$view->setSearchColumns(\array_keys($headers));
			$container->add($view);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('Nothing in the queue'));
			}

		return $container;
		}

	private function getMember(int $memberId) : string
		{
		if (! $memberId)
			{
			return 'Web Master';
			}

		$member = new \App\Record\Member($memberId);

		return $member->fullName();
		}

	private function getEditItemModal(array $item) : \PHPFUI\FAIcon
		{
		$editIcon = new \PHPFUI\FAIcon('far', 'edit', '#');
		$modal = new \PHPFUI\Reveal($this->page, $editIcon);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('journalItemId', $item['journalItemId']));
		$form->add(new \PHPFUI\Input\Text('title', 'Email Title', $item['title']));
		$textArea = new \PHPFUI\Input\TextArea('body', 'Email Body', $item['body']);
		$textArea->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$form->add($textArea);
		$submit = new \PHPFUI\Submit('Save');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $editIcon;
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if ('Save' == ($_POST['submit'] ?? ''))
				{
				$journalItem = new \App\Record\JournalItem($_POST);
				$journalItem->update();
				$this->page->redirect();
				}
			}
		}
	}
