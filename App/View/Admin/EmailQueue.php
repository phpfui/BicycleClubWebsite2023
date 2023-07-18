<?php

namespace App\View\Admin;

class EmailQueue
	{
	private readonly \App\Table\MailPiece $mailPieceTable;

	private readonly \PHPFUI\AJAX $paused;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->mailPieceTable = new \App\Table\MailPiece();
		$this->paused = new \PHPFUI\AJAX('pauseItem');
		$this->paused->addFunction('success', '$("#"+data.iconId+"a").html(data.response);');
		$page->addJavaScript($this->paused->getPageJS());
		$this->processRequest();
		}

	public function getQueue() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$mailItemTable = new \App\Table\MailItem();

		if (\count($mailItemTable))
			{
			$view = new \App\UI\ContinuousScrollTable($this->page, $mailItemTable);
			$record = $mailItemTable->getRecord();

			$record->addDisplayTransform('memberId', $this->getMember(...));

			$deleter = new \App\Model\DeleteRecord($this->page, $view, $mailItemTable, 'Are you sure you want to permanently delete this email?');
			$view->addCustomColumn('del', $deleter->columnCallback(...));
			$view->addCustomColumn('edit', $this->getEditItemModal(...));
			$view->addCustomColumn('paused', $this->getPauseControl(...));
			$view->addCustomColumn('emails', $this->getCount(...));

			$headers = [
				'memberId' => 'Sender',
				'title',
				'emails' => 'Emails to send',
				'replyTo',
				'paused' => 'Paused',
			];

			$view->setSortableColumns(\array_keys($headers))->setHeaders(\array_merge($headers, ['edit', 'del']));
			unset($headers['memberId'], $headers['paused']);
			$view->setSearchColumns(\array_keys($headers));
			$container->add($view);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('Nothing in the queue'));
			}

		return $container;
		}

	public function getTimerCount(string $id) : int
		{
		$ids = \explode('-', $id);

		$this->mailPieceTable->setWhere(new \PHPFUI\ORM\Condition('mailItemId', $ids[1]));

		return $this->mailPieceTable->count();
		}

	/**
	 * @param array<string,string> $mailItem
	 */
	private function getCount(array $mailItem)
		{
		$this->mailPieceTable->setWhere(new \PHPFUI\ORM\Condition('mailItemId', $mailItem['mailItemId']));
		new \PHPFUI\TimedCellUpdate($this->page, 'emails-' . $mailItem['mailItemId'], $this->getTimerCount(...), 15);

		return $this->mailPieceTable->count();
		}

	/**
	 * @param array<string,string> $mailItem
	 */
	private function getEditItemModal(array $mailItem) : \PHPFUI\FAIcon
		{
		$editIcon = new \PHPFUI\FAIcon('far', 'edit', '#');
		$modal = new \PHPFUI\Reveal($this->page, $editIcon);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('mailItemId', $mailItem['mailItemId']));
		$form->add(new \PHPFUI\Input\Text('title', 'Email Title', $mailItem['title']));
		$textArea = new \PHPFUI\Input\TextArea('body', 'Email Body', $mailItem['body']);

		if ($mailItem['html'])
			{
			$textArea->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			}
		$form->add($textArea);
		$submit = new \PHPFUI\Submit('Save');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $editIcon;
		}

	private function getMember(?int $memberId) : string
		{
		if (! $memberId)
			{
			return 'Web Master';
			}

		$member = new \App\Record\Member($memberId);

		return $member->fullName();
		}

	/**
	 * @param array<string,string> $mailItem
	 */
	private function getPauseControl(array $mailItem) : \PHPFUI\FAIcon
		{
		$pause = new \PHPFUI\FAIcon('fas', $mailItem['paused'] ? 'play' : 'pause', '#');
		$iconId = $pause->getId();
		$pause->addAttribute('onclick', $this->paused->execute(['mailItemId' => $mailItem['mailItemId'], 'iconId' => '"' . $iconId . '"']));

		return $pause;
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				$mailItemId = $_POST['mailItemId'];

				switch ($_POST['action'])
					{
					case 'deleteItem':
						$mailItem = new \App\Record\MailItem();
						$mailItem->mailItemId = (int)$mailItemId;
						$mailItem->delete();
						$this->page->setResponse($mailItemId);

						break;

					case 'pauseItem':
						$mailItem = new \App\Record\MailItem((int)$mailItemId);
						$mailItem->paused = (int)! $mailItem->paused;
						$mailItem->update();
						$icon = new \PHPFUI\FAIcon('fas', $mailItem->paused ? 'play' : 'pause', '#');
						$icon->setId($_POST['iconId']);
						$icon->addAttribute('onclick', $this->paused->execute(['mailItemId' => $mailItemId,
							'iconId' => '"' . $_POST['iconId'] . '"', ]));
						$this->page->setRawResponse(\json_encode(['response' => "{$icon}", 'iconId' => $_POST['iconId']], JSON_THROW_ON_ERROR));

						break;
					}
				}
			elseif (isset($_POST['submit']))
				{
				if ('Save' == $_POST['submit'])
					{
					$mailItem = new \App\Record\MailItem();
					$mailItem->setFrom($_POST);
					$mailItem->update();
					$this->page->redirect();
					}
				}
			}
		}
	}
