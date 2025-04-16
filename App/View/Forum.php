<?php

namespace App\View;

class Forum
	{
	private readonly \App\Table\ForumMessage $forumMessageTable;

	private readonly \App\Model\Forum $model;

	private string $site;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->model = new \App\Model\Forum();
		$this->forumMessageTable = new \App\Table\ForumMessage();
		$settingTable = new \App\Table\Setting();

		if (\str_contains($_SERVER['SERVER_NAME'] ?? '', '.'))
			{
			$this->site = $settingTable->value('homePage');
			}
		else
			{
			$this->site = 'http://' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
			}
		$this->processRequest();
		}

	public function attachments(\PHPFUI\ORM\RecordCursor $attachments) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! \count($attachments))
			{
			$container->add(new \PHPFUI\SubHeader('No attachments found'));
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('Click the attachment you want'));
			$ol = new \PHPFUI\OrderedList();

			foreach ($attachments as $attachment)
				{
				$ol->addItem(new \PHPFUI\ListItem("<a href='{$this->site}/Forums/attachment/{$attachment->forumId}/{$attachment->forumAttachmentId}'>{$attachment->fileName}</a>"));
				}
			$container->add($ol);
			}

		return $container;
		}

	public function edit(\App\Record\Forum $forum) : \App\UI\ErrorFormSaver
		{
		if ($forum->forumId)
			{
			$submit = new \PHPFUI\Submit();
			$redirectOnAdd = '';
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add');
			$redirectOnAdd = '/Forums/manage';
			}

		$form = new \App\UI\ErrorFormSaver($this->page, $forum, $submit);

		if ($form->save($redirectOnAdd))
			{
			return $form;
			}

		$form->add(new \PHPFUI\Input\Hidden('forumId', (string)$forum->forumId));
		$fieldSet = new \PHPFUI\FieldSet('Forum Settings');
		$forumName = new \PHPFUI\Input\Text('name', 'Public Name', $forum->name);
		$forumName->setToolTip('This is the public name of the forum and will be shown to members and in emails.');
		$forumName->setRequired();
		$email = new \PHPFUI\Input\Text('email', 'email Address', $forum->email);
		$email->setToolTip('This is the club email address of the forum. It will be the part before @' . $this->page->value('domain'));
		$fieldSet->add(new \PHPFUI\MultiColumn($forumName, $email));

		$closed = new \PHPFUI\Input\CheckBoxBoolean('closed', 'Closed for new comments', (bool)$forum->closed);
		$closed->setToolTip('Close this forum for new comments. A forum must be closed before it can be deleted.');

		$attachments = new \PHPFUI\Input\CheckBoxBoolean('attachments', 'Allow attachments', (bool)$forum->attachments);
		$attachments->setToolTip('Allow attachments to be posted and retreived from the group. This could present security issues.');

		$formerMembers = new \PHPFUI\Input\CheckBoxBoolean('formerMembers', 'Allow Former Members', (bool)$forum->formerMembers);
		$formerMembers->setToolTip('Allow former members to participate.');

		$fieldSet->add(new \PHPFUI\MultiColumn($closed, $attachments, $formerMembers));

		$description = new \App\UI\TextAreaImage('description', 'Forum Description', $forum->description);
		$description->htmlEditing($this->page, new \App\Model\TinyMCETextArea($forum->getLength('description')));
		$description->setRequired();
		$description->setToolTip('Description of the group displayed on the forum\'s home page.');
		$fieldSet->add($description);
		$whiteList = \str_replace(',', "\n", $forum->whiteList ?? '');
		$list = \explode("\n", $whiteList);
		$whiteListText = \implode("\n", $list);
		$whiteList = new \PHPFUI\Input\TextArea('whiteList', 'White Listed email Addresses, comma or new line separated.', $whiteListText);
		$whiteList->setToolTip('Add any email addresses that can post to the forum, but will not receive emails.  This is useful for announcement type members who will post but not view response emails.');
		$fieldSet->add($whiteList);
		$form->add($fieldSet);
		$form->add($submit);

		return $form;
		}

	public function editMessage(\App\Record\ForumMessage $message) : \App\UI\ErrorFormSaver
		{
		$submit = new \PHPFUI\Submit('Save Post');
		$form = new \App\UI\ErrorFormSaver($this->page, $message, $submit);

		if ($form->save())
			{
			return $form;
			}
		$form->add(new \PHPFUI\Input\Hidden('forumMessageId', (string)$message->forumMessageId));
		$fieldSet = new \PHPFUI\FieldSet('Message Information');

		if ($message->lastEditorId)
			{
			$fieldSet->add(new \App\UI\Display('Last Edited:', $message->lastEdited));
			$fieldSet->add(new \App\UI\Display('Last Editor:', $message->lastEditor->fullName()));
			}

		$topic = new \PHPFUI\Input\Text('title', 'Subject', $message->title);
		$topic->setRequired();
		$fieldSet->add($topic);
		$textMessage = new \PHPFUI\Input\TextArea('textMessage', 'Text Message', $message->textMessage);
		$textMessage->setRequired();
		$fieldSet->add($textMessage);
		$htmlMessage = new \App\UI\TextAreaImage('htmlMessage', 'HTML Message', $message->htmlMessage);
		$htmlMessage->htmlEditing($this->page, new \App\Model\TinyMCETextArea($message->getLength('htmlMessage')));
		$htmlMessage->setRequired();
		$fieldSet->add($htmlMessage);
		$form->add($fieldSet);
		$form->add($submit);

		return $form;
		}

	public function getAttachmentButton(\App\Record\ForumMessage $message) : ?\PHPFUI\FAIcon
		{
		$attachments = $message->ForumAttachmentChildren;
		$numberAttachments = \count($attachments);
		$attachmentIcon = null;

		if ($numberAttachments)
			{
			$text = $numberAttachments . ' Attachment';

			if ($numberAttachments > 1)
				{
				$text .= 's';
				}

			$attachmentIcon = new \PHPFUI\FAIcon('fas', 'paperclip', '#');
			$attachmentIcon->setToolTip($text);
			$modal = new \PHPFUI\Reveal($this->page, $attachmentIcon);
			$modal->addClass('medium');
			$modal->add($this->attachments($attachments));
			$modal->add($modal->getCloseButton('Close'));
			}

		return $attachmentIcon;
		}

	public function getNavBar(\App\Record\ForumMessage $message, string $deleteId = '', bool $arrows = true) : \PHPFUI\MultiColumn
		{
		$previous = $next = null;

		if ($arrows)
			{
			$previous = $this->forumMessageTable->getPreviousMessage($message);
			$next = $this->forumMessageTable->getNextMessage($message);
			}

		$multiColumn = new \PHPFUI\MultiColumn();

		if ($arrows && ($previous->forumMessageId ?? false))
			{
			$arrowLeft = new \PHPFUI\FAIcon('fas', 'arrow-left', $this->getPostLink($previous));
			$arrowLeft->setToolTip('Previous Message');
			$multiColumn->add($arrowLeft);
			}
		$forum = $message->forum;
		$multiColumn->add($this->getReplyButton($forum, $message->title ?? ''));
		$multiColumn->add($this->getReplyToPosterButton($message));
		$attachmentButton = $this->getAttachmentButton($message);

		if ($attachmentButton)
			{
			$multiColumn->add($attachmentButton);
			}

		if ($this->page->isAuthorized($forum->name . ' Edit Message') || $this->page->isAuthorized('Edit Forum Message'))
			{
			$multiColumn->add(new \PHPFUI\FAIcon('far', 'edit', $this->site . '/Forums/editMessage/' . $message->forumMessageId));
			}

		if ($deleteId && $this->page->isAuthorized('Delete Forum Message'))
			{
			$delete = new \PHPFUI\AJAX('deleteMessage', 'Permanently delete this message?');
			$delete->addFunction('success', '$("#"+data.response).css("background-color","red").hide("fast").remove()');
			$this->page->addJavaScript($delete->getPageJS());
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $delete->execute(['forumMessageId' => $message->forumMessageId, 'deleteId' => '"' . $deleteId . '"']));
			$multiColumn->add($icon);
			}

		$page = (int)($_GET['p'] ?? 0);
		$parts = \explode('/', $this->page->getBaseUrl());
		$forumId = (int)$parts[3];
		$link = "/Forums/history/{$forumId}?p={$page}";
		$arrowUp = new \PHPFUI\FAIcon('fas', 'arrow-up', $link);
		$multiColumn->add($arrowUp);

		while (\count($multiColumn) < 5)
			{
			$multiColumn->add('&nbsp;');
			}

		if ($arrows && $next->loaded())
			{
			$arrowRight = new \PHPFUI\FAIcon('fas', 'arrow-right', $this->getPostLink($next));
			$arrowRight->setToolTip('Next Message');
			$multiColumn->add($arrowRight);
			}

		while (\count($multiColumn) < 6)
			{
			$multiColumn->add('&nbsp;');
			}

		return $multiColumn;
		}

	public function getPost(\App\Record\ForumMessage $message, int $abbrevPost = 0, bool $relativeTimes = true) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$member = $message->member;
		$postLink = $this->site . $this->getPostLink($message);
		$container->add(new \PHPFUI\SubHeader("<a href='{$postLink}'>{$message->title}</a>"));

		if ($relativeTimes)
			{
			$time = 'about ' . \App\Tools\TimeHelper::relativeFormat($message->posted);
			}
		else
			{
			$time = 'at ' . \date('D M j g:i a', \strtotime($message->posted));
			}

		$from = "<p>From: <strong>{$member->fullName()}</strong> {$time}</p><p>";
		$container->add($from);

		if ($abbrevPost)
			{
			$text = $message->textMessage;
			$abbrevText = \App\Tools\TextHelper::abbreviate($text, $abbrevPost);
			$container->add(\str_replace("\n", '<br>', $abbrevText));

			if ($abbrevText != $text)
				{
				$container->add(" (<a href='{$postLink}'>Continued</a>)");
				}
			}
		else
			{
			$html = $message->htmlMessage;
			$headEnd = \stripos($html, '</head>');

			if (false != $headEnd)
				{
				$html = \substr($html, $headEnd + 7);
				}

			$container->add(\htmlspecialchars_decode($html, ENT_QUOTES));
			}

		$container->add('</p>');

		return $container;
		}

	public function getPostLink(\App\Record\ForumMessage $message) : string
		{
		return "/Forums/post/{$message->forumId}/{$message->forumMessageId}";
		}

	public function history(\App\Table\ForumMessage $forumMessageTable) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$view = new \App\UI\ContinuousScrollTable($this->page, $forumMessageTable);
		$recordId = 'forumMessageId';
		$view->setRecordId($recordId);
		$view->setSortColumn('posted');
		$view->setSortDirection('d');

		$delete = new \PHPFUI\AJAX('deleteMessage', 'Permanently delete this message?');
		$delete->addFunction('success', '$("#' . $recordId . '-"+data.response).css("background-color","red").hide("slow").remove()');
		$this->page->addJavaScript($delete->getPageJS());

		$view->addCustomColumn('title', static fn (array $message) => new \PHPFUI\Link("/Forums/post/{$message['forumId']}/{$message['forumMessageId']}", $message['title'] ?? '', false));
		$view->addCustomColumn('Author', static function(array $message) {
			$member = new \App\Record\Member($message['memberId']);

			return $member->fullName();
			});
		$view->addCustomColumn('Del', static function(array $message) use ($recordId, $delete) {
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $delete->execute([$recordId => $message[$recordId], 'deleteId' => $message[$recordId]]));

			return $icon;
			});
		$headers = ['title', 'posted'];
		$view->setSortableColumns($headers)->setSearchColumns($headers);
		$headers[] = 'Author';

		if ($this->page->isAuthorized('Delete Forum Message'))
			{
			$headers[] = 'Del';
			}
		$view->setHeaders($headers);

		$container->add($view);

		return $container;
		}

	public function home(\App\Record\Forum $forum, bool $showSubscription = false) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$fieldSet = new \PHPFUI\FieldSet('Forum Description and Guidelines');
		$fieldSet->add(new \App\UI\Display('Email Address:', \PHPFUI\Link::email($forum->email . '@' . \emailServerName())));
		$fieldSet->add('<hr>');
		$fieldSet->add($forum->description);
		$container->add($fieldSet);
		$buttonGroup = new \PHPFUI\ButtonGroup();

		if (! $forum->closed)
			{
			$key = ['forumId' => $forum->forumId, 'memberId' => \App\Model\Session::signedInMemberId()];
			$member = new \App\Record\ForumMember($key);

			if ($member->loaded())
				{
				$newPost = 'New Post';

				if ($this->page->isAuthorized($newPost))
					{
					$postButton = $this->getReplyButton($forum, '', $newPost);
					$postButton->addClass('success');
					$buttonGroup->addButton($postButton);
					}
				$subscriptionButton = new \PHPFUI\Button('Modify Subscription');
				$subscriptionButton->addClass('warning');
				}
			else
				{
				$subscriptionButton = new \PHPFUI\Button('Subscribe');
				$subscriptionButton->addClass('success');
				$member->setFrom($key);
				}
			$modal = $this->getSubscriptionModal($subscriptionButton, $member, $forum->name);

			if ($showSubscription)
				{
				$modal->showOnPageLoad();
				}
			$buttonGroup->addButton($subscriptionButton);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('This forum is closed and is view only.'));
			}
		$searchButton = new \PHPFUI\Button('Search');
		$this->getSearchModal($searchButton);
		$buttonGroup->addButton($searchButton);
		$historyButton = new \PHPFUI\Button('All Posts', "/Forums/history/{$forum->forumId}");
		$historyButton->addClass('info');
		$buttonGroup->addButton($historyButton);
		$container->add($buttonGroup);

		if (! empty($_GET['posted:min']) && ! empty($_GET['posted:max']))
			{
			$_GET['forumId'] = $forum->forumId;
			$messages = $this->forumMessageTable->find($_GET);
			$count = \count($messages);
			$pural = 1 == $count ? '' : 's';
			$container->add(new \PHPFUI\SubHeader($count . " Search Result{$pural} Found"));

			$row = new \PHPFUI\GridX();
			$start = $_GET['posted:min'];
			$end = $_GET['posted:max'];
			$format = 'l, F j, Y';
			$row->add('From: ' . \App\Tools\Date::formatString($format, $start) . ' Through: ' . \App\Tools\Date::formatString($format, $end));
			$container->add($row);
			$container->add($this->history($this->forumMessageTable));
			}
		else
			{
			$count = 5;
			$container->add(new \PHPFUI\SubHeader($count . ' Most Recent Posts'));
			$this->forumMessageTable->addOrderBy('posted', 'desc')->setLimit($count)->setWhere(new \PHPFUI\ORM\Condition('forumId', $forum->forumId));
			$messages = $this->forumMessageTable->getRecordCursor();
			$container->add($this->showPosts($messages));
			}

		return $container;
		}

	public function listForums(\PHPFUI\ORM\RecordCursor $forums) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (\count($forums))
			{
			$table = new \PHPFUI\Table();
			$table->setRecordId('forumId');
			$delete = new \PHPFUI\AJAX('deleteForum', 'Permanently delete this forum?');
			$delete->addFunction('success', '$("#forumId-"+data.response).css("background-color","red").hide("fast").remove()');
			$this->page->addJavaScript($delete->getPageJS());
			$table->setHeaders(['name' => 'Forum Name',
				'email' => 'email Address',
				'subscribers' => 'Subscribers',
				'edit' => 'Edit',
				'del' => 'Del', ]);

			foreach ($forums as $forum)
				{
				$row = $forum->toArray();
				$row['forumId'] = $forum->forumId;
				$count = (int)\App\Table\ForumMember::getCount($forum);
				$groupIcon = new \PHPFUI\FAIcon('fas', 'users');
				$row['subscribers'] = "<a href='{$this->site}/Forums/members/{$forum->forumId}'>{$groupIcon} {$count}</a>";
				$editIcon = new \PHPFUI\FAIcon('far', 'edit', "{$this->site}/Forums/edit/{$forum->forumId}");
				$row['edit'] = $editIcon;
				$row['name'] = "<a href='{$this->site}/Forums/home/{$forum->forumId}'>{$forum->name}</a>";

				if ($forum->closed)
					{
					$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
					$icon->addAttribute('onclick', $delete->execute(['forumId' => $forum->forumId]));
					$row['del'] = $icon;
					}
				$table->addRow($row);
				}
			$container->add($table);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('No Forums Found'));
			}
		$container->add(new \App\UI\CancelButtonGroup(new \PHPFUI\Button('Add Forum', $this->site . '/Forums/edit/0')));

		return $container;
		}

	public function listMembers(\App\Record\Forum $forum, \App\Table\ForumMember $forumMemberTable) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$form = new \PHPFUI\Form($this->page, new \PHPFUI\Submit());

		if ($form->isMyCallback())
			{
			$key = ['memberId' => $_POST['memberId'] ?? 0, 'forumId' => $forum->forumId];

			$forumMember = new \App\Record\ForumMember($key);

			if ($forumMember->loaded())
				{
				$forumMember->emailType = \App\Enum\Forum\SubscriptionType::from((int)$_POST['emailType']);
				$forumMember->update();
				$this->page->setResponse('Saved');
				}
			else
				{
				$this->page->setResponse('not found');
				}

			return $container;
			}

		if (! \count($forumMemberTable))
			{
			$table = '';
			$container->add(new \PHPFUI\SubHeader('No members found in ' . $forum->name));
			}
		else
			{
			$form->add(new \PHPFUI\SubHeader('Members for ' . $forum->name));
			$form->add(new \PHPFUI\Input\Hidden('forumId', (string)$forum->forumId));

			$table = new \App\UI\ContinuousScrollTable($this->page, $forumMemberTable);
			$headers = ['firstName', 'lastName'];
			$table->setSearchColumns($headers)->setSortableColumns($headers);
			$headers[] = 'setting';
			$headers[] = 'delete';
			$table->setHeaders($headers);
			$this->page->addJavaScript('function changeSubscription(id,value){$.ajax({method:"POST",dataType:"json",data:{memberId:id,emailType:value,submit:"Save",csrf:"' . \PHPFUI\Session::csrf() . '"}})}');
			$deleter = new \App\Model\DeleteRecord($this->page, $table, $forumMemberTable, 'Are you sure you want to delete this member from the forum?');
			$table->addCustomColumn('delete', $deleter->columnCallback(...));
			$table->addCustomColumn('forumId_memberId', static fn (array $member) => $member['forumId'] . '_' . $member['memberId']);
			$table->addCustomColumn('setting', static function(array $member)
				{
				$select = new \PHPFUI\Input\SelectEnum("emailType[{$member['memberId']}]", '', \App\Enum\Forum\SubscriptionType::from((int)$member['emailType']));
				$select->addAttribute('onchange', 'changeSubscription(' . $member['memberId'] . ', this.value)');

				return $select;
				});
			}
		$addMemberButton = new \PHPFUI\Button('Add Member');
		$this->getAddMemberModal($addMemberButton, $forum);
		$form->add($addMemberButton);

		$container->add($form);
		$container->add($table);

		return $container;
		}

	public function memberForums() : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();
		$table->setHeaders(['Name', 'email', 'Subscribers']);

		$forumTable = new \App\Table\Forum();
		$forumTable->addOrderBy('name');

		foreach ($forumTable->getRecordCursor() as $forum)
			{
			if ($this->page->isAuthorized($forum->name))
				{
				$count = (int)\App\Table\ForumMember::getCount($forum);
				$forumHome = "<a href='{$this->site}/Forums/home/{$forum->forumId}'>{$forum->name}</a>";
				$email = \PHPFUI\Link::email($forum->email . '@' . \emailServerName());
				$table->addRow(['Name' => $forumHome, 'email' => $email, 'Subscribers' => $count]);
				}
			}

		return $table;
		}

	public function myForums(\PHPFUI\ORM\RecordCursor $forums) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (\count($forums))
			{
			$key = ['memberId' => \App\Model\Session::signedInMemberId()];

			$table = new \PHPFUI\Table();
			$table->setRecordId('forumId');
			$table->setHeaders(['name' => 'Public Name',
				'email' => 'email Address',
				'emailType' => 'Subscription Type',
				'action' => 'Join / Modify',
			]);

			foreach ($forums as $forum)
				{
				if ($forum->closed || ! $this->page->getPermissions()->isAuthorized($forum->name, 'Forums'))
					{
					continue;
					}
				$row = $forum->toArray();
				$key['forumId'] = $forum->forumId;
				$member = new \App\Record\ForumMember($key);

				if ($member->loaded())
					{
					$text = 'Modify';
					$class = 'warning';
					$row['email'] = \PHPFUI\Link::email($forum->email . '@' . \emailServerName());
					$row['emailType'] = $member->emailType->name();
					$member->emailType = \App\Enum\Forum\SubscriptionType::UNSUBSCRIBE;
					}
				else
					{
					$member->forumId = $forum->forumId;
					$member->emailType = \App\Enum\Forum\SubscriptionType::INDIVIDUAL_EMAILS;
					$text = 'Join';
					$class = 'success';
					$row['email'] = '';
					}
				$button = new \PHPFUI\Button($text);
				$button->addClass($class);
				$this->getSubscriptionModal($button, $member, $forum->name);
				$row['action'] = $button;
				$row['name'] = "<a href='{$this->site}/Forums/home/{$forum->forumId}'>{$forum->name}</a>";
				$table->addRow($row);
				}
			$container->add($table);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('No Forums Found'));
			}

		return $container;
		}

	public function reply(\App\Record\Forum $forum, \App\Record\ForumMessage $message) : \PHPFUI\Form | \PHPFUI\SubHeader
		{
		if (! $message->loaded() || ! $forum->loaded())
			{
			return new \PHPFUI\SubHeader('Message not found');
			}
		$form = $this->getReplyForm($forum, $message->title);
		$form->add(new \PHPFUI\Submit('Reply', 'action'));

		return $form;
		}

	public function showPosts(\PHPFUI\ORM\RecordCursor $messages) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! \count($messages))
			{
			$container->add(new \PHPFUI\SubHeader('No posts found'));

			return $container;
			}
		$message = $messages->current();
		$canDelete = $this->page->isAuthorized($message->forum->name . ' Delete Message') || $this->page->isAuthorized('Delete Forum Message');

		foreach ($messages as $message)
			{
			$div = new \PHPFUI\HTML5Element('div');
			$div->add($this->getPost($message));
			$id = $canDelete ? $div->getId() : 0;
			$div->add($this->getNavBar($message, $id, false));
			$div->add('<hr>');
			$container->add($div);
			}

		return $container;
		}

	private function getAddMemberModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Forum $forum) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('medium');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('forumId', (string)$forum->forumId));

		if ($forum->formerMembers)
			{
			$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\NonMemberPickerNoSave('Enter member name to add'), 'memberId');
			}
		else
			{
			$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Enter member name to add'), 'memberId');
			}
		$form->add($memberPicker->getEditControl());
		$form->add($this->getSubscriptionRadio(\App\Enum\Forum\SubscriptionType::UNSUBSCRIBE));
		$submit = new \PHPFUI\Submit('Add Member', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function getReplyButton(\App\Record\Forum $forum, string $topic, string $text = '') : \PHPFUI\Button | \PHPFUI\FAIcon
		{
		if ($text)
			{
			$replyButton = new \PHPFUI\Button($text);
			$replyButton->setToolTip('Start a new topic.');
			}
		else
			{
			$text = 'Reply';
			$replyButton = new \PHPFUI\FAIcon('fas', 'reply-all', '#');
			$replyButton->setToolTip('Reply to the group.');
			}
		$modal = new \PHPFUI\Reveal($this->page, $replyButton);
		$modal->addClass('large');
		$form = $this->getReplyForm($forum, $topic);
		$form->setAreYouSure(false);
		$submit = new \PHPFUI\Submit($text, 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $replyButton;
		}

	private function getReplyForm(\App\Record\Forum $forum, string $topic) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->add(new \PHPFUI\SubHeader('Post a message to ' . $forum->name));
		$form->add(new \PHPFUI\Input\Hidden('forumId', (string)$forum->forumId));
		$topic = new \PHPFUI\Input\Text('title', 'Subject', $topic);
		$topic->setToolTip('Subject will be the title of the email sent, so make it clear what your message is about.');
		$topic->setRequired();
		$form->add($topic);
		$message = new \PHPFUI\Input\TextArea('textMessage', 'Message');
		$message->setRequired();
		$form->add($message);

		return $form;
		}

	private function getReplyToPosterButton(\App\Record\ForumMessage $message) : \PHPFUI\FAIcon
		{
		$title = \urlencode($message->title ?? '');
		$icon = new \PHPFUI\FAIcon('fas', 'reply', "{$this->site}/Membership/email/{$message->memberId}?title={$title}");
		$icon->setToolTip('Reply to this poster only.');

		return $icon;
		}

	private function getSearchModal(\PHPFUI\HTML5Element $modalLink) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'get');
		$fieldSet = new \PHPFUI\FieldSet('Search Forums');
		$title = new \PHPFUI\Input\Text('title', 'Text in title to find', $_GET['title'] ?? '');
		$title->setToolTip('Groups of words will be searched as a phrase and not individually.');
		$fieldSet->add($title);
		$textMessage = new \PHPFUI\Input\Text('textMessage', 'Text in messages to find', $_GET['textMessage'] ?? '');
		$textMessage->setToolTip('Groups of words will be searched as a phrase and not individually.');
		$fieldSet->add($textMessage);
		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Posting Member Name'), 'memberId');
		$picker = $memberPicker->getEditControl();
		$picker->setToolTip('You can search for specific posters by entering their name.');
		$fieldSet->add($picker);
		$end = \App\Tools\Date::today();
		$start = $end - 30;

		if (! empty($_GET['posted:min']))
			{
			$start = \App\Tools\Date::fromString($_GET['posted:min']);
			}

		if (! empty($_GET['posted:max']))
			{
			$end = \App\Tools\Date::fromString($_GET['posted:max']);
			}

		$multiColumn = new \PHPFUI\MultiColumn();
		$multiColumn->add(new \PHPFUI\Input\Date($this->page, 'posted:min', 'Start Date', \App\Tools\Date::toString($start)));
		$multiColumn->add(new \PHPFUI\Input\Date($this->page, 'posted:max', 'End Date', \App\Tools\Date::toString($end)));
		$fieldSet->add($multiColumn);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	private function getSubscriptionModal(\PHPFUI\HTML5Element $modalLink, \App\Record\ForumMember $member, string $name) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('meduim');
		$modal->add(new \PHPFUI\SubHeader("{$name} Subscription Settings"));
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('forumId', (string)$member->forumId));
		$radio = $this->getSubscriptionRadio($member->emailType);
		$form->add($radio);
		$form->add('<br>');
		$submit = new \PHPFUI\Submit('Save', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	private function getSubscriptionRadio(\App\Enum\Forum\SubscriptionType $initialValue, string $fieldPostfix = '') : \PHPFUI\Input\RadioGroupEnum
		{
		$radio = new \PHPFUI\Input\RadioGroupEnum('emailType' . $fieldPostfix, 'Subscription Type', $initialValue);
		$radio->setToolTip('Members can always view forums on the web, but can not send email to the group unless subscribed.');
		$radio->setSeparateRows();

		return $radio;
		}

	private function processRequest() : void
		{
		$url = '';

		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'deleteForum':
						$forum = new \App\Record\Forum((int)$_POST['forumId']);

						if ($forum->loaded() && $forum->closed)
							{
							$forum->delete();
							$this->page->setResponse($_POST['forumId']);
							}
						else
							{
							$this->page->setResponse('0');
							}

						break;

					case 'deleteMessage':
						if ($this->page->isAuthorized('Delete Forum Message'))
							{
							$forumMesage = new \App\Record\ForumMessage();
							$forumMesage->setFrom($_POST);
							$forumMesage->delete();
							$this->page->setResponse($_POST['deleteId']);
							}

						break;

					case 'deleteMember':
						$forumMember = new \App\Record\ForumMember();
						$forumMember->setFrom(['forumId' => (int)$_POST['forumId'], 'memberId' => (int)$_POST['memberId']]);
						$forumMember->delete();
						$this->page->setResponse($_POST['memberId']);

						break;

					case 'Reply':
						$url = '/Forums/home/' . $_POST['forumId'];

						// Intentionally fall through
					case 'New Post':
						$member = \App\Model\Session::getSignedInMember();
						$_POST['firstName'] = $member['firstName'];
						$_POST['lastName'] = $member['lastName'];
						$_POST['email'] = $member['email'];
						$_POST['memberId'] = $member['memberId'];
						$_POST['posted'] = \date('Y-m-d H:i:s');
						$id = $this->model->post($_POST);
						$this->page->redirect($url);

						break;

					case 'Save':
						$_POST['memberId'] = \App\Model\Session::signedInMemberId();

						// Intentionally fall through
					case 'Add Member':
						$forumMember = new \App\Record\ForumMember();
						$forumMember->setFrom($_POST);

						if (! $forumMember->emailType->value)
							{
							$forumMember->delete();
							$this->page->redirect('/Forums/my');
							}
						else
							{
							$forumMember->save();
							$this->page->redirect();
							}

						break;
					}
				}
			}
		}
	}
