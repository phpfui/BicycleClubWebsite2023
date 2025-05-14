<?php

namespace App\View;

class File extends \App\View\Folder
	{
	private readonly \App\Model\FileFiles $fileFiles;

	public function __construct(\App\View\Page $page)
		{
		parent::__construct($page, __CLASS__);
		$this->fileFiles = new \App\Model\FileFiles();
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

				if (empty($file->description))
					{
					$file->description = $file->fileName;
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
		$caption = new \PHPFUI\Input\Text('description', 'File Description', $file->description);
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

	public function listFiles(\App\Table\File $fileTable, int $folderId = 0) : \App\UI\ContinuousScrollTable
		{
		$view = new \App\UI\ContinuousScrollTable($this->page, $fileTable);

		$this->cuts = $this->getCuts();
		$that = $this;

		$view->addCustomColumn('uploaded', static fn (array $file) => \date('Y-m-d', \strtotime((string)$file['uploaded'])));
		$view->addCustomColumn('description', static fn (array $file) => $that->editItem ? new \PHPFUI\Link('/File/edit/' . $file['fileId'], $file['description'], false) : $file['description']);
		$view->addCustomColumn('fileName', static fn (array $file) => new \PHPFUI\Link('/File/download/' . $file['fileId'], $file['fileName'], false));
		$view->addCustomColumn('member', static function(array $file) { $member = new \App\Record\Member($file['memberId']);

return $member->fullName();});

		$headers = ['fileName' => 'Download', 'description' => 'Description', 'uploaded' => 'Uploaded'];
		$normalHeaders = ['member'];

		if ($this->deleteItem)
			{
			$deleter = new \App\Model\DeleteRecord($this->page, $view, $fileTable, 'Are you sure you want to permanently delete this file?');
			$view->addCustomColumn('del', $deleter->columnCallback(...));
			$normalHeaders[] = 'del';
			}

		if ($this->moveItem)
			{
			$normalHeaders[] = 'cut';
			$view->addCustomColumn('cut', $this->getCut(...));
			}

		$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, $normalHeaders))->setSortableColumns(\array_keys($headers));

		return $view;
		}

	protected function addModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Folder $folder) : void
		{
		$submit = new \PHPFUI\Submit('Add File');

		if (\App\Model\Session::checkCSRF() && $submit->submitted($_POST))
			{
			$file = new \App\Record\File();
			$file->setFrom([
				'folderId' => $folder->folderId,
				'description' => $_POST['description'] ?? '',
				'memberId' => $this->signedInMember,
				'public' => $_POST['public'] ?? 0,
			]);
			$fileId = $file->insert();
			$file->reload();

			if ($this->fileFiles->upload((string)$fileId, 'file', $_FILES, null))
				{
				$file->extension = $this->fileFiles->getExtension();

				$file->fileName = \substr($this->fileFiles->getUploadName(), 0, \strrpos($this->fileFiles->getUploadName(), '.'));

				if (empty($file->description))
					{
					$file->description = $file->fileName;
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
	}
