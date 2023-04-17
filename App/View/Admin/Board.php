<?php

namespace App\View\Admin;

class Board
	{
	private readonly \App\Table\BoardMember $boardMemberTable;

	private readonly \App\Model\BoardImages $imageModel;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->boardMemberTable = new \App\Table\BoardMember();
		$this->imageModel = new \App\Model\BoardImages();
		}

	public function editMember(\App\Record\BoardMember $board) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($board->empty())
			{
			$form->add(new \PHPFUI\SubHeader('Not found'));

			return $form;
			}

		$this->imageModel->update($board->toArray());

		if ($form->isMyCallback())
			{
			$_POST['description'] = \App\Tools\TextHelper::cleanUserHtml($_POST['description']);
			$board->setFrom($_POST);
			$board->update();
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']) && 'deletePhoto' == $_POST['action'])
				{
				$this->imageModel->delete();
				$board->extension = '';
				$board->update();
				$this->page->setResponse((string)$board->memberId);
				}
			elseif (isset($_POST['submit']) && 'Add Photo' == $_POST['submit'])
				{
				if ($this->imageModel->upload((string)$board->memberId, 'photo', $_FILES))
					{
					$board->extension = $this->imageModel->getExtension();
					$board->update();
					$this->imageModel->createThumb(100);
					\App\Model\Session::setFlash('success', 'Photo uploaded successfully');
					}
				else
					{
					\App\Model\Session::setFlash('alert', $this->imageModel->getLastError());
					}
				$this->page->redirect();
				}
			}
		else
			{
			$member = $board->member;
			$form->add(new \PHPFUI\SubHeader($member->firstName . ' ' . $member->lastName));
			$fieldSet = new \PHPFUI\FieldSet('Board Information');
			$fieldSet->add(new \PHPFUI\Input\Hidden('memberId', (string)$board->memberId));
			$title = new \PHPFUI\Input\Text('title', 'Board Position', $board->title);
			$title->setRequired();
			$fieldSet->add($title);
			$description = new \PHPFUI\Input\TextArea('description', 'Profile Information', $board->description);
			$description->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			$fieldSet->add($description);
			$form->add($fieldSet);
			$addPhotoButton = new \PHPFUI\Button('Add Photo');
			$addPhotoButton->addClass('success');
			$form->saveOnClick($addPhotoButton);
			$modal = new \PHPFUI\Reveal($this->page, $addPhotoButton);
			$submitPhoto = new \PHPFUI\Submit('Add Photo');
			$uploadForm = new \PHPFUI\Form($this->page);
			$uploadForm->setAreYouSure(false);
			$file = new \PHPFUI\Input\File($this->page, 'photo', 'Select Photo');
			$file->setAllowedExtensions(['png', 'jpg', 'jpeg']);
			$file->setToolTip('Photo should be clear and high quality.  It will be sized correctly, so the higher resolution, the better.');
			$uploadForm->add($file);
			$uploadForm->add($modal->getButtonAndCancel($submitPhoto));
			$modal->add($uploadForm);
			$this->imageModel->update($board->toArray());

			if ($board->extension)
				{
				$photoSet = new \PHPFUI\FieldSet('Photo');
				$row = new \PHPFUI\GridX();
				$row->add($this->imageModel->getThumbnail());
				$photoSet->add($row);
				$deletePhoto = new \PHPFUI\AJAX('deletePhoto', 'Are you sure you want to delete this photo? Will be able to add a new one after it is deleted.');
				$deletePhoto->addFunction('success', '$("#' . $photoSet->getId() . '").css("background-color","red").hide("fast");$("#' . $addPhotoButton->getId() . '").show()');
				$this->page->addJavaScript($deletePhoto->getPageJS());
				$this->page->addJavaScript('$("#' . $addPhotoButton->getId() . '").hide()');
				$delete = new \PHPFUI\Button('Delete', '#');
				$delete->addAttribute('onclick', $deletePhoto->execute(['memberId' => $board->memberId]));
				$row = new \PHPFUI\GridX();
				$row->add('&nbsp;');
				$photoSet->add($row);
				$row = new \PHPFUI\GridX();
				$row->add($delete);
				$photoSet->add($row);
				$form->add($photoSet);
				}
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$buttonGroup->addButton($addPhotoButton);
			$form->add($buttonGroup);
			}

		return $form;
		}

	public function editView() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save Ranking');
		$form = new \PHPFUI\Form($this->page, $submit);

		if (isset($_POST['action']) && \App\Model\Session::checkCSRF())
			{
			switch ($_POST['action'])
				{
				case 'deleteBoardMember':
					$item = new \App\Record\BoardMember((int)$_POST['memberId']);

					if (! $item->empty())
						{
						$this->imageModel->update($item->toArray());
						$this->imageModel->delete();
						$item->delete();
						}
					$this->page->setResponse($_POST['memberId']);

					break;

				case 'Add':
					$boardMember = (int)$_POST['EnterBoardMemberName'];

					if ($boardMember)
						{
						$data = ['memberId' => $boardMember, 'title' => 'Board Member'];
						$boardMember = new \App\Record\BoardMember($data);
						$id = $boardMember->insertOrUpdate();
						$this->page->redirect("/Admin/boardMember/{$id}");
						}
					$this->page->redirect('/Admin/board');

					break;
				}
			}
		elseif ($form->isMyCallback())
			{
			\PHPFUI\ORM::beginTransaction();
			$this->boardMemberTable->clearRank();
			$rank = 100;

			if (isset($_POST['memberId']))
				{
				$boardMember = new \App\Record\BoardMember();

				foreach ($_POST['memberId'] as $memberId)
					{
					$boardMember->rank = --$rank;
					$boardMember->memberId = (int)$memberId;
					$boardMember->update();
					}
				}
			\PHPFUI\ORM::commit();
			$this->page->setResponse('Ranking Saved');
			}
		else
			{
			$index = 'memberId';
			$delete = new \PHPFUI\AJAX('deleteBoardMember', 'Are you sure you want to delete this board member?');
			$delete->addFunction('success', "$('#id{$index}-'+data.response).css('background-color','red').hide('fast')");
			$this->page->addJavaScript($delete->getPageJS());

			$add = new \PHPFUI\Button('Add Board Member');
			$form->saveOnClick($add);
			$this->addBoardMemberModal($add);
			$form->add($add);

			$ul = new \PHPFUI\UnorderedList($this->page);
			$boardMembers = $this->boardMemberTable->getBoardMembers();

			foreach ($boardMembers as $board)
				{
				$member = $board->member;
				$memberId = $member->memberId;
				$row = new \PHPFUI\GridX();
				$titleColumn = new \PHPFUI\Cell(5);
				$title = "<strong><em>{$board->title}</em></strong>";
				$hidden = new \PHPFUI\Input\Hidden('memberId[]', $memberId);
				$titleColumn->add($title . $hidden);
				$row->add($titleColumn);
				$descriptionColumn = new \PHPFUI\Cell(5);
				$descriptionColumn->add($member->firstName . ' ' . $member->lastName);
				$row->add($descriptionColumn);
				$editColumn = new \PHPFUI\Cell(1);
				$editColumn->add(new \PHPFUI\FAIcon('far', 'edit', '/Admin/boardMember/' . $memberId));
				$row->add($editColumn);
				$trashColumn = new \PHPFUI\Cell(1);
				$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$trash->addAttribute('onclick', $delete->execute([$index => $memberId]));
				$trashColumn->add($trash);
				$row->add($trashColumn);
				$listItem = new \PHPFUI\ListItem($row);
				$ul->addItem($listItem->setId("id{$index}-{$memberId}"));
				}
			$form->add($ul);
			$form->add($submit);
			}

		return $form;
		}

	public function publicView() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$boardMembers = $this->boardMemberTable->getBoardMembers();

		foreach ($boardMembers as $board)
			{
			$mediaObject = new \PHPFUI\MediaObject();
			$mediaObject->stackForSmall();
			$member = $board->member;

			if ($board->extension)
				{
				$this->imageModel->update($board->toArray());
				$image = $this->imageModel->getThumbnailImg();
				$thumbnail = new \PHPFUI\HTML5Element('div');
				$thumbnail->addClass('thumbnail');
				$thumbnail->add($image);
				$mediaObject->addSection($thumbnail, false, 'align-self-bottom');
				}
			$header = new \PHPFUI\Header("<em>{$board->title}</em> - {$member->fullName()}", 5);
			$mediaObject->addSection($header . $board->description, true);
			$container->add($mediaObject);
			}

		return $container;
		}

	private function addBoardMemberModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Board Member Name (type first or last name)');
		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Enter Board Member Name'));
		$fieldSet->add($memberPicker->getEditControl());
		$modalForm->add($fieldSet);
		$modalForm->add(new \PHPFUI\Submit('Add', 'action'));
		$modal->add($modalForm);
		}
	}
