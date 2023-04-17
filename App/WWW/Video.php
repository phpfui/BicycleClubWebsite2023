<?php

namespace App\WWW;

class Video extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		}

	public function addVideo() : void
		{
		if ($this->page->addHeader('Add Video'))
			{
			$videoView = new \App\View\Video($this->page);
			$video = new \App\Record\Video();
			$this->page->addPageContent($videoView->edit($video));
			}
		}

	public function all() : void
		{
		$this->page->setPublic();

		if ($this->page->addHeader('All Videos'))
			{
			$videoView = new \App\View\Video($this->page);
			$this->page->addPageContent($videoView->list());
			}
		}

	public function deleteFile(\App\Record\Video $video = new \App\Record\Video()) : void
		{
		if ($this->page->isAuthorized('Edit Video'))
			{
			if (! $video->empty())
				{
				$fileName = $_SERVER['DOCUMENT_ROOT'] . '/video/' . $video->fileName;

				if (\file_exists($fileName) && ! \is_dir($fileName))
					{
					\unlink($fileName);
					}
				$video->fileName = '';
				$video->update();
				$this->page->redirect('/Video/edit/' . $video->videoId);
				}
			}
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

	public function search() : void
		{
		if ($this->page->addHeader('Find Videos'))
			{
			$this->page->addPageContent($view = new \App\View\VideoSearch($this->page));
			}
		}

	public function types(string $table = '') : void
		{
		if ($this->page->addHeader('Video Types'))
			{
			$view = new \App\UI\TableEditor($this->page, 'VideoType');
			$view->setHeaders(['name' => 'Name', 'delete' => 'Del']);
			$this->page->addPageContent($view->edit());
			}
		}

	public function upload() : void
		{
		if ($this->page->isAuthorized('Edit Video'))
			{
			$config = new \Flow\Config();
			$config->setTempDir(PROJECT_ROOT . '/files/chunkUploader');
			$file = new \Flow\File($config);

			if ('GET' === $_SERVER['REQUEST_METHOD'])
				{
				if ($file->checkChunk())
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
				if ($file->validateChunk())
					{
					$file->saveChunk();
					}
				else
					{
					// error, invalid chunk upload request, retry
					\header('HTTP/1.1 400 Bad Request');
					}
				}

			if ($file->validateFile())
				{
				$video = new \App\Record\Video($_POST['videoId']);
				$video->fileName = $_POST['flowFilename'];
				$video->update();
				$file->save($_SERVER['DOCUMENT_ROOT'] . '/video/' . $_POST['flowFilename']);
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
