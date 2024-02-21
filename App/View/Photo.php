<?php

namespace App\View;

class Photo
	{
	/**
	 * @var array<int,int>
	 */
	private array $cuts = [];

	private readonly bool $deleteComments;

	private bool $moveFolder = false;

	private bool $movePhoto = false;

	private readonly \App\Model\PhotoFiles $photoFiles;

	private readonly \App\Table\PhotoTag $photoTagTable;

	/**
	 * @var array<string>
	 */
	private array $rows = [1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Forth', 5 => 'Fifth'];

	private ?\PHPFUI\Button $searchButton = null;

	private readonly int $signedInMember;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->photoFiles = new \App\Model\PhotoFiles();
		$this->photoTagTable = new \App\Table\PhotoTag();
		$this->deleteComments = $this->page->isAuthorized('Delete Photo Comments');
		$this->signedInMember = \App\Model\Session::signedInMemberId();
		$this->movePhoto = $page->isAuthorized('Move Photo');
		$this->moveFolder = $page->isAuthorized('Move Folder');
		}

	public function clipboard(int $photoFolderId) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$cuts = \App\Model\Session::getPhotoCuts();

		if ($cuts)
			{
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$form->setAttribute('action', '/Photo/paste');
			$form->add(new \PHPFUI\Input\Hidden('photoFolderId', (string)$photoFolderId));
			$fieldSet = new \PHPFUI\FieldSet('Pasteable Items');
			$multiSelect = new \PHPFUI\Input\MultiSelect('paste');
			$multiSelect->selectAll();

			foreach ($cuts as $photoId => $value)
				{
				if ($photoId < 0)
					{
					$photoFolder = new \App\Record\PhotoFolder(0 - $photoId);
					$name = $photoFolder->photoFolder;
					$multiSelect->addOption('Folder: ' . $name, (string)$photoId);
					}
				else
					{
					$photo = new \App\Record\Photo($photoId);
					$name = $photo->photo ?: $photoId;

					if ($photoFolderId)
						{
						$multiSelect->addOption('Photo: ' . $name, (string)$photoId);
						}
					else
						{
						$multiSelect->addOption('Paste Disabled: ' . $name, disabled:true);
						}
					}
				}
			$fieldSet->add($multiSelect);

			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton(new \PHPFUI\Submit('Paste'));
			$buttonGroup->addButton(new \PHPFUI\Submit('UnCut'));
			$fieldSet->add($buttonGroup);
			$form->add($fieldSet);
			$container->add($form);
			}

		return $container;
		}

	/**
	 * Get standard folder breadcrumbs
	 *
	 * @param string $url should be / terminated, folderId will be appended
	 */
	public function getBreadCrumbs(string $url, int $photoFolderId, int $photoId = 0) : \PHPFUI\BreadCrumbs
		{
		$breadCrumbs = new \PHPFUI\BreadCrumbs();

		$folders = \App\Table\PhotoFolder::getFolders($photoFolderId);

		$breadCrumbs->addCrumb('All', '/Photo/browse');

		foreach ($folders as $folderId => $name)
			{
			$link = '';

			if ($folderId != $photoFolderId || $photoId)
				{
				$link = $url . $folderId;
				}
			$breadCrumbs->addCrumb($name, $link);
			}

		if ($photoId)
			{
			$photo = new \App\Record\Photo($photoId);
			$breadCrumbs->addCrumb($photo->photo);
			}

		return $breadCrumbs;
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
				$photo->photo = $_POST['photo'];
				$photo->public = (int)$_POST['public'];
				$photo->update();
				$this->page->setResponse('Saved');

				return $container;
				}

			$gridX = new \PHPFUI\GridX();
			$gridX->addClass('align-middle');
			$cell = new \PHPFUI\Cell(10, 11);
			$description = new \PHPFUI\Input\Text('photo', 'Photo Caption', $photo->photo);
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
			$link = new \PHPFUI\Link($url, $photo->photo);
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

	/**
	 * @param array<string,string> $parameters
	 */
	public function getSearchButton(\App\Table\Photo $photoTable, array $parameters = [], bool $openOnPageLoad = true) : \PHPFUI\Button
		{
		if ($this->searchButton)
			{
			return $this->searchButton;
			}

		$this->searchButton = new \PHPFUI\Button('Search');

		$modal = new \PHPFUI\Reveal($this->page, $this->searchButton);
		$form = new \PHPFUI\Form($this->page);
		$form->add(new \PHPFUI\SubHeader('Search Photos'));

		if ($openOnPageLoad)
			{
			$modal->showOnPageLoad();
			}

		if (! \count($photoTable) && $openOnPageLoad)
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->addClass('small');
			$callout->add('No matches found');
			$form->add($callout);
			}
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('p', $parameters['p'] ?? 0));
		$form->setAttribute('method', 'get');

		$searchFields = [
			'photo' => 'Caption',
			'photoTag' => 'Tag',
			'photoComment' => 'Comment',
		];

		foreach ($searchFields as $field => $name)
			{
			$form->add(new \PHPFUI\Input\Text($field, $name, $parameters[$field] ?? ''));
			}

		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $this->searchButton;
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

	public function hasPermission(\App\Record\Photo | \App\Record\PhotoFolder $file) : bool
		{
		if ($file instanceof \App\Record\Photo)
			{
			if (! $file->loaded())
				{
				return false;
				}

			if ($file->public)
				{
				return true;
				}

			if (! \App\Model\Session::isSignedIn())
				{
				return false;
				}

			// user must have permissions all the way up
			$parentFolder = $file->photoFolder;
			}
		else
			{
			$parentFolder = $file;
			}

		while ($parentFolder->loaded())
			{
			if ($parentFolder->permissionId)
				{
				if (! $this->page->getPermissions()->hasPermission($parentFolder->permissionId))
					{
					return false;
					}
				}
			$parentFolder = $parentFolder->parentFolder;
			}

		return true;
		}

	public function listFolders(\App\Table\PhotoFolder $folders, \App\Record\PhotoFolder $parentFolder) : \PHPFUI\Table
		{
		$container = new \PHPFUI\Table();

		$container->setHeaders(['Folder', 'Cut' => 'Cut &nbsp; &nbsp; &nbsp;']);
		$container->addColumnAttribute('Cut', ['class' => 'float-right']);

		$buttonGroup = new \PHPFUI\HTML5Element('div');
		$buttonGroup->addClass('clearfix');

		$permission = 'Add Photo Folder';

		if ($this->page->isAuthorized($permission))
			{
			$addFolderButton = new \PHPFUI\Button($permission);
			$addFolderButton->addClass('secondary');
			$this->addFolderModal($addFolderButton, $parentFolder);
			$buttonGroup->add($addFolderButton);
			}

		if ($parentFolder->loaded())
			{

			if ($this->page->isAuthorized('Add Photo'))
				{
				$addPhotoButton = new \PHPFUI\Button('Add Photo');
				$addPhotoButton->addClass('success');
				$this->addPhotoModal($addPhotoButton, $parentFolder);
				$buttonGroup->add($addPhotoButton);
				}

			$permission = 'Edit Photo Folder';

			if ($this->page->isAuthorized($permission))
				{
				$renameFolderButton = new \PHPFUI\Button($permission);
				$renameFolderButton->addClass('warning');
				$this->addEditFolderModal($renameFolderButton, $parentFolder);
				$buttonGroup->add($renameFolderButton);
				}
			}
		else
			{
			if ($this->page->isAuthorized('Add Photo'))
				{
				$addPhotoButton = new \PHPFUI\Button('Add Photo');
				$addPhotoButton->addClass('success');
				$addPhotoButton->setConfirm('You can only add photos to folders. Create or choose a folder first');
				$buttonGroup->add($addPhotoButton);
				}
			}

		if ($this->movePhoto || $this->moveFolder)
			{
			$cutButton = new \PHPFUI\Submit('Cut');
			$cutButton->addClass('alert');
			$cutButton->addClass('float-right');
			$buttonGroup->add($cutButton);
			}

		$container->add($buttonGroup);

		$cuts = \App\Model\Session::getPhotoCuts();

		$photoFolderTable = new \App\Table\PhotoFolder();

		foreach($folders->getRecordCursor() as $folder)
			{
			if (! $this->hasPermission($folder))
				{
				continue;
				}
			$row = [];
			$row['Folder'] = new \PHPFUI\Link('/Photo/browse/' . $folder->photoFolderId, $folder->photoFolder, false);

			if (! $photoFolderTable->folderCount($folder))
				{
				$row['Cut'] = new \PHPFUI\FAIcon('fas', 'trash-alt', '/Photo/deleteFolder/' . $folder->photoFolderId);
				}
			elseif (! isset($cuts[0 - $folder->photoFolderId]) && $this->moveFolder)
				{
				$cb = new \PHPFUI\Input\CheckBox('cutFolder[]', '', $folder->photoFolderId);
				$row['Cut'] = $cb;
				}

			$container->addRow($row);
			}

		return $container;
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

		$this->cuts = \App\Model\Session::getPhotoCuts();

		$view->addCustomColumn('uploaded', static fn (array $photo) => \date('Y-m-d', \strtotime((string)$photo['uploaded'])));
		$view->addCustomColumn('taken', static fn (array $photo) => $photo['taken'] ? \date('D M j, Y, g:i a', \strtotime((string)$photo['taken'])) : '');
		$view->addCustomColumn(
			'photo',
			static function(array $photo)
			{
			$name = empty($photo['photo']) ? $photo['photoId'] : $photo['photo'];

			return new \PHPFUI\Link('/Photo/view/' . $photo['photoId'], $name, false);
			}
		);

		$headers = ['photo', 'taken', 'uploaded'];
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

	private function addEditFolderModal(\PHPFUI\HTML5Element $modalLink, \App\Record\PhotoFolder $photoFolder) : void
		{
		$submit = new \PHPFUI\Submit();

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			unset($_POST['photoFolderId']);
			$photoFolder->setFrom($_POST);
			$photoFolder->update();
			$this->page->redirect();
			}

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Edit Photo Folder');
		$hidden = new \PHPFUI\Input\Hidden('photoFolderId', (string)$photoFolder->photoFolderId);
		$fieldSet->add($hidden);
		$folderName = new \PHPFUI\Input\Text('photoFolder', 'Folder Name', $photoFolder->photoFolder);
		$folderName->setRequired();
		$fieldSet->add($folderName);

		$permissionGroupPicker = new \App\UI\PermissionGroupPicker($this->page, 'permissionId', 'Optional Permission Group Restriction', $photoFolder->permission);
		$fieldSet->add($permissionGroupPicker->getEditControl());

		$form->add($fieldSet);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function addFolderModal(\PHPFUI\HTML5Element $modalLink, \App\Record\PhotoFolder $parentFolder) : void
		{
		$permission = 'Add Photo Folder';
		$submit = new \PHPFUI\Submit($permission);

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			$photoFolder = new \App\Record\PhotoFolder();
			$photoFolder->setFrom($_POST);
			$photoFolder->insert();
			$this->page->redirect();
			}

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('New Folder Name');
		$hidden = new \PHPFUI\Input\Hidden('parentFolderId', (string)$parentFolder->photoFolderId);
		$fieldSet->add($hidden);
		$folderName = new \PHPFUI\Input\Text('photoFolder', 'New Folder Name');
		$folderName->setRequired();
		$fieldSet->add($folderName);

		$permissionGroupPicker = new \App\UI\PermissionGroupPicker($this->page, 'permissionId', 'Optional Permission Group Restriction');
		$fieldSet->add($permissionGroupPicker->getEditControl());

		$form->add($fieldSet);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function addPhotoModal(\PHPFUI\HTML5Element $modalLink, \App\Record\PhotoFolder $photoFolder) : void
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
				'photoFolderId' => $photoFolder->photoFolderId,
				'photo' => $_POST['photo'] ?? '',
				'memberId' => $this->signedInMember,
				'public' => $_POST['public'] ?? 0,
			]);
			$photoId = $photo->insert();
			$photo->reload();

			if ($this->photoFiles->upload((string)$photoId, 'file', $_FILES, $fileTypes))
				{
				$photo->extension = $this->photoFiles->getExtension();

				if (empty($photo->photo))
					{
					$photo->photo = \substr($this->photoFiles->getUploadName(), 0, \strpos($this->photoFiles->getUploadName(), '.'));
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
		$caption = new \PHPFUI\Input\Text('photo', 'Photo Caption');
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

	/**
	 * @param array<int> $photo
	 */
	private function getCut(array $photo) : string
		{
		if (! isset($this->cuts[$photo['photoId']]) && ($photo['memberId'] == $this->signedInMember || $this->movePhoto))
			{
			return new \PHPFUI\Input\CheckBox('cut[]', '', $photo['photoId']);
			}

		return '';
		}
	}
