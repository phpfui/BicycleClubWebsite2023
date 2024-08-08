<?php

namespace App\WWW;

class Video extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\Folder $folderTable;

	private readonly \App\Table\Video $table;

	private readonly \App\View\Video $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->table = new \App\Table\Video();
		$this->folderTable = new \App\Table\Folder();
		$this->view = new \App\View\Video($this->page);
		}

	public function add(\App\Record\Folder $folder = new \App\Record\Folder()) : void
		{
		if (! $this->view->hasPermission($folder) || ($folder->loaded() && \App\Enum\FolderType::VIDEO != $folder->folderType))
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Folder Not Found'));
			}
		elseif ($this->page->addHeader('Add Video'))
			{
			$callout = new \PHPFUI\Callout('info');
			$callout->add('You must first add the video information before you can upload the video.');
			$video = new \App\Record\Video();
			$this->page->addPageContent($this->view->edit($video));
			}
		}

	public function browse(\App\Record\Folder $folder = new \App\Record\Folder()) : void
		{
		$this->page->turnOffBanner();

		if (! $this->view->hasPermission($folder) || ($folder->loaded() && \App\Enum\FolderType::VIDEO != $folder->folderType))
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Folder Not Found'));
			}
		elseif ($this->page->addHeader('Browse Videos'))
			{
			$folder->folderId ??= 0;

			$this->page->addPageContent($this->view->getBreadCrumbs('/Video/browse', $folder));

			$condition = new \PHPFUI\ORM\Condition('folderType', \App\Enum\FolderType::VIDEO);
			$condition->and('parentFolderId', (int)$folder->folderId);
			$this->folderTable->setWhere($condition)->addOrderBy('name');
			$this->page->addPageContent($this->view->clipboard(
				$folder	/**
	 * @return array<int,int>
	 */
			));
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$form->setAttribute('action', '/Video/cut');
			$form->add($this->view->listFolders($this->folderTable, $folder));

			if ($folder->loaded())
				{
				$this->table->setWhere(new \PHPFUI\ORM\Condition('folderId', $folder->folderId));
				$form->add($this->view->list($this->table, true, $folder->folderId));
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
				$video = new \App\Record\Video($fileId);

				if (! $video->empty() && ($video->memberId == \App\Model\Session::signedInMemberId() || $this->page->isAuthorized('Move Video')))
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
				$this->view->cut($fileId);
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

	public function delete(\App\Record\Video $video = new \App\Record\Video()) : void
		{
		if (! $video->empty() && ($video->memberId == \App\Model\Session::signedInMemberId() || $this->page->isAuthorized('Edit Video')))
			{
			$url = '/Video/browse/' . $video->folderId;
			$video->delete();
			\App\Model\Session::setFlash('success', 'Video deleted.');
			$this->page->redirect($url);
			}
		else
			{
			\App\Model\Session::setFlash('alert', 'Video not found.');
			}
		}

	public function deleteFile(\App\Record\Video $video = new \App\Record\Video()) : void
		{
		if ($this->page->isAuthorized('Edit Video'))
			{
			if (! $video->empty())
				{
				$video->deleteFile();
				$video->update();
				$this->page->redirect('/Video/edit/' . $video->videoId);
				}
			}
		}

	public function deleteFolder(\App\Record\Folder $folder = new \App\Record\Folder()) : void
		{
		$this->view->deleteFolder('/Video/browse/', $folder);
		}

	public function edit(\App\Record\Video $video = new \App\Record\Video()) : void
		{
		if ($this->page->isAuthorized('Edit Video'))
			{
			if (! $video->empty())
				{
				$videoView = new \App\View\Video($this->page);
				$this->page->addPageContent($videoView->edit($video));
				}
			else
				{
				$this->page->addSubHeader("Video {$video->videoId} not found");
				}
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
				$this->view->cut($fileId, false);

				if ($paste)
					{
					if ($fileId > 0)
						{
						$video = new \App\Record\Video($fileId);
						$video->folderId = $folderId;
						$video->update();
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
		if ($this->page->addHeader('Find Videos'))
			{
			$this->page->addPageContent($view = new \App\View\VideoSearch($this->page));
			}
		}

	public function upload() : void
		{
		if ($this->page->addHeader('Edit Video'))
			{
			$config = new \Flow\Config();
			$config->setTempDir(PROJECT_ROOT . '/files/chunkUploader');
			$videoFile = new \Flow\File($config);

			if ('GET' === $_SERVER['REQUEST_METHOD'])
				{
				if ($videoFile->checkChunk())
					{
					\header('HTTP/1.1 200 Ok');
					}
				else
					{
					\header('HTTP/1.1 204 No Content');
					}
				}
			else
				{
				if ($videoFile->validateChunk())
					{
					$videoFile->saveChunk();
					}
				else
					{
					// error, invalid chunk upload request, retry
					\header('HTTP/1.1 400 Bad Request');
					}
				}

			if ($videoFile->validateFile())
				{
				$video = new \App\Record\Video($_POST['videoId']);
				$parts = \explode('.', $_POST['flowFilename']);
				$video->fileName = $video->videoId . '.' . \uniqid(more_entropy:true) . '.' . \array_pop($parts);
				$video->update();
				$videoFile->save($_SERVER['DOCUMENT_ROOT'] . '/video/' . $video->fileName);
				}
			}
		}

	public function view(\App\Record\Video $video = new \App\Record\Video()) : void
		{
		$this->page->turnOffBanner()->setPublic();

		if ($this->page->addHeader('View Video'))
			{
			if (! $video->empty() && ($video->public || \App\Model\Session::isSignedIn()))
				{
				$videoView = new \App\View\Video($this->page);
				$this->page->addPageContent($videoView->view($video));
				}
			else
				{
				$this->page->addSubHeader("Video {$video->videoId} not found");
				}
			}
		}
	}
