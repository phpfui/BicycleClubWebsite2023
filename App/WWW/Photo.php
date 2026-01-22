<?php

namespace App\WWW;

class Photo extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\Folder $folderTable;

	private readonly \App\Table\PhotoTag $photoTagTable;

	private readonly \App\Table\Photo $table;

	private readonly \App\View\Photo $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->table = new \App\Table\Photo();
		$this->photoTagTable = new \App\Table\PhotoTag();
		$this->folderTable = new \App\Table\Folder();
		$this->view = new \App\View\Photo($this->page);
		}

	public function browse(\App\Record\Folder $folder = new \App\Record\Folder()) : void
		{
		$this->page->turnOffBanner();

		if (! $this->view->hasPermission($folder))
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Folder Not Found'));
			}
		elseif ($this->page->addHeader('Browse Photos'))
			{
			$folder->folderId ??= 0;

			$this->page->addPageContent($this->view->getBreadCrumbs('/Photo/browse', $folder));

			$condition = new \PHPFUI\ORM\Condition('folderType', \App\Enum\FolderType::PHOTO->value);
			$condition->and('parentFolderId', (int)$folder->folderId);
			$this->folderTable->setWhere($condition)->addOrderBy('name');
			$this->page->addPageContent($this->view->clipboard($folder));
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$form->setAttribute('action', '/Photo/cut');
			$form->add($this->view->listFolders($this->folderTable, $folder, ''));

			if ($folder->loaded())
				{
				$this->table->setWhere(new \PHPFUI\ORM\Condition('folderId', $folder->folderId));
				\App\Model\Session::clearPhotoAlbum();

				if ($this->table->count())
					{
					$form->add($this->getSlideShowButtonGroup($this->page->getController()->getUri(), $folder));
					}
				$form->add($this->view->listPhotos($this->table));
				}
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

				if (! $photo->empty() && ($photo->memberId === \App\Model\Session::signedInMemberId() || $this->page->isAuthorized('Move Photo')))
					{
					$photos[] = $photoId;
					}
				}

			foreach ($_POST['cutFolder'] ?? [] as $folderId)
				{
				if ($this->page->isAuthorized('Move Folder'))
					{
					$photos[] = -$folderId;
					}
				}

			foreach ($photos as $photoId)
				{
				$this->view->cut($photoId);
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

	public function delete() : void
		{
		$photo = new \App\Record\Photo($_POST['photoId'] ?? 0);

		if (! $photo->empty() && ($photo->memberId == \App\Model\Session::signedInMemberId() || $this->page->isAuthorized('Delete Photo')))
			{
			$photo->delete();
			$this->page->setResponse("{$photo->photoId}");
			}
		else
			{
			$this->page->setResponse('error');
			}
		}

	public function deleteFolder(\App\Record\Folder $folder = new \App\Record\Folder()) : void
		{
		$this->view->deleteFolder('/Photo/browse/', $folder);
		}

	public function image(string $id = '') : void
		{
		$parts = \explode('-', $id);
		$photo = new \App\Record\Photo((int)$parts[0]);

		if (! $photo->empty() && ($photo->public || $this->page->isAuthorized('View Album Photo')))
			{
			$fileModel = new \App\Model\PhotoFiles();
			$fileModel->download($photo->photoId, $photo->extension);
			}
		else
			{
			\http_response_code(404);
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
			$this->table->addJoin('photoTag');
			$this->table->setWhere(new \PHPFUI\ORM\Condition('photoTag.memberId', $member->memberId));
			\App\Model\Session::clearPhotoAlbum();

			if ($this->table->count())
				{
				$this->page->addPageContent($this->getSlideShowButtonGroup($this->page->getController()->getUri()));
				}
			$this->page->addPageContent($this->view->listPhotos($this->table));
			}
		}

	public function mostTagged() : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('Most Tagged'))
			{
			$photos = $this->photoTagTable->mostTagged();
			$url = $this->page->isAuthorized('View Member Photos') ? '/Photo/inPhotos/' : '';
			$this->page->addPageContent($this->view->listMembers($photos, $url));
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
			$this->table->setWhere(new \PHPFUI\ORM\Condition('memberId', $member->memberId));
			\App\Model\Session::clearPhotoAlbum();

			if ($this->table->count())
				{
				$this->page->addPageContent($this->getSlideShowButtonGroup($this->page->getController()->getUri()));
				}
			$this->page->addPageContent($this->view->listPhotos($this->table));
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


			foreach ($pastes as $photoId)
				{
				$this->view->cut($photoId, false);

				if ($paste)
					{
					if ($photoId > 0)
						{
						$photo = new \App\Record\Photo($photoId);
						$photo->folderId = $folderId;
						$photo->update();
						}
					else
						{
						$folder = new \App\Record\Folder(-$photoId);
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
							$folder = new \App\Record\Folder($folder->parentFolderId);
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
			$showSearch = true;
			$searchFields = [
				'description' => 'Caption',
				'photoTag' => 'Tag',
				'photoComment' => 'Comment',
			];

			if (($_GET['submit'] ?? '') == 'Search')
				{
				$showSearch = false;
				$this->table->search(\array_intersect_key($_GET, $searchFields));
				}
			$this->page->addPageContent($this->view->getSearchButton($this->table->count(), $searchFields, $_GET, $showSearch));

			if (! $showSearch)
				{
				\App\Model\Session::clearPhotoAlbum();

				if ($this->table->count())
					{
					$this->page->addPageContent($this->getSlideShowButtonGroup($this->page->getController()->getUri()));
					}
				$this->page->addPageContent($this->view->listPhotos($this->table, false));
				$this->page->addPageContent($this->view->getSearchButton($this->table->count(), $searchFields, $_GET, $showSearch));
				}
			}
		}

	public function slideShow(\App\Record\Photo $photo = new \App\Record\Photo()) : void
		{
		$url = '';

		if ($_GET['url'] ?? false)
			{
			$url = $_GET['url'];
			$this->page->addPageContent(new \PHPFUI\Button('Back', $url));
			}
		$photos = \App\Model\Session::getPhotoAlbum();

		if (\count($photos))
			{
			if (! $photo->loaded())
				{
				$photo = new \App\Record\Photo(0);
				}

			if ($url)
				{
				$url = '?url=' . $url;
				}
			$this->page->addPageContent($this->view->getImage($photo, '/Photo/slideShow/~photoId~' . $url));
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('No active slideshow'));
			}
		}

	public function slideTable(\App\Record\Folder $photoFolder = new \App\Record\Folder()) : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('Slide Table'))
			{
			if ($photoFolder->loaded())
				{
				$this->page->addPageContent($this->view->slideTable($photoFolder));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Folder not found'));
				}
			}
		}

	public function taggers() : void
		{
		$this->page->turnOffBanner();

		if ($this->page->addHeader('Top Taggers'))
			{
			$photos = $this->photoTagTable->topTaggers();
			$this->page->addPageContent($this->view->listMembers($photos));
			}
		}

	public function v() : void
		{
		$this->Browse();
		}

	public function view(\App\Record\Photo $photo = new \App\Record\Photo()) : void
		{
		$this->page->turnOffBanner();

		if ($photo->empty())
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Photo Not Found'));

			return;
			}

		if ($this->page->addHeader('View Photo'))
			{
			$this->page->addPageContent($this->view->getBreadCrumbs('/Photo/browse', $photo->folder, true));
			$this->page->addPageContent($this->view->getImage($photo));
			$this->page->addPageContent($this->view->getInfo($photo));

			if ($this->page->isAuthorized('Photo Tags'))
				{
				$this->page->addPageContent($this->view->getTags($photo));
				}
			else
				{
				$this->page->addPageContent($this->view->listTags($photo));
				}

			if ($this->page->isAuthorized('Photo Comments'))
				{
				$this->page->addPageContent($this->view->getComments($photo));
				}
			}
		}

	private function getMember(\App\Record\Member $member) : \PHPFUI\SubHeader
		{
		$header = $member->empty() ? 'Member Not Found' : $member->fullName();

		return new \PHPFUI\SubHeader($header);
		}

	private function getSlideShowButtonGroup(string $url, ?\App\Record\Folder $folder = null) : \PHPFUI\ButtonGroup
		{
		if ($_GET['url'] ?? false)
			{
			$url = $_GET['url'];
			}

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->add(new \PHPFUI\Button('Slide Show', '/Photo/slideShow?url=' . $url)->addClass('success'));

		if ($folder && $this->page->isAuthorized('Slide Table'))
			{
			$buttonGroup->add(new \PHPFUI\Button('Slide Table', '/Photo/slideTable/' . $folder->folderId . '?url=' . $url)->addClass('info'));
			}

		return $buttonGroup;
		}
	}
