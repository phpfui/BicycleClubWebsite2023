<?php

namespace App\View;

class File
	{
	private readonly \App\Model\FileFiles $fileFiles;

	private ?\PHPFUI\Button $searchButton = null;

	private readonly int $signedInMember;

	private array $cuts = [];

	private bool $moveFile = false;

	private bool $moveFolder = false;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->fileFiles = new \App\Model\FileFiles();
		$this->signedInMember = \App\Model\Session::signedInMemberId();
		$this->moveFile = $page->isAuthorized('Move File');
		$this->moveFolder = $page->isAuthorized('Move Folder');
		}

	public function clipboard(int $fileFolderId) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$cuts = \App\Model\Session::getFileCuts();

		if ($fileFolderId && $cuts)
			{
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$form->setAttribute('action', '/File/paste');
			$form->add(new \PHPFUI\Input\Hidden('fileFolderId', (string)$fileFolderId));
			$fieldSet = new \PHPFUI\FieldSet('Pasteable Items');
			$multiSelect = new \PHPFUI\Input\MultiSelect('paste');
			$multiSelect->selectAll();

			foreach ($cuts as $fileId => $value)
				{
				if ($fileId < 0)
					{
					$fileFolder = new \App\Record\FileFolder(0 - $fileId);
					$name = $fileFolder->fileFolder;
					}
				else
					{
					$file = new \App\Record\File($fileId);
					$name = $file->file ?: $fileId;
					}
				$multiSelect->addOption($name, $fileId);
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
	public function getBreadCrumbs(string $url, int $fileFolderId, int $fileId = 0) : \PHPFUI\BreadCrumbs
		{
		$breadCrumbs = new \PHPFUI\BreadCrumbs();

		$folders = \App\Table\FileFolder::getFolders($fileFolderId);

		$breadCrumbs->addCrumb('All', '/File/browse');

		foreach ($folders as $folderId => $name)
			{
			$link = '';

			if ($folderId != $fileFolderId || $fileId)
				{
				$link = $url . $folderId;
				}
			$breadCrumbs->addCrumb($name, $link);
			}

		if ($fileId)
			{
			$file = new \App\Record\File($fileId);
			$breadCrumbs->addCrumb($file->file);
			}

		return $breadCrumbs;
		}

	public function edit(\App\Record\File $file) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$submit = new \PHPFUI\Submit('Save');

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			$file->setFrom($_POST);
			$file->update();

			if ($this->fileFiles->upload((string)$file->fileId, 'file', $_FILES, null))
				{
				$file->extension = $this->fileFiles->getExtension();

				$file->fileName = \substr($this->fileFiles->getUploadName(), 0, \strpos($this->fileFiles->getUploadName(), '.'));

				if (empty($file->file))
					{
					$file->file = $file->fileName;
					}
				$file->update();
				\App\Model\Session::setFlash('success', 'File updated');
				}
			else
				{
				\App\Model\Session::setFlash('success', 'Saved');
				}
			$this->page->redirect();
			}
		else
			{
			$form = $this->getEditForm($file);
			$form->add('<br>');
			$form->add($submit);
			$fieldSet = new \PHPFUI\FieldSet('File Information');
			$fieldSet->add($form);
			$container->add($fieldSet);
			}

		return $container;
		}

	public function getEditForm(\App\Record\File $file) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$publicField = new \PHPFUI\Input\CheckBoxBoolean('public', 'Allow Public Views', (bool)$file->public);
		$publicField->setToolTip('If checked, this file can be accessed by anyone with the correct link');
		$multiColumn = new \PHPFUI\MultiColumn($publicField);

		if (! $file->loaded())
			{
			$form->setAreYouSure(false);
			}
		else
			{
			$link = new \PHPFUI\Link($this->page->value('homePage') . '/File/download/' . $file->fileId, $file->fileName);

			if (! $file->public)
				{
				$link->addClass('hide');
				}
			$publicField->addAttribute('onclick', '$("#' . $link->getId() . '").toggleClass("hide");');
			$multiColumn->add($link);
			}

		$form->add($multiColumn);
		$caption = new \PHPFUI\Input\Text('file', 'File Description', $file->file);
		$caption->setToolTip('This description will also be shown in the folder list view.');
		$form->add($caption);

		if ($file->loaded())
			{
			$form->add(new \PHPFUI\Input\Hidden('fileId', (string)$file->fileId));
			$fileName = new \PHPFUI\Input\Text('fileName', 'File Name on Download', $file->fileName);
			$fileName->setToolTip('This will be the file name when downloaded.  Do not include an extension.');
			$form->add($fileName);
			$file = new \PHPFUI\Input\File($this->page, 'file', 'File To Update (if needed)');
			}
		else
			{
			$file = new \PHPFUI\Input\File($this->page, 'file', 'File To Add');
			$file->setRequired();
			}
		$form->add($file);

		return $form;
		}

	public function getSearchButton(array $parameters = [], bool $openOnPageLoad = true) : \PHPFUI\Button
		{
		if ($this->searchButton)
			{
			return $this->searchButton;
			}

		$this->searchButton = new \PHPFUI\Button('Search');

		$modal = new \PHPFUI\Reveal($this->page, $this->searchButton);
		$form = new \PHPFUI\Form($this->page);
		$form->add(new \PHPFUI\SubHeader('Search Files'));

		if ($openOnPageLoad)
			{
			$modal->showOnPageLoad();
			}

		if (! empty($parameters) && $openOnPageLoad)
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
			'file' => 'Description',
			'fileName' => 'File Name',
			'extension' => 'Extension',
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

	public function hasPermission(\App\Record\File | \App\Record\FileFolder $file) : bool
		{
		if ($file instanceof \App\Record\File)
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
			$parentFolder = $file->fileFolder;
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

	public function listFolders(\App\Table\FileFolder $folders, \App\Record\FileFolder $parentFolder) : \PHPFUI\Table
		{
		$container = new \PHPFUI\Table();

		if (! $parentFolder->loaded())
			{
			$container->setHeaders(['Folder', 'Cut' => 'Cut/Del']);
			$container->addColumnAttribute('Cut', ['class' => 'float-right']);
			}
		$buttonGroup = new \PHPFUI\HTML5Element('div');
		$buttonGroup->addClass('clearfix');

		$permission = 'Add File Folder';

		if ($this->page->isAuthorized($permission))
			{
			$addFolderButton = new \PHPFUI\Button($permission);
			$addFolderButton->addClass('secondary');
			$this->addFolderModal($addFolderButton, $parentFolder);
			$buttonGroup->add($addFolderButton);
			}

		if ($parentFolder->loaded())
			{

			if ($this->page->isAuthorized('Add File'))
				{
				$addFileButton = new \PHPFUI\Button('Add File');
				$addFileButton->addClass('success');
				$this->addFileModal($addFileButton, $parentFolder);
				$buttonGroup->add($addFileButton);
				}

			$permission = 'Edit File Folder';

			if ($this->page->isAuthorized($permission))
				{
				$renameFolderButton = new \PHPFUI\Button($permission);
				$renameFolderButton->addClass('warning');
				$this->addEditFolderModal($renameFolderButton, $parentFolder);
				$buttonGroup->add($renameFolderButton);
				}
			}

		if ($parentFolder->loaded() && ($this->moveFile || $this->moveFolder))
			{
			$cutButton = new \PHPFUI\Submit('Cut');
			$cutButton->addClass('alert');
			$cutButton->addClass('float-right');
			$buttonGroup->add($cutButton);
			}

		$container->add($buttonGroup);

		$cuts = \App\Model\Session::getFileCuts();

		$fileFolderTable = new \App\Table\FileFolder();

		foreach($folders->getRecordCursor() as $folder)
			{
			if (! $this->hasPermission($folder))
				{
				continue;
				}
			$row = [];
			$row['Folder'] = new \PHPFUI\Link('/File/browse/' . $folder->fileFolderId, $folder->fileFolder, false);

			if (! $fileFolderTable->folderCount($folder))
				{
				$row['Cut'] = new \PHPFUI\FAIcon('fas', 'trash-alt', '/File/deleteFolder/' . $folder->fileFolderId);
				}
			elseif ($parentFolder->loaded() && (! isset($cuts[0 - $folder->fileFolderId]) && $this->moveFolder))
				{
				$cb = new \PHPFUI\Input\CheckBox('cutFolder[]', '', $folder->fileFolderId);
				$row['Cut'] = $cb;
				}

			$container->addRow($row);
			}

		return $container;
		}

	public function listFiles(\App\Table\File $fileTable, bool $allowCut = false, int $fileFolderId = 0) : \App\UI\ContinuousScrollTable
		{
		$view = new \App\UI\ContinuousScrollTable($this->page, $fileTable);
		$deleter = new \App\Model\DeleteRecord($this->page, $view, $fileTable, 'Are you sure you want to permanently delete this file?');
		$view->addCustomColumn('del', $deleter->columnCallback(...));

		$this->cuts = \App\Model\Session::getFileCuts();

		$view->addCustomColumn('uploaded', static fn (array $file) => \date('Y-m-d', \strtotime((string)$file['uploaded'])));
		$view->addCustomColumn('file', static fn (array $file) => new \PHPFUI\Link('/File/edit/' . $file['fileId'], $file['file'], false));
		$view->addCustomColumn('fileName', static fn (array $file) => new \PHPFUI\Link('/File/download/' . $file['fileId'], $file['fileName'], false));
		$view->addCustomColumn('member', static function(array $file) { $member = new \App\Record\Member($file['memberId']);

return $member->fullName();});

		$headers = ['fileName' => 'Download', 'file' => 'Description', 'uploaded' => 'Uploaded'];
		$normalHeaders = ['member', 'del'];

		if ($allowCut)
			{
			$normalHeaders[] = 'cut';
			$view->addCustomColumn('cut', $this->getCut(...));
			}

		$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, $normalHeaders))->setSortableColumns(\array_keys($headers));

		return $view;
		}

	private function getCut(array $file) : string
		{
		if (! isset($this->cuts[$file['fileId']]) && ($file['memberId'] == $this->signedInMember || $this->moveFile))
			{
			return new \PHPFUI\Input\CheckBox('cut[]', '', $file['fileId']);
			}

		return '';
		}

	private function addFolderModal(\PHPFUI\HTML5Element $modalLink, \App\Record\FileFolder $parentFolder) : void
		{
		$permission = 'Add File Folder';
		$submit = new \PHPFUI\Submit($permission);

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			$fileFolder = new \App\Record\FileFolder();
			$fileFolder->setFrom($_POST);
			$fileFolder->insert();
			$this->page->redirect();
			}

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('New Folder Name');
		$hidden = new \PHPFUI\Input\Hidden('parentFolderId', (string)$parentFolder->fileFolderId);
		$fieldSet->add($hidden);
		$folderName = new \PHPFUI\Input\Text('fileFolder', 'New Folder Name');
		$folderName->setRequired();
		$fieldSet->add($folderName);

		$permissionGroupPicker = new \App\UI\PermissionGroupPicker($this->page, 'permissionId', 'Optional Permission Group Restriction');
		$fieldSet->add($permissionGroupPicker->getEditControl());

		$form->add($fieldSet);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function addFileModal(\PHPFUI\HTML5Element $modalLink, \App\Record\FileFolder $fileFolder) : void
		{
		$submit = new \PHPFUI\Submit('Add File');

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			$file = new \App\Record\File();
			$file->setFrom([
				'fileFolderId' => $fileFolder->fileFolderId,
				'file' => $_POST['file'] ?? '',
				'memberId' => $this->signedInMember,
				'public' => $_POST['public'] ?? 0,
			]);
			$fileId = $file->insert();

			if ($this->fileFiles->upload((string)$fileId, 'file', $_FILES, null))
				{
				$file->extension = $this->fileFiles->getExtension();

				$file->fileName = \substr($this->fileFiles->getUploadName(), 0, \strpos($this->fileFiles->getUploadName(), '.'));

				if (empty($file->file))
					{
					$file->file = $file->fileName;
					}
				$file->update();
				\App\Model\Session::setFlash('success', 'File uploaded');
				}
			else
				{
				$file->delete();
				\App\Model\Session::setFlash('alert', $this->fileFiles->getLastError());
				}
			$this->page->redirect();

			return;
			}

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$fieldSet = new \PHPFUI\FieldSet('Add File To This Folder');
		$form = $this->getEditForm(new \App\Record\File());
		$form->setAreYouSure(false);
		$form->add($modal->getButtonAndCancel($submit));
		$fieldSet->add($form);
		$modal->add($fieldSet);
		}

	private function addEditFolderModal(\PHPFUI\HTML5Element $modalLink, \App\Record\FileFolder $fileFolder) : void
		{
		$submit = new \PHPFUI\Submit();

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			unset($_POST['fileFolderId']);
			$fileFolder->setFrom($_POST);
			$fileFolder->update();
			$this->page->redirect();
			}

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Edit File Folder');
		$hidden = new \PHPFUI\Input\Hidden('fileFolderId', (string)$fileFolder->fileFolderId);
		$fieldSet->add($hidden);
		$folderName = new \PHPFUI\Input\Text('fileFolder', 'Folder Name', $fileFolder->fileFolder);
		$folderName->setRequired();
		$fieldSet->add($folderName);

		$permissionGroupPicker = new \App\UI\PermissionGroupPicker($this->page, 'permissionId', 'Optional Permission Group Restriction', $fileFolder->permission);
		$fieldSet->add($permissionGroupPicker->getEditControl());

		$form->add($fieldSet);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
