<?php

namespace App\View\Admin;

class BikeShopAreas implements \Stringable
	{
	private readonly \App\Table\BikeShopArea $bikeShopAreaTable;

	private string $primaryKey = 'bikeShopAreaId';

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->bikeShopAreaTable = new \App\Table\BikeShopArea();
		}

	public function __toString() : string
		{
		$output = '';
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->bikeShopAreaTable->updateFromTable($_POST);
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteArea':

					$bikeShopArea = new \App\Record\BikeShopArea($_POST[$this->primaryKey]);
					$bikeShopArea->delete();
					$this->page->setResponse($_POST[$this->primaryKey]);

					break;


				case 'Add':

					$bikeShopArea = new \App\Record\BikeShopArea();
					$bikeShopArea->setFrom($_POST);
					$bikeShopArea->insert();
					$this->page->redirect();

					break;

				default:

					$this->page->redirect();

				}
			}
		else
			{
			$this->bikeShopAreaTable->addOrderBy('area');

			$delete = new \PHPFUI\AJAX('deleteArea', 'Permanently delete this area?');
			$delete->addFunction('success', '$("#' . $this->primaryKey . '-"+data.response).css("background-color","red").hide("slow").remove();');
			$this->page->addJavaScript($delete->getPageJS());
			$table = new \PHPFUI\Table();
			$table->setRecordId($this->primaryKey);
			$table->addHeader('area', 'Area Description');
			$table->addHeader('state', 'State');
			$table->addHeader('delete', 'Del');

			$bikeShopTable = new \App\Table\BikeShop();

			foreach ($this->bikeShopAreaTable->getRecordCursor() as $bikeShopArea)
				{
				$row = $bikeShopArea->toArray();
				$id = $row[$this->primaryKey];
				$name = new \PHPFUI\Input\Text("area[{$id}]", '', $bikeShopArea->area);
				$hidden = new \PHPFUI\Input\Hidden("{$this->primaryKey}[{$id}]", $id);
				$row['state'] = new \App\UI\State($this->page, "state[{$id}]", '', $bikeShopArea->state);
				$row['area'] = $name . $hidden;
				$bikeShopTable->setWhere(new \PHPFUI\ORM\Condition('bikeShopAreaId', $bikeShopArea->bikeShopAreaId));

				if (! \count($bikeShopTable))
					{
					$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
					$icon->addAttribute('onclick', $delete->execute([$this->primaryKey => $id]));
					$row['delete'] = $icon;
					}
				$table->addRow($row);
				}
			$form->add($table);
			$buttonGroup = new \App\UI\CancelButtonGroup();

			if (\count($this->bikeShopAreaTable))
				{
				$buttonGroup->addButton($submit);
				}
			$add = new \PHPFUI\Button('Add Area');
			$add->addClass('warning');
			$this->addAreaModal($add);
			$buttonGroup->addButton($add);
			$form->add($buttonGroup);
			$output = $form;
			}

		return $output;
		}

	private function addAreaModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Area Information');
		$name = new \PHPFUI\Input\Text('area', 'Area Name');
		$name->setRequired()->setToolTip('This is the name of the area the bike shops are in (county, section, etc)');
		$fieldSet->add($name);
		$state = new \App\UI\State($this->page, 'state', 'State');
		$state->setRequired();
		$fieldSet->add($state);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
