<?php

namespace App\View;

abstract class Folder
	{
	protected bool $allowCuts = true;

	protected string $browseSection = 'browse';

	protected readonly string $className;

	/**
	 * @var array<int,int>
	 */
	protected array $cuts = [];

	protected bool $editItem;

	protected \App\Enum\FolderType $folderType = \App\Enum\FolderType::PHOTO;

	protected string $itemName;

	protected bool $moveFolder;

	protected bool $moveItem;

	protected ?\PHPFUI\Button $searchButton = null;

	protected readonly int $signedInMember;

	protected readonly string $type;

	public function __construct(protected readonly \App\View\Page $page, string $className)
		{
		$parts = \explode('\\', $className);
		$this->className = \array_pop($parts);
		$this->setItemName($this->className);
		$this->itemName = $this->className;
		$this->type = \strtolower($this->className);
		$this->signedInMember = \App\Model\Session::signedInMemberId();

		switch ($this->className)
			{
			case 'File':
				$this->folderType = \App\Enum\FolderType::FILE;

				break;

			case 'Photo':
				$this->folderType = \App\Enum\FolderType::PHOTO;

				break;

			case 'Store':
				$this->folderType = \App\Enum\FolderType::STORE;

				break;

			case 'Video':
				$this->folderType = \App\Enum\FolderType::VIDEO;

				break;
			}
		}

	public function clipboard(\App\Record\Folder $folder) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! $this->allowCuts)
			{
			return $container;
			}

		$cuts = $this->getCuts();

		if ($cuts)
			{
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$form->setAttribute('action', "/{$this->className}/paste");
			$form->add(new \PHPFUI\Input\Hidden('folderId', (string)$folder->folderId));
			$fieldSet = new \PHPFUI\FieldSet('Pasteable Items');
			$multiSelect = new \PHPFUI\Input\MultiSelect('paste');
			$multiSelect->selectAll();
			$itemType = '\\App\\Record\\' . $this->className;

			foreach ($cuts as $itemId => $value)
				{
				if ($itemId < 0)
					{
					$folder = new \App\Record\Folder(0 - $itemId);
					$name = $folder->name;
					$multiSelect->addOption('Folder: ' . $name, (string)$itemId);
					}
				else
					{
					$file = new $itemType($itemId);
					$name = $file->description ?: $itemId;

					if ($folder->folderId)
						{
						$multiSelect->addOption($this->className . ': ' . $name, (string)$itemId);
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

	public function deleteFolder(string $url, \App\Record\Folder $folder = new \App\Record\Folder()) : void
		{
		if (! $folder->empty() && $this->page->isAuthorized("Delete {$this->itemName} Folder"))
			{
			if (! $folder->childCount())
				{
				\App\Model\Session::setFlash('success', "Folder {$folder->name} deleted.");
				$url .= $folder->parentFolderId;
				$folder->delete();
				}
			else
				{
				\App\Model\Session::setFlash('alert', "Folder {$folder->name} is not empty.");
				}
			}
		else
			{
			\App\Model\Session::setFlash('alert', 'Folder not found.');
			}
		$this->page->redirect($url);
		}

	/**
	 * Get standard folder breadcrumbs
	 *
	 * @param string $url / and $folder->folderId will be appended
	 */
	public static function getBreadCrumbs(string $url, \App\Record\Folder $folder, bool $linkLast = false) : \PHPFUI\BreadCrumbs
		{
		$breadCrumbs = new \PHPFUI\BreadCrumbs();

		$folders = \App\Table\Folder::getParentFolders($folder->folderId ?? 0);

		$breadCrumbs->addCrumb('All', $url);

		foreach ($folders as $folderId => $name)
			{
			$link = '';

			if ($folder->folderId != $folderId || $linkLast)
				{
				$link = $url . '/' . $folderId;
				}
			$breadCrumbs->addCrumb($name, $link);
			}

		if ($linkLast)
			{
			$breadCrumbs->addCrumb('');
			}

		return $breadCrumbs;
		}

	/**
	 * @param array<string,string> $searchFields
	 * @param array<string,string> $parameters
	 */
	public function getSearchButton(int $count, array $searchFields, array $parameters = [], bool $openOnPageLoad = true) : \PHPFUI\Button
		{
		if ($this->searchButton)
			{
			return $this->searchButton;
			}

		$this->searchButton = new \PHPFUI\Button('Search');

		$modal = new \PHPFUI\Reveal($this->page, $this->searchButton);
		$form = new \PHPFUI\Form($this->page);
		$form->add(new \PHPFUI\SubHeader('Search ' . $this->className . 's'));

		if ($openOnPageLoad)
			{
			$modal->showOnPageLoad();
			}

		if (! $count && $openOnPageLoad)
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->addClass('small');
			$callout->add('No matches found');
			$form->add($callout);
			}
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('p', $parameters['p'] ?? 0));
		$form->setAttribute('method', 'get');

		foreach ($searchFields as $field => $name)
			{
			$form->add(new \PHPFUI\Input\Text($field, $name, $parameters[$field] ?? ''));
			}

		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $this->searchButton;
		}

	public function hasPermission(\App\Record\Photo | \App\Record\File | \App\Record\Folder $file) : bool
		{
		if (! $file instanceof \App\Record\Folder)
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
			$parentFolder = $file->folder;
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

	public function listFolders(\App\Table\Folder $folderTable, \App\Record\Folder $parentFolder, ?string $addButtonName = '') : \PHPFUI\Table
		{
		$container = new \PHPFUI\Table();

		$container->setHeaders(['Folder', 'Cut' => $this->allowCuts ? 'Cut/Del' : 'Del']);
		$container->addColumnAttribute('Cut', ['class' => 'float-right']);
		$buttonGroup = new \PHPFUI\HTML5Element('div');
		$buttonGroup->addClass('clearfix');

		$permission = 'Add ' . $this->className . ' Folder';

		if ($this->page->isAuthorized($permission))
			{
			$addFolderButton = new \PHPFUI\Button($permission);
			$addFolderButton->addClass('secondary');
			$this->addFolderModal($addFolderButton, $parentFolder);
			$buttonGroup->add($addFolderButton);
			}

		if (null !== $addButtonName)
			{
			$addButtonName = $addButtonName ?: $this->className;
			}

		if ($parentFolder->loaded())
			{
			if (null !== $addButtonName && $this->page->isAuthorized('Add ' . $addButtonName))
				{
				$addFileButton = new \PHPFUI\Button('Add ' . $addButtonName);
				$addFileButton->addClass('success');
				$this->addModal($addFileButton, $parentFolder);
				$buttonGroup->add($addFileButton);
				}

			$permission = 'Edit ' . $this->className . ' Folder';

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
			if (null !== $addButtonName && $this->page->isAuthorized('Add ' . $addButtonName))
				{
				$addFileButton = new \PHPFUI\Button('Add ' . $addButtonName);
				$addFileButton->addClass('success');
				$addFileButton->setConfirm('You can only add ' . $addButtonName . 's to folders. Create or choose a folder first');
				$buttonGroup->add($addFileButton);
				}
			}

		if ($this->allowCuts && ($this->moveItem || $this->moveFolder))
			{
			$cutButton = new \PHPFUI\Submit('Cut');
			$cutButton->addClass('alert');
			$cutButton->addClass('float-right');
			$buttonGroup->add($cutButton);
			}

		$container->add($buttonGroup);

		$cuts = $this->getCuts();

		foreach($folderTable->getRecordCursor() as $folder)
			{
			if (! $this->hasPermission($folder))
				{
				continue;
				}
			$row = [];
			$row['Folder'] = new \PHPFUI\Link('/' . $this->className . '/' . $this->browseSection . '/' . $folder->folderId, $folder->name, false);

			if (! $folder->childCount())
				{
				$row['Cut'] = new \PHPFUI\FAIcon('fas', 'trash-alt', '/' . $this->className . '/deleteFolder/' . $folder->folderId);
				}
			elseif ($this->allowCuts && (! isset($cuts[0 - $folder->folderId]) && $this->moveFolder))
				{
				$cb = new \PHPFUI\Input\CheckBox('cutFolder[]', '', $folder->folderId);
				$row['Cut'] = $cb;
				}

			$container->addRow($row);
			}

		return $container;
		}

	public function setBrowseSection(string $section) : self
		{
		$this->browseSection = $section;

		return $this ;
		}

	public function setItemName(string $itemName) : self
		{
		$this->itemName = $itemName;
		$this->moveItem = $this->page->isAuthorized('Move ' . $this->itemName);
		$this->editItem = $this->page->isAuthorized('Edit ' . $this->itemName);
		$this->moveFolder = $this->page->isAuthorized("Move {$this->itemName} Folder");

		return $this;
		}

	protected function addEditFolderModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Folder $folder) : void
		{
		$submit = new \PHPFUI\Submit();

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			unset($_POST['folderId']);
			$folder->setFrom($_POST);
			$folder->update();
			$this->page->redirect();
			}

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Edit ' . $this->className . ' Folder');
		$hidden = new \PHPFUI\Input\Hidden('folderId', (string)$folder->folderId);
		$fieldSet->add($hidden);
		$folderName = new \PHPFUI\Input\Text('name', 'Folder Name', $folder->name);
		$folderName->setRequired();
		$fieldSet->add($folderName);

		$permissionGroupPicker = new \App\UI\PermissionGroupPicker($this->page, 'permissionId', 'Optional Permission Group Restriction', $folder->permission);
		$fieldSet->add($permissionGroupPicker->getEditControl());

		$form->add($fieldSet);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	protected function addFolderModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Folder $parentFolder) : void
		{
		$permission = 'Add ' . $this->className . ' Folder';
		$submit = new \PHPFUI\Submit($permission);

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			$folder = new \App\Record\Folder();
			$folder->setFrom($_POST);
			$folder->folderType = $this->folderType;
			$folder->insert();
			$this->page->redirect();
			}

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('New Folder Name');
		$hidden = new \PHPFUI\Input\Hidden('parentFolderId', (string)$parentFolder->folderId);
		$fieldSet->add($hidden);
		$folderName = new \PHPFUI\Input\Text('name', 'New Folder Name');
		$folderName->setRequired();
		$fieldSet->add($folderName);

		$permissionGroupPicker = new \App\UI\PermissionGroupPicker($this->page, 'permissionId', 'Optional Permission Group Restriction');
		$fieldSet->add($permissionGroupPicker->getEditControl());

		$form->add($fieldSet);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	abstract protected function addModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Folder $folder) : void;

	/**
	 * @param array<int> $item
	 */
	protected function getCut(array $item) : string
		{
		$id = $item[$this->type . 'Id'];

		if (! isset($this->cuts[$id]) && ($item['memberId'] == $this->signedInMember || $this->moveItem))
			{
			return new \PHPFUI\Input\CheckBox('cut[]', '', $id);
			}

		return '';
		}

	/**
	 * @return array<int,int>
	 */
	protected function getCuts() : array
		{
		return \App\Model\Session::getCuts($this->type);
		}
	}
