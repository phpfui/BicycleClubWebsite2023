<?php

namespace App\WWW;

class File extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\Folder $folderTable;

	private readonly \App\Table\File $table;

	private readonly \App\View\File $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->table = new \App\Table\File();
		$this->folderTable = new \App\Table\Folder();
		$this->view = new \App\View\File($this->page);
		}

	public function browse(\App\Record\Folder $folder = new \App\Record\Folder()) : void
		{
		$this->page->turnOffBanner();

		if (! $this->view->hasPermission($folder) || ($folder->loaded() && \App\Enum\FolderType::FILE != $folder->folderType))
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Folder Not Found'));
			}
		elseif ($this->page->addHeader('Browse Files'))
			{
			$folder->folderId ??= 0;

			$this->page->addPageContent($this->view->getBreadCrumbs('/File/browse', $folder));

			$condition = new \PHPFUI\ORM\Condition('folderType', \App\Enum\FolderType::FILE->value);
			$condition->and('parentFolderId', (int)$folder->folderId);
			$this->folderTable->setWhere($condition)->addOrderBy('name');
			$this->page->addPageContent($this->view->clipboard(
				$folder	/**
	 * @return array<int,int>
	 */
			));
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$form->setAttribute('action', '/File/cut');
			$form->add($this->view->listFolders($this->folderTable, $folder));

			if ($folder->loaded())
				{
				$this->table->setWhere(new \PHPFUI\ORM\Condition('folderId', $folder->folderId));
				$form->add($this->view->listFiles($this->table, true, $folder->folderId));
				}
			$this->page->addPageContent($form);
			}
		}

	public function cut() : void
		{
		$url = $_SERVER['HTTP_REFERER'] ?? '';

		if ($url)
			{
			$files = [];

			foreach ($_POST['cut'] ?? [] as $fileId)
				{
				$file = new \App\Record\File($fileId);

				if (! $file->empty() && ($file->memberId == \App\Model\Session::signedInMemberId() || $this->page->isAuthorized('Move File')))
					{
					$files[] = $fileId;
					}
				}

			foreach ($_POST['cutFolder'] ?? [] as $folderId)
				{
				$folder = new \App\Record\Folder($folderId);

				if (! $folder->empty() && $this->page->isAuthorized('Move Folder'))
					{
					$files[] = 0 - $folderId;
					}
				}

			foreach ($files as $fileId)
				{
				\App\Model\Session::fileCut($fileId);
				}

			if (\count($files))
				{
				\App\Model\Session::setFlash('success', 'Items added to clipboard');
				}
			else
				{
				\App\Model\Session::setFlash('alert', 'No items cut');
				}

			$this->page->redirect($url);
			}
		}

	public function delete(\App\Record\File $file = new \App\Record\File()) : void
		{
		if (! $file->empty() && ($file->memberId == \App\Model\Session::signedInMemberId() || $this->page->isAuthorized('Delete File')))
			{
			$url = '/File/browse/' . $file->folderId;
			$file->delete();
			\App\Model\Session::setFlash('success', 'File deleted.');
			$this->page->redirect($url);
			}
		else
			{
			\App\Model\Session::setFlash('alert', 'File not found.');
			}
		}

	public function deleteFolder(\App\Record\Folder $folder = new \App\Record\Folder()) : void
		{
		$url = '';

		if (! $folder->empty() && $this->page->isAuthorized('Delete File Folder'))
			{
			if (! $this->folderTable->folderCount($folder))
				{
				\App\Model\Session::setFlash('success', "Folder {$folder->name} deleted.");
				$url = '/File/browse/' . $folder->parentFolderId;
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

	public function download(\App\Record\File $file = new \App\Record\File()) : void
		{
		if (! $file->loaded())
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('File not found'));
			\http_response_code(404);

			return;
			}

		if (! $this->view->hasPermission($file))
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('File Restricted'));
			\http_response_code(401);

			return;
			}

		$fileModel = new \App\Model\FileFiles();
		$fileModel->download($file->fileId, $file->extension, $file->fileName . $file->extension);

		exit;
		}

	public function edit(\App\Record\File $file = new \App\Record\File()) : void
		{
		if ($this->page->addHeader('Edit File', '', $this->view->hasPermission($file)))
			{
			if ($file->loaded())
				{
				$this->page->addPageContent($this->view->edit($file));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('File not found'));
				}
			}
		}

	public function gearCSV() : void
		{
		$model = new \App\Model\GearCalculator($_GET);
		$model->csv();
		}

	public function gearPrint() : void
		{
		$model = new \App\Model\GearCalculator($_GET);
		$model->print();
		}

	public function myFiles(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('My Files'))
			{
			if ($member->empty() || ($member->memberId != \App\Model\Session::signedInMemberId() && ! $this->page->isAuthorized('View Member Files')))
				{
				$member = new \App\Record\Member(\App\Model\Session::signedInMemberId());
				}
			else
				{
				$this->page->addPageContent($this->getMember($member));
				}
			$this->table->setWhere(new \PHPFUI\ORM\Condition('memberId', $member->memberId));
			$this->page->addPageContent($this->view->listFiles($this->table));
			}
		}

	public function paste() : void
		{
		$url = $_SERVER['HTTP_REFERER'] ?? '';
		$folderId = (int)($_POST['folderId'] ?? 0);

		if ($url && \App\Model\Session::checkCSRF())
			{
			$paste = ($_POST['submit'] ?? 'Paste') == 'Paste';
			$pastes = $_POST['paste'] ?? [];

			if (\is_countable($pastes) ? \count($pastes) : 0)
				{
				\App\Model\Session::setFlash('success', (\is_countable($pastes) ? \count($pastes) : 0) . ' items ' . ($paste ? 'pasted.' : 'uncut.'));
				}
			else
				{
				\App\Model\Session::setFlash('alert', 'No items selected.');
				}


			foreach ($pastes as $fileId)
				{
				\App\Model\Session::fileCut($fileId, false);

				if ($paste)
					{
					if ($fileId > 0)
						{
						$file = new \App\Record\File($fileId);
						$file->folderId = $folderId;
						$file->update();
						}
					else
						{
						$folder = new \App\Record\Folder(0 - $fileId);
						$originalfolderId = $folder->folderId;
						$folder->parentFolderId = $folderId;
						$folder->update();

						// loop through folders till we find root, if we find ourselves, then reset us to be parent of root.
						while ($folder->parentFolderId)
							{
							if ($originalfolderId == $folder->parentFolderId)
								{
								// infinite loop, set parent to root
								$folder->parentFolderId = 0;
								$folder->update();
								}
							$folder = $folder->parentFolder;
							}
						}
					}
				}
			$this->page->redirect($url);
			}
		}

	public function search() : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('Find Files'))
			{
			$showSearch = true;
			$searchFields = [
				'description' => 'Description',
				'fileName' => 'File Name',
				'extension' => 'Extension',
			];

			// need to check if in permissioned folder
			if (($_GET['submit'] ?? '') == 'Search')
				{
				$showSearch = false;
				$this->table->search($_GET);
				}
			$this->page->addPageContent($this->view->getSearchButton($this->table->count(), $searchFields, $_GET, $showSearch));

			if (! $showSearch)
				{
				$this->page->addPageContent($this->view->listFiles($this->table));
				$this->page->addPageContent($this->view->getSearchButton($this->table->count(), $searchFields, $_GET, $showSearch));
				}
			}
		}

	private function getMember(\App\Record\Member $member) : \PHPFUI\SubHeader
		{
		$header = $member->empty() ? 'Member Not Found' : $member->fullName();

		return new \PHPFUI\SubHeader($header);
		}
	}
