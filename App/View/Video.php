<?php

namespace App\View;

class Video
	{
	public function __construct(private \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function accordionList(iterable $videos) : \PHPFUI\Accordion
		{
		$accordion = new \PHPFUI\Accordion();

		foreach ($videos as $video)
			{
			$container = new \PHPFUI\Container();
			$container->add(\PHPFUI\TextHelper::unhtmlentities($video['description']));
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton(new \PHPFUI\Button('View', '/Video/view/' . $video['videoId']));

			if ($this->page->isAuthorized('Edit Video'))
				{
				$editButton = new \PHPFUI\Button('Edit', '/Video/edit/' . $video['videoId']);
				$editButton->addClass('secondary');
				$buttonGroup->addButton($editButton);
				}

			$container->add($buttonGroup);
			$row = new \PHPFUI\GridX();
			$title = new \PHPFUI\Cell(6);
			$title->add($video['title']);
			$row->add($title);
			$type = new \PHPFUI\Cell(3);
			$videoType = new \App\Record\VideoType($video['videoTypeId']);
			$type->add($videoType->name);
			$row->add($type);
			$date = new \PHPFUI\Cell(2);
			$date->add($video['videoDate']);
			$row->add($date);
			$hits = new \PHPFUI\Cell(1);
			$hits->add("{$video['hits']} Views");
			$row->add($hits);
			$accordion->addTab($row, $container);
			}

		return $accordion;
		}

	public function edit(\App\Record\Video $video) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if ($video->lastEdited || $video->editor || $video->fileName || $video->hits)
			{
			$fieldSet = new \PHPFUI\FieldSet('Information');

			if ($video->lastEdited)
				{
				$fieldSet->add(new \App\UI\Display('Last Edited', $video->lastEdited));
				}

			if ($video->editor)
				{
				$member = new \App\Record\Member($video->editor);
				$fieldSet->add(new \App\UI\Display('Last Edited By', $member->fullName()));
				}

			if ($video->fileName)
				{
				$fieldSet->add(new \App\UI\Display('File Name', $video->fileName));
				}

			if ($video->hits)
				{
				$fieldSet->add(new \App\UI\Display('Times Viewed', $video->hits));
				}
			$container->add($fieldSet);
			}

		$fieldSet = new \PHPFUI\FieldSet();
		$title = new \PHPFUI\Input\Text('title', 'Video Title', $video->title);
		$title->setRequired();
		$fieldSet->add($title);

		$description = new \PHPFUI\Input\TextArea('description', 'Description', \PHPFUI\TextHelper::unhtmlentities($video->description));
		$description->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$description->setRequired();
		$fieldSet->add($description);

		$videoDate = new \PHPFUI\Input\Date($this->page, 'videoDate', 'Date Video Recorded', $video->videoDate);
		$videoDate->setRequired();

		$videoType = new \PHPFUI\Input\Select('videoTypeId', 'Video Type');

		$videoTypeTable = new \App\Table\VideoType();

		foreach ($videoTypeTable->getRecordCursor() as $type)
			{
			$videoType->addOption($type->name, $type->videoTypeId);
			}
		$videoType->select((string)$video->videoTypeId);

		$public = new \PHPFUI\Input\CheckBoxBoolean('public', 'Publicly Viewable', (bool)$video->public);

		$fieldSet->add(new \PHPFUI\MultiColumn($videoDate, $videoType, $public));

		if ($video->videoId)
			{
			$submit = new \PHPFUI\Submit('Save');
			$form = new \PHPFUI\Form($this->page, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add');
			$form = new \PHPFUI\Form($this->page);
			}

		if ($form->isMyCallback())
			{
			$_POST['videoId'] = $video->videoId;
			$_POST['editor'] = \App\Model\Session::signedInMemberId();
			$_POST['lastEdited'] = \date('Y-m-d H:i:s', \time());

			$video = new \App\Record\Video($_POST);
			$video->update();
			$this->page->setResponse('Saved');

			return $container;
			}
		elseif ('Add' == ($_POST['submit'] ?? '') && \App\Model\Session::checkCSRF())
			{
			$video = new \App\Record\Video();
			$video->setFrom($_POST);
			$id = $video->insert();
			$this->page->done();
			$this->page->redirect('/Video/edit/' . $id);

			return $container;
			}

		$form->add($fieldSet);
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$form->add($buttonGroup);
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
				$buttonGroup->addButton($deleteVideo);
				}
			else
				{
				$uploader = new \App\View\ChunkedUploader($this->page);
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

	public function getPlayer(?string $fileName) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! $fileName || ! \file_exists($_SERVER['DOCUMENT_ROOT'] . '/video/' . $fileName))
			{
			$container->add(new \PHPFUI\SubHeader('Video was not found on the server'));

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

	public function list() : \App\UI\PaginatedTable
		{
		$videoTable = new \App\Table\Video();
		$videoTable->addJoin('videoType');

		if (! \App\Model\Session::isSignedIn())
			{
			$videoTable->setWhere(new \PHPFUI\ORM\Condition('public', 1));
			}

		$view = new \App\UI\PaginatedTable($this->page, $videoTable);

		$view->addCustomColumn('title', static function(array $video) {
			$span = new \PHPFUI\HTML5Element('span');
			$span->add($video['title']);
			$panel = new \PHPFUI\HTML5Element('div');
			$panel->add(\htmlspecialchars_decode($video['description']));
			$title = new \PHPFUI\DropDown($span, $panel);
			$title->setHover();

			return new \PHPFUI\Link('/Video/view/' . $video['videoId'], $title, false);
			});

		$view->addCustomColumn('edit', static function(array $video) {
			return new \PHPFUI\FAIcon('far', 'edit', '/Video/edit/' . $video['videoId']);
			});

		$deleter = new \App\Model\DeleteRecord($this->page, $view, $videoTable, 'Are you sure you want to permanently delete this video?');
		$view->addCustomColumn('description', static function(array $row) {return new \PHPFUI\Link('/Video/edit/' . $row['videoId'], $row['title'], false);});
		$view->addCustomColumn('del', $deleter->columnCallback(...));
		$headers = ['title' => 'Title', 'videoDate' => 'Date', 'hits' => 'Views', 'videoType.name' => 'Type'];
		$editControls = [];

		if ($this->page->isAuthorized('Edit Video'))
			{
			$editControls[] = 'edit';
			}

		if ($this->page->isAuthorized('Delete Video'))
			{
			$editControls[] = 'del';
			}
		$view->setHeaders(\array_merge($headers, $editControls))->setSortableColumns(\array_keys($headers));
		$view->setSearchColumns(['title', 'videoDate', 'hits']);

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
		$videos = new \PHPFUI\Button('All Videos', '/Videos/All');

		return $container;
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'deleteVideo':
						$video = new \App\Record\Video((int)$_POST['videoId']);
						$fileName = $_SERVER['DOCUMENT_ROOT'] . '/video/' . $video->fileName;
						$video->delete();

						if (\file_exists($fileName) && ! \is_dir($fileName))
							{
							\unlink($fileName);
							}
						$this->page->setResponse($_POST['videoId']);

						break;
					}
				}
			}
		}
	}
