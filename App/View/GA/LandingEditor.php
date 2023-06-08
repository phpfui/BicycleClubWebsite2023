<?php

namespace App\View\GA;

class LandingEditor
{
	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->settingTable = new \App\Table\Setting();

		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'Add':

					$tabs = \json_decode($this->settingTable->value('GATabs'), true);
					$tabs[] = $_POST['name'];
					$this->settingTable->save('GATabs', \json_encode($tabs, JSON_THROW_ON_ERROR));
					$this->page->redirect();

					break;


				case 'deleteTab':

					$tabs = \json_decode($this->settingTable->value('GATabs'), true);
					$tabId = (int)($_POST['tabId']);
					unset($tabs[$tabId]);
					$this->settingTable->save('GATabs', \json_encode($tabs, JSON_THROW_ON_ERROR));
					$this->settingTable->setWhere(new \PHPFUI\ORM\Condition('name', 'GATab' . $tabId));
					$this->settingTable->delete();
					$this->page->setResponse($_POST['tabId']);

				}
			}
	}

	public function menu($item) : \PHPFUI\UnorderedList | \PHPFUI\Form | \App\View\SettingEditor
		{
		switch ($item)
			{
			case 'Global':
				return $this->settings();

			case 'Header':
			case 'Footer':
				return new \App\View\SettingEditor($this->page, 'GAPage' . $item, true);

			case 'Tab':
				return $this->tabEditor();

			case 'Reorder':
				return $this->reorderTabs();
			}
		$ul = new \PHPFUI\UnorderedList();
		$link = '/GA/landingPageEditor/';
		$type = 'Global';
		$ul->addItem(new \PHPFUI\ListItem("<a href='{$link}{$type}'>Global Page Settings</a>"));
		$type = 'Header';
		$ul->addItem(new \PHPFUI\ListItem("<a href='{$link}{$type}'>Edit Page {$type}</a>"));
		$type = 'Footer';
		$ul->addItem(new \PHPFUI\ListItem("<a href='{$link}{$type}'>Edit Page {$type}</a>"));
		$type = 'Tab';
		$ul->addItem(new \PHPFUI\ListItem("<a href='{$link}{$type}'>Tab Editor</a>"));

		return $ul;
		}

  public function reorderTabs() : \PHPFUI\Form
		{
		$tabs = \json_decode($this->settingTable->value('GATabs'), true);
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->settingTable->save('GATabs', \json_encode($_POST['tab'], JSON_THROW_ON_ERROR));
			$this->page->setResponse('Order Saved');

			return $form;
			}
		$ul = new \PHPFUI\UnorderedList($this->page);

		foreach ($tabs as $index => $tab)
			{
			$row = new \PHPFUI\GridX();
			$column = new \PHPFUI\Cell(12);
			$column->add(new \PHPFUI\Header($tab, 6));
			$column->add(new \PHPFUI\Input\Hidden("tab[{$index}]", $tab));
			$row->add($column);
			$ul->addItem(new \PHPFUI\ListItem($row));
			}
		$form->add($ul);
		$form->add($submit);

		return $form;
		}

	public function settings() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Global Page Settings');
		$settingsSaver = new \App\Model\SettingsSaver();
		$fieldSet->add($settingsSaver->generateField('GAPageName', 'Page Name'));
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Global Page Image');
		$fileName = $this->settingTable->value('GABannerName');
		$gaName = $this->settingTable->value('generalAdmissionName', 'General Admission');
		$fieldSet->add('<img src="/images/GA/' . $fileName . '" alt="' . $gaName . ' Logo" style="display:block;margin:0 auto;">');
		$fieldSet->add('<br>');
		$graphicButton = new \PHPFUI\Button('Upload Graphic');
		$form->saveOnClick($graphicButton);
		$modal = new \PHPFUI\Reveal($this->page, $graphicButton);
		$submitGraphic = new \PHPFUI\Submit('Upload Graphic');
		$uploadForm = new \PHPFUI\Form($this->page);
		$uploadForm->setAreYouSure(false);
		$file = new \PHPFUI\Input\File($this->page, 'graphic', 'Select Graphic');
		$file->setAllowedExtensions(['png', 'jpg', 'jpeg']);
		$file->setToolTip('Graphic should be in the right proportions.');
		$uploadForm->add($file);
		$uploadForm->add($modal->getButtonAndCancel($submitGraphic));
		$modal->add($uploadForm);
		$fieldSet->add($graphicButton);
		$form->add($fieldSet);

		if (isset($_POST['submit']) && $_POST['submit'] == $submitGraphic->getText())
			{
			$fileModel = new \App\Model\GABannerFile();

			if ($fileModel->upload('GALogo', 'graphic', $_FILES))
				{
				$this->settingTable->save('GABannerName', 'GALogo' . $fileModel->getExtension());
				}
			$this->page->redirect();
			}
		elseif ($form->isMyCallback())
			{
			$settingsSaver->save($_POST);
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add($submit);
			}

		return $form;
		}

	public function tabEditor() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->settingTable->save('GATabs', \json_encode($_POST['tab'], JSON_THROW_ON_ERROR));
			$this->page->setResponse('Saved');

			return $form;
			}
		$tabs = \json_decode($this->settingTable->value('GATabs'), true);

		if (! \is_array($tabs))
			{
			$tabs = [];
			}
		$delete = new \PHPFUI\AJAX('deleteTab', 'Are you sure you want to delete this tab?');
		$recordId = 'tabId';
		$delete->addFunction('success', "$('#{$recordId}-'+data.response).css('background-color','red').hide('fast')");
		$this->page->addJavaScript($delete->getPageJS());
		$table = new \PHPFUI\Table();
		$table->setRecordId($recordId);
		$table->setHeaders(['tab' => 'Tab Name', 'edit' => 'Edit', 'del' => 'Del']);

		foreach ($tabs as $index => $tab)
			{
			$input = new \PHPFUI\Input\Text("tab[{$index}]", '', $tab);
			$row = [$recordId => $index, 'tab' => $input];
			$editIcon = new \PHPFUI\FAIcon('far', 'edit', '/GA/tabEditor/' . $index);
			$row['edit'] = $editIcon;
			$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$trash->addAttribute('onclick', $delete->execute([$recordId => $index]));
			$row['del'] = $trash;
			$table->addRow($row);
			}
		$form->add($table);
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$add = new \PHPFUI\Button('Add Tab', '/GA/landingPageEditor/AddTab');
		$buttonGroup->addButton($add);
		$buttonGroup->addButton(new \PHPFUI\Button('Reorder Tabs', '/GA/landingPageEditor/Reorder'));
		$form->saveOnClick($add);
		$this->addEventModal($add);
		$form->add($buttonGroup);

		return $form;
		}

	private function addEventModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('small');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$name = new \PHPFUI\Input\Text('name', 'Tab Name');
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($name);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
}
