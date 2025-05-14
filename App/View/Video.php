<?php

namespace App\View;

class Video extends \App\View\Folder
	{
	private readonly \PHPFUI\ButtonGroup $buttonGroup;

	private static bool $editFile = false;

	public function __construct(\App\View\Page $page)
		{
		parent::__construct($page, __CLASS__);
		self::$editFile = $page->isAuthorized('Edit Video');
		$this->buttonGroup = new \PHPFUI\ButtonGroup();
		}

	public function edit(\App\Record\Video $video) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$container->add(\App\View\Folder::getBreadCrumbs('/Video/browse', $video->folder, true));

		$form = $this->geteditform($video);
		$container->add($form);

		if (! empty($video->videoId))
			{
			$fieldSet = new \PHPFUI\FieldSet('Video');

			if ($video->fileName)
				{
				$fieldSet->add($this->getPlayer($video->fileName));
				$deleteVideo = new \PHPFUI\Button('Delete Video', '/Video/deleteFile/' . $video->videoId);
				$deleteVideo->setConfirm('Are you sure you want to delete the video, it can not be undone?');
				$deleteVideo->addClass('alert');
				$this->buttonGroup->addButton($deleteVideo);
				}
			else
				{
				$uploader = new \App\UI\ChunkedUploader($this->page);
				$uploader->setOption('target', "'/Video/upload'");
				$uploader->setOption('chunkSize', 1024 * 1024);
				$uploader->setOption('testChunks', false);
				$uploader->setOption('singleFile', true);
				$uploader->setOption('query', ['videoId' => $video->videoId]);

				$fieldSet->add($uploader->getError());
				$button = new \PHPFUI\Button('Select Video');
				$text = new \PHPFUI\Container();
				$text->add('Drag and drop a video here.  Or ');
				$text->add($button);
				$fieldSet->add($uploader->getUploadArea($text, $button));
				}
			$container->add($fieldSet);
			}

		return $container;
		}

	public function getEditForm(\App\Record\Video $video) : \PHPFUI\Form
		{
		if ($video->videoId)
			{
			$submit = new \PHPFUI\Submit('Save');
			$form = new \PHPFUI\Form($this->page, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add Video');
			$form = new \PHPFUI\Form($this->page);
			}

		if ($form->isMyCallback())
			{
			$_POST['lastEdited'] = \date('Y-m-d H:i:s');
			$video->setFrom($_POST);
			$video->update();
			$this->page->setResponse('Saved');

			return $form;
			}

		if ($video->lastEdited || $video->memberId || $video->fileName || $video->hits)
			{
			$fieldSet = new \PHPFUI\FieldSet('Information');

			if ($video->lastEdited)
				{
				$fieldSet->add(new \App\UI\Display('Last Edited', $video->lastEdited));
				}

			if ($video->memberId)
				{
				$fieldSet->add(new \App\UI\Display('Uploaded By', $video->member->fullName()));
				}

			if ($video->fileName)
				{
				$fieldSet->add(new \App\UI\Display('File Name', $video->fileName));
				}

			if ($video->hits)
				{
				$fieldSet->add(new \App\UI\Display('Times Viewed', $video->hits));
				}
			$form->add($fieldSet);
			}

		$fieldSet = new \PHPFUI\FieldSet();
		$title = new \PHPFUI\Input\Text('title', 'Video Title', $video->title);
		$title->setRequired();
		$fieldSet->add($title);

		$description = new \App\UI\TextAreaImage('description', 'Description', \PHPFUI\TextHelper::unhtmlentities($video->description));
		$description->htmlEditing($this->page, new \App\Model\TinyMCETextArea($video->getLength('description')));
		$description->setRequired();
		$fieldSet->add($description);

		$videoDate = new \PHPFUI\Input\Date($this->page, 'videoDate', 'Date Video Recorded', $video->videoDate);
		$videoDate->setRequired();

		$public = new \PHPFUI\Input\CheckBoxBoolean('public', 'Publicly Viewable', (bool)$video->public);

		$fieldSet->add(new \PHPFUI\MultiColumn($videoDate, $public));

		$form->add($fieldSet);
		$this->buttonGroup->addButton($submit);
		$form->add($this->buttonGroup);

		return $form;
		}

	public function getPlayer(?string $fileName) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! $fileName || ! \file_exists($_SERVER['DOCUMENT_ROOT'] . '/video/' . $fileName))
			{
			$container->add(new \PHPFUI\SubHeader('Video was not found on the server ' . $fileName));

			return $container;
			}

		if (\str_contains($fileName, '.flv'))
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add('This video is an Adobe Flash video file and can not be viewed in a browser.  You may be able to download it an view it on your computer with the proper software installed.');
			$container->add($callout);
			$button = new \PHPFUI\Button('Download Flash Video', '/video/' . $fileName);
			$button->addClass('warning');
			$container->add($button);
			}
		else
			{
			$embed = new \PHPFUI\Embed();
			$player = new \PHPFUI\VideoJs($this->page);
			$player->addSource('/video/' . $fileName);
			$embed->add($player);
			$container->add($embed);
			$this->page->addJavaScript("videojs('player', {techOrder: ['html5','flvh265'],controlBar:{pictureInPictureToggle:false}})");
			}

		return $container;
		}

	public function list(\App\Table\Video $videoTable, int $folderId = 0) : \App\UI\ContinuousScrollTable
		{
		$view = new \App\UI\ContinuousScrollTable($this->page, $videoTable);

		$this->cuts = $this->getCuts();

		$view->addCustomColumn('videoDate', static fn (array $file) => $file['videoDate']);
		$view->addCustomColumn('title', static fn (array $file) => new \PHPFUI\Link('/Video/view/' . $file['videoId'], $file['title'], false));
		$view->addCustomColumn('description', static fn (array $file) => self::$editFile ? new \PHPFUI\Link('/Video/edit/' . $file['videoId'], $file['description'], false) : $file['description']);
		$view->addCustomColumn('member', static function(array $file) { $member = new \App\Record\Member($file['memberId']);

return $member->fullName();});

		$sortHeaders = ['title' => 'View', 'description' => 'Description', 'videoDate' => 'Date', 'hits' => 'Views'];
		$normalHeaders = ['member'];

		if ($this->moveItem)
			{
			$normalHeaders[] = 'cut';
			$view->addCustomColumn('cut', $this->getCut(...));
			}

		if ($this->deleteItem)
			{
			$normalHeaders[] = 'del';
			$deleter = new \App\Model\DeleteRecord($this->page, $view, $videoTable, 'Are you sure you want to permanently delete this video?');
			$view->addCustomColumn('del', $deleter->columnCallback(...));
			}

		$view->setSearchColumns($sortHeaders)->setHeaders(\array_merge($sortHeaders, $normalHeaders))->setSortableColumns(\array_keys($sortHeaders));

		return $view;
		}

	public function view(\App\Record\Video $video) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$container->add(new \PHPFUI\SubHeader($video->title));
		$container->add(new \PHPFUI\Header(\App\Tools\Date::format('l, F j, Y', \App\Tools\Date::fromString($video->videoDate)), 5));
		$container->add($this->getPlayer($video->fileName));
		++$video->hits;
		$video->update();
		$p = new \PHPFUI\HTML5Element('p');
		$p->add(\PHPFUI\TextHelper::unhtmlentities($video->description));
		$container->add($p);
		$videos = new \PHPFUI\Button('Browse Videos', '/Videos/browse');

		return $container;
		}

	protected function addModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Folder $folder) : void
		{
		if ('Add Video' == ($_POST['submit'] ?? '') && \App\Model\Session::checkCSRF())
			{
			$video = new \App\Record\Video();
			$video->setFrom($_POST);
			$video->folderId = $folder->folderId;
			$video->memberId = $this->signedInMember;
			$this->page->done();
			$this->page->redirect('/Video/edit/' . $video->insert());

			return;
			}

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$fieldSet = new \PHPFUI\FieldSet('Add Video To This Folder');
		$form = $this->getEditForm(new \App\Record\Video());
		$form->setAreYouSure(false);
		$this->buttonGroup->addButton($modal->getCloseButton());
		$fieldSet->add($form);
		$modal->add($fieldSet);
		}
	}
