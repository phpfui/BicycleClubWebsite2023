<?php

namespace App\WWW;

class Photo extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\PhotoFolder $photoFolderTable;

	private readonly \App\Table\Photo $photoTable;

	private readonly \App\Table\PhotoTag $photoTagTable;

	private readonly \App\View\Photo $photoView;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->photoTable = new \App\Table\Photo();
		$this->photoTagTable = new \App\Table\PhotoTag();
		$this->photoFolderTable = new \App\Table\PhotoFolder();
		$this->photoView = new \App\View\Photo($this->page);
		}

	public function browse(\App\Record\PhotoFolder $photoFolder = new \App\Record\PhotoFolder()) : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('Browse Photos'))
			{
			$photoFolder->photoFolderId ??= 0;

			$this->page->addPageContent($this->photoView->getBreadCrumbs('/Photo/browse/', $photoFolder->photoFolderId));

			$this->photoFolderTable->setWhere(new \PHPFUI\ORM\Condition('parentId', $photoFolder->photoFolderId))->setOrderBy('photoFolder');
			$this->page->addPageContent($this->photoView->clipboard($photoFolder->photoFolderId));
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$form->setAttribute('action', '/Photo/cut');
			$form->add($this->photoView->listFolders($this->photoFolderTable, $photoFolder->photoFolderId));

			$this->photoTable->setWhere(new \PHPFUI\ORM\Condition('photoFolderId', $photoFolder->photoFolderId));
			$form->add($this->photoView->listPhotos($this->photoTable, true, $photoFolder->photoFolderId));
			$this->page->addPageContent($form);
			}
		}

	public function cut() : void
		{
		$url = $_SERVER['HTTP_REFERER'] ?? '';

		if ($url)
			{
			$photos = [];

			foreach ($_POST['cut'] ?? [] as $photoId)
				{
				$photo = new \App\Record\Photo($photoId);

				if (! $photo->empty() && ($photo->memberId == \App\Model\Session::signedInMemberId() || $this->page->isAuthorized('Move Photo')))
					{
					$photos[] = $photoId;
					}
				}

			foreach ($_POST['cutFolder'] ?? [] as $photoFolderId)
				{
				$photoFolder = new \App\Record\PhotoFolder($photoFolderId);

				if (! $photoFolder->empty() && $this->page->isAuthorized('Move Folder'))
					{
					$photos[] = 0 - $photoFolderId;
					}
				}

			foreach ($photos as $photoId)
				{
				\App\Model\Session::photoCut($photoId);
				}

			if (\count($photos))
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

	public function d() : void
		{
		$this->Browse();
		}

	public function delete(\App\Record\Photo $photo = new \App\Record\Photo()) : void
		{
		if (! $photo->empty() && ($photo->memberId == \App\Model\Session::signedInMemberId() || $this->page->isAuthorized('Delete Photo')))
			{
			$url = '/Photo/browse/' . $photo->photoFolderId;
			$photo->delete();
			\App\Model\Session::setFlash('success', 'Photo deleted.');
			$this->page->redirect($url);
			}
		else
			{
			\App\Model\Session::setFlash('alert', 'Photo not found.');
			}
		}

	public function deleteFolder(\App\Record\PhotoFolder $photoFolder = new \App\Record\PhotoFolder()) : void
		{
		$url = '';

		if (! $photoFolder->empty() && $this->page->isAuthorized('Add Folder'))
			{
			if (! $this->photoFolderTable->folderCount($photoFolder))
				{
				\App\Model\Session::setFlash('success', "Folder {$photoFolder->photoFolder} deleted.");
				$url = '/Photo/browse/' . $photoFolder->parentId;
				$photoFolder->delete();
				}
			else
				{
				\App\Model\Session::setFlash('alert', "Folder {$photoFolder->photoFolder} is not empty.");
				}
			}
		else
			{
			\App\Model\Session::setFlash('alert', 'Folder not found.');
			}
		$this->page->redirect($url);
		}

	public function image(\App\Record\Photo $photo = new \App\Record\Photo()) : void
		{
		if (! $photo->empty() && ($photo->public || $this->page->isAuthorized('View Album Photo')))
			{
			$fileModel = new \App\Model\PhotoFiles();
			$fileModel->download($photo->photoId, $photo->extension);
			}

		exit;
		}

	public function inPhotos(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('In Photos'))
			{
			if ($member->empty() || ($member->memberId != \App\Model\Session::signedInMemberId() && ! $this->page->isAuthorized('View Member Photos')))
				{
				$member = new \App\Record\Member(\App\Model\Session::signedInMemberId());
				}
			else
				{
				$this->page->addPageContent($this->getMember($member));
				}
			$this->photoTable->addJoin('photoTag', 'photoId');
			$this->photoTable->setWhere(new \PHPFUI\ORM\Condition('photoTag.memberId', $member->memberId));
			$this->page->addPageContent($this->photoView->listPhotos($this->photoTable));
			}
		}

	public function mostTagged() : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('Most Tagged'))
			{
			$photos = $this->photoTagTable->mostTagged();
			$url = $this->page->isAuthorized('View Member Photos') ? '/Photo/inPhotos/' : '';
			$this->page->addPageContent($this->photoView->listMembers($photos, $url));
			}
		}

	public function myPhotos(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('My Photos'))
			{
			if ($member->empty() || ($member->memberId != \App\Model\Session::signedInMemberId() && ! $this->page->isAuthorized('View Member Photos')))
				{
				$member = new \App\Record\Member(\App\Model\Session::signedInMemberId());
				}
			else
				{
				$this->page->addPageContent($this->getMember($member));
				}
			$this->photoTable->setWhere(new \PHPFUI\ORM\Condition('memberId', $member->memberId));
			$this->page->addPageContent($this->photoView->listPhotos($this->photoTable));
			}
		}

	public function paste() : void
		{
		$url = $_SERVER['HTTP_REFERER'] ?? '';
		$photoFolderId = (int)($_POST['photoFolderId'] ?? 0);

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


			foreach ($pastes as $photoId)
				{
				\App\Model\Session::photoCut($photoId, false);

				if ($paste)
					{
					if ($photoId > 0)
						{
						$photo = new \App\Record\Photo($photoId);
						$photo->photoFolderId = $photoFolderId;
						$photo->update();
						}
					else
						{
						$photoFolder = new \App\Record\PhotoFolder(0 - $photoId);
						$originalPhotoFolderId = $photoFolder->photoFolderId;
						$photoFolder->parentId = $photoFolderId;
						$photoFolder->update();

						// loop through folders till we find root, if we find ourselves, then reset us to be parent of root.
						while ($photoFolder->parentId)
							{
							if ($originalPhotoFolderId == $photoFolder->parentId)
								{
								// infinite loop, set parent to root
								$photoFolder->parentId = 0;
								$photoFolder->update();
								}
							$photoFolder = new \App\Record\PhotoFolder($photoFolder->parentId);
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

		if ($this->page->addHeader('Find Photos'))
			{
			if (empty($_GET))
				{
				$photos = [];
				}
			else
				{
				$this->photoTable->search($_GET);
				}
			$this->page->addPageContent($this->photoView->getSearchButton($_GET, ! $this->photoTable->count()));
			$this->page->addPageContent($this->photoView->listPhotos($this->photoTable));
			$this->page->addPageContent($this->photoView->getSearchButton());
			}
		}

	public function taggers() : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('Top Taggers'))
			{
			$photos = $this->photoTagTable->topTaggers();
			$this->page->addPageContent($this->photoView->listMembers($photos));
			}
		}

	public function v() : void
		{
		$this->Browse();
		}

	public function view(\App\Record\Photo $photo = new \App\Record\Photo()) : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('View Photo'))
			{
			$this->page->addPageContent($this->photoView->getBreadCrumbs('/Photo/browse/', $photo->photoFolderId, $photo->photoId));
			$this->page->addPageContent($this->photoView->getImage($photo));
			$this->page->addPageContent($this->photoView->getInfo($photo));

			if ($this->page->isAuthorized('Photo Tags'))
				{
				$this->page->addPageContent($this->photoView->getTags($photo));
				}
			else
				{
				$this->page->addPageContent($this->photoView->listTags($photo));
				}

			if ($this->page->isAuthorized('Photo Comments'))
				{
				$this->page->addPageContent($this->photoView->getComments($photo));
				}
			}
		}

	private function getMember(\App\Record\Member $member) : \PHPFUI\SubHeader
		{
		$header = $member->empty() ? 'Member Not Found' : $member->fullName();

		return new \PHPFUI\SubHeader($header);
		}
	}
