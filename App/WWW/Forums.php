<?php

namespace App\WWW;

class Forums extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Forum $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Forum($this->page);
		}

	public function attachment(\App\Record\Forum $forum = new \App\Record\Forum(), \App\Record\ForumAttachment $forumAttachment = new \App\Record\ForumAttachment()) : void
		{
		if ($forum->loaded())
			{
			if ($this->page->isAuthorized($forum->name))
				{
				$model = new \App\Model\Forum();
				$error = $model->download($forumAttachment);

				if ($error)
					{
					$this->page->addPageContent("<h3>{$error}</h3>");
					}

				return;
				}
			}
		$this->page->addPageContent(new \PHPFUI\Header('Attachment not found'));
		}

	public function attachments(\App\Record\Forum $forum = new \App\Record\Forum(), \App\Record\ForumMessage $forumMessage = new \App\Record\ForumMessage()) : void
		{
		if ($forum->loaded())
			{
			if ($this->page->isAuthorized($forum->name))
				{
				if ($this->page->addHeader('Attachments'))
					{
					$this->page->addPageContent($this->view->attachments($forumMessage->ForumAttachmentChildren));
					}
				}
			else
				{
				$this->page->addHeader($forum->name);
				}
			}
		elseif ($this->page->addHeader('Member Forums'))
			{
			$this->page->addPageContent($this->view->memberForums());
			}
		}

	public function edit(int $forumId = 0) : void
		{
		$header = ($forumId ? 'Edit' : 'Add') . ' Forum';
		$forum = new \App\Record\Forum($forumId);

		if (! $forumId || $forum->loaded())
			{
			$moderator = $this->page->isAuthorized($forum->name . ' Edit');

			if ($moderator || $this->page->addHeader($header))
				{
				if ($moderator)
					{
					$this->page->addHeader($header, '', true);
					}
				$this->page->addPageContent($this->view->edit($forum));
				}
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\Header('Forum not found'));
			}
		}

	public function editMessage(\App\Record\ForumMessage $message = new \App\Record\ForumMessage()) : void
		{
		if ($message->loaded())
			{
			$moderator = $this->page->isAuthorized($message->forum->name . ' Edit Message');
			$permission = 'Edit Forum Message';

			if ($moderator || $this->page->addHeader($permission))
				{
				if ($moderator)
					{
					$this->page->addPageContent(new \PHPFUI\Header($permission));
					}
				$this->page->addPageContent($this->view->editMessage($message));
				}
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Message not found'));
			}
		}

	public function history(\App\Record\Forum $forum = new \App\Record\Forum()) : void
		{
		if ($forum->loaded())
			{
			if ($this->page->addHeader($forum->name))
				{
				$forumMessageTable = new \App\Table\ForumMessage();
				$forumMessageTable->setWhere(new \PHPFUI\ORM\Condition('forumId', $forum->forumId));
				$this->page->addPageContent($this->view->history($forumMessageTable));
				}
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\Header('Forum not found'));
			}
		}

	public function home(\App\Record\Forum $forum = new \App\Record\Forum()) : void
		{
		if ($forum->loaded())
			{
			if ($this->page->addHeader($forum->name))
				{
				$this->page->addPageContent($this->view->home($forum, isset($_GET['subscribe'])));
				}
			}
		elseif ($this->page->addHeader('Member Forums'))
			{
			$this->page->addPageContent($this->view->memberForums());
			}
		}

	public function manage() : void
		{
		if ($this->page->addHeader('Manage Forums'))
			{
			$forumTable = new \App\Table\Forum();
			$forumTable->addOrderBy('name');
			$this->page->addPageContent($this->view->listForums($forumTable->getRecordCursor()));
			}
		}

	public function members(\App\Record\Forum $forum = new \App\Record\Forum()) : void
		{
		$header = 'Forum Members';

		if ($forum->loaded())
			{
			$moderator = $this->page->isAuthorized($forum->name . ' Members');

			if ($moderator || $this->page->addHeader($header))
				{
				if ($moderator)
					{
					$this->page->addHeader($header, '', true);
					}
				$forumMemberTable = new \App\Table\ForumMember();
				$forumMemberTable->setMembersQuery($forum);
				$this->page->addPageContent($this->view->listMembers($forum, $forumMemberTable));
				}
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\Header('Forum not found'));
			}
		}

	public function my() : void
		{
		if ($this->page->addHeader('My Forums'))
			{
			$forumTable = new \App\Table\Forum();
			$forumTable->addOrderBy('name');

			$this->page->addPageContent($this->view->myForums($forumTable->getRecordCursor()));
			}
		}

	public function post(\App\Record\Forum $forum = new \App\Record\Forum(), \App\Record\ForumMessage $message = new \App\Record\ForumMessage()) : void
		{
		if ($forum->loaded())
			{
			if ($this->page->addHeader($forum->name))
				{
				if ($message->loaded())
					{
					$this->page->addPageContent($this->view->getNavBar($message));
					$this->page->addPageContent($this->view->getPost($message));
					$this->page->addPageContent($this->view->getNavBar($message));
					}
				else
					{
					$this->page->addPageContent(new \PHPFUI\SubHeader('Message not found'));
					}
				}
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\Header('Forum not found'));
			}
		}

	public function reply(\App\Record\Forum $forum = new \App\Record\Forum(), \App\Record\ForumMessage $message = new \App\Record\ForumMessage()) : void
		{
		if ($forum->loaded() && $message->loaded())
			{
			if ($this->page->isAuthorized($forum->name))
				{
				if ($this->page->addHeader('Reply'))
					{
					$this->page->addPageContent($this->view->reply($forum, $message));
					}
				}
			else
				{
				$this->page->addHeader($forum->name);
				}
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\Header('Forum not found'));
			}
		}
	}
