<?php

namespace App\View\Admin;

class Images
	{
	/** @var array<string> */
	protected array $fieldsToSave = [];

	/** @var array<string> */
	protected array $files = [];

	private readonly \App\Model\ImageFiles $model;

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->model = new \App\Model\ImageFiles();
		$this->files = $this->model->getAll();
		$this->settingTable = new \App\Table\Setting();
		}

	public function assign() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save', 'assign');
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Assign Images for each Type');
		$fieldSet->add($this->getFilePicker('clubLogo', 'General Club Logo'));
		$fieldSet->add($this->getFilePicker('cardLogo', 'Membership Card Logo'));
		$fieldSet->add($this->getFilePicker('invoiceLogo', 'Invoice Logo'));
		$fieldSet->add($this->getFilePicker('nameTagLogo', 'Name Tag Logo'));
		$fieldSet->add($this->getFilePicker('missingProfile', 'Missing Profile Photo'));
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			foreach ($this->fieldsToSave as $field)
				{
				$this->settingTable->save($field, $_POST[$field] ?? '');
				}
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add($submit);
			}

		return $form;
		}

	public function getSettings() : \PHPFUI\Tabs
		{
		$tabs = new \PHPFUI\Tabs();
		$tabs->addTab('Assign Images', $this->assign(), true);
		$tabs->addTab('Upload Image', $this->upload());

		return $tabs;
		}

	public function upload() : \PHPFUI\Form
		{
		$upload = 'Upload';
		$field = 'Image';
		$form = new \PHPFUI\Form($this->page);

		if (isset($_POST[$upload]) && $_POST[$upload] == $upload && \App\Model\Session::checkCSRF())
			{
			$allowedFiles = ['.jpg' => 'image/jpeg',
				'.gif' => 'image/gif',
				'.png' => 'image/png', ];

			if ($this->model->upload(null, $field, $_FILES, $allowedFiles))
				{
				\App\Model\Session::setFlash('success', 'Image uploaded successfully');
				}
			else
				{
				\App\Model\Session::setFlash('alert', $this->model->getLastError());
				}
			$this->page->redirect();
			}
		else
			{
			$file = new \PHPFUI\Input\File($this->page, $field, 'Select Image to Upload (.jpg, .png, .gif only)');
			$file->setAllowedExtensions(['png', 'jpg', 'jpeg', 'gif']);
			$file->setToolTip('Image to be uploaded should be clear and high quality.  It will not be resized.');
			$form->add($file);
			$form->add(new \PHPFUI\Submit($upload, $upload));
			}

		return $form;
		}

	private function getFilePicker(string $fieldName, string $description) : \PHPFUI\Input\Select
		{
		$this->fieldsToSave[] = $fieldName;
		$select = new \PHPFUI\Input\Select($fieldName, $description);
		$setting = $this->settingTable->value($fieldName);

		foreach ($this->files as $file)
			{
			$select->addOption($file, $file, $file == $setting);
			}

		return $select;
		}
	}
