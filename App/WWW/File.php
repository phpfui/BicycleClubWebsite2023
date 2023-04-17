<?php

namespace App\WWW;

class File extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\FileFolder $fileFolderTable;

	private readonly \App\Table\File $fileTable;

	private readonly \App\View\File $fileView;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->fileTable = new \App\Table\File();
		$this->fileFolderTable = new \App\Table\FileFolder();
		$this->fileView = new \App\View\File($this->page);
		}

	public function browse(\App\Record\FileFolder $fileFolder = new \App\Record\FileFolder()) : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('Browse Files'))
			{
			$fileFolder->fileFolderId ??= 0;

			$this->page->addPageContent($this->fileView->getBreadCrumbs('/File/browse/', $fileFolder->fileFolderId));

			$this->fileFolderTable->setWhere(new \PHPFUI\ORM\Condition('parentId', $fileFolder->fileFolderId))->addOrderBy('fileFolder');
			$this->page->addPageContent($this->fileView->clipboard($fileFolder->fileFolderId));
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$form->setAttribute('action', '/File/cut');
			$form->add($this->fileView->listFolders($this->fileFolderTable, $fileFolder->fileFolderId));

			$this->fileTable->setWhere(new \PHPFUI\ORM\Condition('fileFolderId', $fileFolder->fileFolderId));
			$form->add($this->fileView->listFiles($this->fileTable, true, $fileFolder->fileFolderId));
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

			foreach ($_POST['cutFolder'] ?? [] as $fileFolderId)
				{
				$fileFolder = new \App\Record\FileFolder($fileFolderId);

				if (! $fileFolder->empty() && $this->page->isAuthorized('Move Folder'))
					{
					$files[] = 0 - $fileFolderId;
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
			$url = '/File/browse/' . $file->fileFolderId;
			$file->delete();
			\App\Model\Session::setFlash('success', 'File deleted.');
			$this->page->redirect($url);
			}
		else
			{
			\App\Model\Session::setFlash('alert', 'File not found.');
			}
		}

	public function deleteFolder(\App\Record\FileFolder $fileFolder = new \App\Record\FileFolder()) : void
		{
		$url = '';

		if (! $fileFolder->empty() && $this->page->isAuthorized('Add Folder'))
			{
			if (! $this->fileFolderTable->folderCount($fileFolder))
				{
				\App\Model\Session::setFlash('success', "Folder {$fileFolder->fileFolder} deleted.");
				$url = '/File/browse/' . $fileFolder->parentId;
				$fileFolder->delete();
				}
			else
				{
				\App\Model\Session::setFlash('alert', "Folder {$fileFolder->fileFolder} is not empty.");
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
		if (! $file->loaded() || (! $file->public && ! \App\Model\Session::isSignedIn()))
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('File not found'));
			\http_response_code(404);

			return;
			}
		$fileModel = new \App\Model\FileFiles();
		$fileModel->download($file->fileId, $file->extension, $file->fileName . $file->extension);

		exit;
		}

	public function edit(\App\Record\File $file = new \App\Record\File()) : void
		{
		if ($this->page->addHeader('Edit File', '', $file->memberId == \App\Model\Session::signedInMemberId()))
			{
			if ($file->loaded())
				{
				$this->page->addPageContent($this->fileView->edit($file));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('File not found'));
				}
			}
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
			$this->fileTable->setWhere(new \PHPFUI\ORM\Condition('memberId', $member->memberId));
			$this->page->addPageContent($this->fileView->listFiles($this->fileTable));
			}
		}

	public function paste() : void
		{
		$url = $_SERVER['HTTP_REFERER'] ?? '';
		$fileFolderId = (int)($_POST['fileFolderId'] ?? 0);

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
						$file->fileFolderId = $fileFolderId;
						$file->update();
						}
					else
						{
						$fileFolder = new \App\Record\FileFolder(0 - $fileId);
						$originalFileFolderId = $fileFolder->fileFolderId;
						$fileFolder->parentId = $fileFolderId;
						$fileFolder->update();

						// loop through folders till we find root, if we find ourselves, then reset us to be parent of root.
						while ($fileFolder->parentId)
							{
							if ($originalFileFolderId == $fileFolder->parentId)
								{
								// infinite loop, set parent to root
								$fileFolder->parentId = 0;
								$fileFolder->update();
								}
							$fileFolder = new \App\Record\FileFolder($fileFolder->parentId);
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
			if (! empty($_GET))
				{
				$this->fileTable->search($_GET);
				}
			$this->page->addPageContent($this->fileView->getSearchButton($_GET, ! empty($_GET)));
			$this->page->addPageContent($this->fileView->listFiles($this->fileTable));
			$this->page->addPageContent($this->fileView->getSearchButton());
			}
		}

	private function getMember(\App\Record\Member $member) : \PHPFUI\SubHeader
		{
		$header = $member->empty() ? 'Member Not Found' : $member->fullName();

		return new \PHPFUI\SubHeader($header);
		}
	}
