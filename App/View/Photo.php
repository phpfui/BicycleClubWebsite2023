<?php

namespace App\View;

class Photo extends \App\View\Folder
	{
	private readonly bool $deleteComments;

	private readonly \App\Model\PhotoFiles $photoFiles;

	private readonly \App\Table\PhotoTag $photoTagTable;

	/**
	 * @var array<string>
	 */
	private array $rows = [1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Forth', 5 => 'Fifth'];

	public function __construct(\App\View\Page $page)
		{
		parent::__construct($page, __CLASS__);
		$this->photoFiles = new \App\Model\PhotoFiles();
		$this->photoTagTable = new \App\Table\PhotoTag();
		$this->deleteComments = $this->page->isAuthorized('Delete Photo Comments');
		}

	public function getComments(\App\Record\Photo $photo) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$addComment = new \PHPFUI\Submit('Add');
		$addComment->setConfirm('Are you sure you want to add your comment?');

		if (\App\Model\Session::checkCSRF())
			{
			if ($addComment->submitted($_POST) && ! empty($_POST['photoComment']) && $this->page->isAuthorized('Photo Comments'))
				{
				$comment = new \App\Record\PhotoComment();
				$comment->setFrom(['photoComment' => $_POST['photoComment'], 'photoId' => $photo->photoId, 'memberId' => $this->signedInMember]);
				$comment->insert();
				$this->page->redirect();

				return $container;
				}
			elseif ('deleteComment' == ($_POST['action'] ?? '') && isset($_POST['photoCommentId']))
				{
				$comment = new \App\Record\PhotoComment((int)$_POST['photoCommentId']);

				if ($this->deleteComments || $comment->memberId == $this->signedInMember)
					{
					$comment->delete();
					}
				$this->page->setResponse($_POST['photoCommentId']);

				return $container;
				}
			}

		$index = 'photoCommentId';
		$delete = new \PHPFUI\AJAX('deleteComment', 'Are you sure you want to delete this comment?');
		$delete->addFunction('success', "$('#{$index}-'+data.response).css('background-color','red').hide('fast').remove()");
		$this->page->addJavaScript($delete->getPageJS());

		foreach ($photo->PhotoCommentChildren as $comment)
			{
			$photoCommentId = $comment->photoCommentId;
			$row = new \PHPFUI\GridX();
			$nameColumn = new \PHPFUI\Cell(11);
			$time = \App\Tools\TimeHelper::relativeFormat($comment->timestamp);
			$member = $comment->member;

			$nameColumn->add("<b>{$member->fullName()}</b> - <i>{$time}</i> said:<br>" . $comment->photoComment);
			$row->add($nameColumn);

			if ($this->deleteComments || $comment->memberId == $this->signedInMember)
				{
				$deleteColumn = new \PHPFUI\Cell(1);
				$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$trash->addAttribute('onclick', $delete->execute([$index => $photoCommentId]));
				$deleteColumn->add($trash);
				$row->add($deleteColumn);
				}
			$row->setId("{$index}-{$photoCommentId}");
			$container->add($row);
			$container->add('<hr>');
			}

		$form = new \PHPFUI\Form($this->page);
		$gridX = new \PHPFUI\GridX();
		$gridX->addClass('align-middle');
		$cell = new \PHPFUI\Cell(11);
		$photoComment = new \PHPFUI\Input\TextArea('photoComment', 'Your Comments');
		$photoComment->setRequired()->setAttribute('maxlength', (string)255)->setAttribute('rows', (string)3);
		$cell->add($photoComment);
		$gridX->add($cell);
		$buttonCell = new \PHPFUI\Cell(1);
		$buttonCell->add($addComment);
		$gridX->add($buttonCell);
		$form->add($gridX);
		$container->add($form);

		return $container;
		}

	public function getImage(\App\Record\Photo $photo) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$container->add($this->getNavBar($photo));

		$gridX = new \PHPFUI\GridX();
		$cell = new \PHPFUI\Cell(12);
		$cell->addClass('text-center');
		$cell->add($photo->getImage());
		$gridX->add($cell);
		$container->add($gridX);

		$container->add($this->getNavBar($photo));

		return $container;
		}

	public function getInfo(\App\Record\Photo $photo) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if ($photo->memberId == $this->signedInMember || $this->page->isAuthorized('Edit Photo Title'))
			{
			$save = new \PHPFUI\Submit('Save');
			$save->addClass('small');
			$form = new \PHPFUI\Form($this->page, $save);

			if ($form->isMyCallback())
				{
				$photo->description = $_POST['description'];
				$photo->public = (int)$_POST['public'];
				$photo->update();
				$this->page->setResponse('Saved');

				return $container;
				}

			$gridX = new \PHPFUI\GridX();
			$gridX->addClass('align-middle');
			$cell = new \PHPFUI\Cell(10, 11);
			$description = new \PHPFUI\Input\Text('description', 'Photo Caption', $photo->description);
			$cell->add($description);
			$gridX->add($cell);
			$buttonCell = new \PHPFUI\Cell(2, 1);
			$buttonCell->add($save);
			$gridX->add($buttonCell);
			$form->add($gridX);

			$publicField = new \PHPFUI\Input\CheckBoxBoolean('public', 'Allow Public Views', (bool)$photo->public);
			$publicField->setToolTip('If checked, this photo can be accessed by anyone with the correct link');
			$callout = new \PHPFUI\HTML5Element('b');
			$url = $this->page->value('homePage') . '/Photo/image/' . $photo->photoId;
			$link = new \PHPFUI\Link($url, $photo->description);
			$link->addAttribute('target', '_blank');
			$callout->add($link);
			$publicField->addAttribute('onclick', '$("#' . $callout->getId() . '").toggleClass("hide");');

			if (! $photo->public)
				{
				$callout->addClass('hide');
				}

			$gridX = new \PHPFUI\GridX();
			$gridX->addClass('align-middle');
			$publicViewCell = new \PHPFUI\Cell(4);
			$publicViewCell->add($publicField);
			$gridX->add($publicViewCell);
			$linkCell = new \PHPFUI\Cell(7);
			$linkCell->add($callout);
			$gridX->add($linkCell);
			$copyCell = new \PHPFUI\Cell(1);
			$copyButton = new \PHPFUI\Button('Copy');
			$flash = new \PHPFUI\Callout('success');
			$flash->add($url . ' Copied to clipboard');
			$this->page->addCopyToClipboard($url, $copyButton, $flash);
			$copyButton->addClass('tiny warning');
			$copyCell->add($copyButton);
			$gridX->add($copyCell);
			$form->add($gridX);
			$form->add($flash);

			$container->add($form);
			}
		elseif ($photo->photo)
			{
			$titleGrid = new \PHPFUI\HTML5Element('p');
			$titleGrid->addClass('text-center');
			$titleGrid->add($photo->photo);
			$container->add($titleGrid);
			}

		$info = $this->photoFiles->getInformation($photo->photoId, $photo->extension);
		$link = \App\Model\RideWithGPS::getMapPinLink($info);

		$titleGrid = new \PHPFUI\GridX();
		$titleGrid->addClass('grid-padding-x');
		$titleGrid->addClass('align-center');

		$member = $photo->member;

		if (! $member->empty())
			{
			$titleGrid->add('<b>Uploaded By:</b> ' . $member->fullName());
			}

		if ($link)
			{
			if (\count($titleGrid))
				{
				$titleGrid->add(' - ');
				}
			$titleGrid->add(new \PHPFUI\Link($link, 'Photo Location'));
			}

		if (! empty($photo->taken))
			{
			if (\count($titleGrid))
				{
				$titleGrid->add(' - ');
				}
			$titleGrid->add('<b>Taken:</b> ' . \date('D M j, Y, g:i a', \strtotime($photo->taken)));
			}

		if (\count($titleGrid))
			{
			$container->add($titleGrid);
			}

		return $container;
		}

	public function getNavBar(\App\Record\Photo $photo) : \PHPFUI\GridX
		{
		$album = \App\Model\Session::getPhotoAlbum();
		$navDiv = new \PHPFUI\GridX();

		if (empty($album))
			{
			return $navDiv;
			}

		$photoId = $photo->photoId;

		foreach ($album as $index => $id)
			{
			if ($id == $photoId)
				{
				break;
				}
			}
		$next = $index + 1;
		$previous = $index - 1;

		if ($previous < 0)
			{
			$previous = \count($album) - 1;
			}

		if ($next >= \count($album))
			{
			$next = 0;
			}
		$next = $album[$next];
		$previous = $album[$previous];

		$navDiv->addClass('text-center');
		$cellLeft = new \PHPFUI\Cell(1);
		$left = new \PHPFUI\FAIcon('fas', 'caret-square-left', '/Photo/view/' . $previous);
		$cellLeft->add($left);
		$navDiv->add($cellLeft);
		$cellCenter = new \PHPFUI\Cell(10);

		if ($this->page->isAuthorized('Delete Photo'))
			{
			$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '/Photo/delete/' . $photo->photoId);
			$trash->setConfirm('Delete this photo? This can not be undone.');
			$cellCenter->add($trash);
			}
		$navDiv->add($cellCenter);
		$cellRight = new \PHPFUI\Cell(1);
		$right = new \PHPFUI\FAIcon('fas', 'caret-square-right', '/Photo/view/' . $next);
		$cellRight->add($right);
		$navDiv->add($cellRight);

		return $navDiv;
		}

	public function getTags(\App\Record\Photo $photo) : \PHPFUI\Form
		{
		$photoId = $photo->photoId;
		$saveTagButton = new \PHPFUI\Submit('Save Tag Order');
		$form = new \PHPFUI\Form($this->page, $saveTagButton);

		$photoTag = null;

		if ($form->isMyCallback())
			{
			$tags = $_POST['photoTagId'] ?? [];
			$this->photoTagTable->deleteNotIn($photoId, $tags);
			$leftToRight = 0;

			foreach ($tags as $index => $photoTagId)
				{
				$photoTag = new \App\Record\PhotoTag([
					'photoTagId' => $photoTagId,
					'frontToBack' => $_POST['frontToBack'][$index],
					'leftToRight' => ++$leftToRight]);
				$photoTag->update();
				}
			$this->page->setResponse('Tag Order Saved');

			return $form;
			}

		$js = <<<JS
(function() {
  var dragSrcEl_ = null;
  this.handleDragStart = function(e) {
    dragSrcEl_ = this;
    $(this).addClass('moving');
  };
  this.handleDragOver = function(e) {
    if (e.preventDefault) {e.preventDefault();} // Allows us to drop.
		return false;
  };
  this.handleDragEnter = function(e) {
		var target = $(this);
		target.data('count', target.data('count') + 1);
		target.addClass('over');
  };
  this.handleDragLeave = function(e) {
		var cols_ = document.querySelectorAll('.photoTag');
		var target = $(this);
		target.data('count', target.data('count') - 1);
		if (target.data('count') <= 0) {
			target.removeClass('over');
			target.data('count', 0);
		}
  };
  this.handleDrop = function(e) {
    if (e.stopPropagation) {e.stopPropagation();} // stops the browser from redirecting.
    // Don't do anything if we're dropping on the same column we're dragging.
    if (dragSrcEl_ != this) {
			var original = $(dragSrcEl_);
			var moved = original.clone(true);
			var me = $(this);
			// need to update frontToBack hidden field value from drop container
			moved.children("input[name='frontToBack[]']").val(me.parent().data('row'));
			me.before(moved);
			original.remove();
			$('.photoTag').removeClass('over moving').data('count',0);
    }
    return false;
  };
  this.handleDragEnd = function(e) {
		$('.photoTag').removeClass('over moving').data('count',0);
  };
	this.init = function() {
		var photoTag=$('.photoTag');
		photoTag.on('dragstart', this.handleDragStart);
		photoTag.on('dragenter', this.handleDragEnter);
		photoTag.on('dragover', this.handleDragOver);
		photoTag.on('dragleave', this.handleDragLeave);
		photoTag.on('drop', this.handleDrop);
		photoTag.on('dragend', this.handleDragEnd);
	};
	this.init();
})();
JS;

		$this->page->addJavaScript($js);
		$tags = $this->photoTagTable->getTagsForPhoto($photoId);

		$lastRow = 0;
		$callout = null;
		$endTag = '<div class="photoTag" data-count=0>&nbsp; &nbsp;</div>';

		foreach ($tags as $tag)
			{
			if ($tag['frontToBack'] != $lastRow)
				{
				if ($callout)
					{
					$callout->add($endTag);
					$form->add($callout);
					}
				$lastRow = $tag['frontToBack'];
				$callout = new \PHPFUI\Callout();
				$callout->addClass('small')->addAttribute('data-row', $lastRow)->addClass('flex-container');
				$callout->add("<b>{$this->rows[$lastRow]} Row</b> (L-R):");
				}
			$callout->add(new \App\View\PhotoTag($tag));
			}

		if ($callout)
			{
			$callout->add($endTag);
			$form->add($callout);
			}

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$addTagButton = new \PHPFUI\Button('Add Tag');
		$form->saveOnClick($addTagButton);
		$addTagButton->addClass('secondary');
		$this->getAddTagReveal($addTagButton, $photoId);

		if (\count($tags))
			{
			$buttonGroup->addButton($saveTagButton);
			}
		$buttonGroup->addButton($addTagButton);
		$form->add($buttonGroup);

		return $form;
		}

	public function listMembers(\PHPFUI\ORM\ArrayCursor $members, string $url = '') : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();

		foreach ($members as $member)
			{
			if ($member['count'])
				{
				$row = [];
				$row[] = $member['count'];
				$name = $member['firstName'] . ' ' . $member['lastName'];

				if ($url)
					{
					$name = new \PHPFUI\Link($url . $member['memberId'], $name, false);
					}
				$row[] = $name;
				$table->addRow($row);
				}
			}

		return $table;
		}

	public function listPhotos(\App\Table\Photo $photoTable, bool $allowCut = false) : \App\UI\ContinuousScrollTable
		{
		\App\Model\Session::clearPhotoAlbum();

		$view = new \App\UI\ContinuousScrollTable($this->page, $photoTable);
		$cursor = $view->getRawArrayCursor();

		foreach ($cursor as $photo)
			{
			\App\Model\Session::addPhotoToAlbum((int)$photo['photoId']);
			}

		$this->cuts = $this->getCuts();

		$view->addCustomColumn('uploaded', static fn (array $photo) => \date('Y-m-d', \strtotime((string)$photo['uploaded'])));
		$view->addCustomColumn('taken', static fn (array $photo) => $photo['taken'] ? \date('D M j, Y, g:i a', \strtotime((string)$photo['taken'])) : '');
		$view->addCustomColumn(
			'description',
			static function(array $photo)
			{
			$name = empty($photo['description']) ? $photo['photoId'] : $photo['description'];

			return new \PHPFUI\Link('/Photo/view/' . $photo['photoId'], $name, false);
			}
		);

		$headers = ['description' => 'Caption', 'taken', 'uploaded'];
		$normalHeaders = [];

		if ($allowCut)
			{
			$normalHeaders = ['cut'];
			$view->addCustomColumn('cut', $this->getCut(...));
			}

		$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, $normalHeaders))->setSortableColumns($headers);

		return $view;
		}

	public function listTags(\App\Record\Photo $photo) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$photoId = $photo->photoId;
		$tags = $this->photoTagTable->getTagsForPhoto($photoId);

		$lastRow = 0;
		$row = null;
		$comma = '';
		$maxRow = 0;
		$tagCount = \count($tags);

		foreach ($tags as $tag)
			{
			$maxRow = \max($maxRow, $tag['frontToBack']);
			}

		foreach ($tags as $tag)
			{
			if ($tag['frontToBack'] != $lastRow)
				{
				if ($row)
					{
					$container->add($row);
					$comma = '';
					}
				$lastRow = $tag['frontToBack'];
				$row = new \PHPFUI\GridX();
				$row->addClass('grid-padding-x');
				$row->addClass('align-center');

				if ($maxRow > 1)
					{
					$row->add("<b>{$this->rows[$lastRow]} Row</b>");
					}

				if ($tagCount > 1)
					{
					$row->add('(L-R):');
					}
				}
			$row->add($comma);
			$comma = ', ';
			$row->add($tag['photoTag']);
			}

		if ($row)
			{
			$container->add($row);
			}

		return $container;
		}

	protected function addModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Folder $folder) : void
		{
		$submit = new \PHPFUI\Submit('Add Photo');

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			$fileTypes = [
				'.jpg' => 'image/jpeg',
				'.jpeg' => 'image/jpeg',
				'.gif' => 'image/gif',
				'.png' => 'image/png',
			];

			$photo = new \App\Record\Photo();
			$photo->setFrom([
				'folderId' => $folder->folderId,
				'description' => $_POST['description'] ?? '',
				'memberId' => $this->signedInMember,
				'public' => $_POST['public'] ?? 0,
			]);
			$photoId = $photo->insert();
			$photo->reload();

			if ($this->photoFiles->upload((string)$photoId, 'file', $_FILES, $fileTypes))
				{
				$photo->extension = $this->photoFiles->getExtension();

				if (empty($photo->description))
					{
					$photo->description = \substr($this->photoFiles->getUploadName(), 0, \strpos($this->photoFiles->getUploadName(), '.'));
					}
				$info = $this->photoFiles->getInformation($photoId, $photo->extension);

				if (isset($info['taken']))
					{
					$photo->taken = $info['taken'];
					}
				$photo->update();
				\App\Model\Session::setFlash('success', 'Photo uploaded');
				}
			else
				{
				$photo->delete();
				\App\Model\Session::setFlash('alert', $this->photoFiles->getLastError());
				}
			$this->page->redirect();

			return;
			}

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Add Photo To This Folder');
		$publicField = new \PHPFUI\Input\CheckBoxBoolean('public', 'Allow Public Views');
		$publicField->setToolTip('If checked, this photo can be accessed by anyone with the correct link');
		$fieldSet->add($publicField);
		$caption = new \PHPFUI\Input\Text('description', 'Photo Caption');
		$caption->setToolTip('This caption will also be shown in the folder list view.');
		$fieldSet->add($caption);
		$file = new \PHPFUI\Input\File($this->page, 'file', 'Photo To Add');
		$file->setRequired();
		$fieldSet->add($file);
		$form->add($fieldSet);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function getAddTagReveal(\PHPFUI\HTML5Element $modalLink, int $photoId) : void
		{
		$submit = new \PHPFUI\Submit('Add Tag');

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			if (! empty($_POST['memberId']) || ! empty($_POST['photoTag']))
				{
				$row = (int)$_POST['row'];
				$photoTag = new \App\Record\PhotoTag();
				$photoTag->setFrom([
					'memberId' => empty($_POST['memberId']) ? null : (int)$_POST['memberId'],
					'photoId' => $photoId,
					'taggerId' => $this->signedInMember,
					'frontToBack' => $row,
					'leftToRight' => $this->photoTagTable->getHighestRight($photoId, $row),
				]);

				if (empty($_POST['photoTag']))
					{
					$photoTag->photoTag = \trim((string)$_POST['memberIdText']);
					}
				else
					{
					$photoTag->photoTag = \trim((string)$_POST['photoTag']);
					}
				$photoTag->insert();
				}
			$this->page->redirect();

			return;
			}

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$modal->add(new \PHPFUI\SubHeader('Tag People'));
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\NonMemberPickerNoSave('Member'), 'memberId');
		$autoSelect = $memberPicker->getEditControl();
		$form->add($autoSelect);
		$nameInput = new \PHPFUI\Input\Text('photoTag', 'Non-Member Name');
		$nameInput->setToolTip('If the person was never a member, add their name here');
		$form->add($nameInput);

		$rowSelect = new \PHPFUI\Input\Select('row', 'Row');

		foreach ($this->rows as $index => $row)
			{
			$rowSelect->addOption($row, $index);
			}

		$form->add($rowSelect);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
