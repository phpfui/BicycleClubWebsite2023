<?php

namespace App\View\System;

class FavIcon
	{
	private readonly \App\Model\FavIconFiles $model;

	public function __construct(private readonly \PHPFUI\Page $page)
		{
		$this->model = new \App\Model\FavIconFiles();
		}

	public function editSettings() : \PHPFUI\Form
		{
		$uploadButtonName = 'Save';
		$settingsFieldName = 'faviconHeaders';
		$fileFieldName = 'zipfile';
		$form = new \PHPFUI\Form($this->page);
		$settingTable = new \App\Table\Setting();

		if (isset($_POST[$uploadButtonName]) && $_POST[$uploadButtonName] == $uploadButtonName && \App\Model\Session::checkCSRF())
			{
			$settingTable->save($settingsFieldName, $_POST[$settingsFieldName] ?? '');

			if ($this->model->upload(null, $fileFieldName, $_FILES, ['.zip' => 'application/zip', ]))
				{
				\App\Model\Session::setFlash('success', 'Favicon uploaded. Please confirm it looks good');
				}
			else
				{
				\App\Model\Session::setFlash('alert', $this->model->getLastError());
				}
			$this->page->redirect();
			}
		else
			{
			$fileFieldNameSet = new \PHPFUI\FieldSet('FavIcon Settings');
			$link = new \PHPFUI\Link('https://realfavicongenerator.net', 'realfavicongenerator.net');
			$fileFieldNameSet->add("You can upload your favicon here. We recommend the free {$link}, but leave a tip! You will need to upload a zip file containing your favicon files.");
			$file = new \PHPFUI\Input\File($this->page, $fileFieldName, 'Zip file containing favicon.ico and other icon files');
			$file->setAllowedExtensions(['zip']);
			$file->setToolTip('Can be downloaded directly from realfavicongenerator.net');
			$fileFieldNameSet->add($file);
			$headers = new \PHPFUI\Input\TextArea($settingsFieldName, 'Optional FavIcon Headers', $settingTable->value($settingsFieldName));
			$headers->setToolTip('As suggested by realfavicongenerator.net');
			$fileFieldNameSet->add($headers);
			$form->add($fileFieldNameSet);
			$form->add(new \App\UI\CancelButtonGroup(new \PHPFUI\Submit($uploadButtonName, $uploadButtonName)));
			}

		return $form;
		}
	}
