<?php

namespace App\View\Admin;

class BikeShop
	{
	private readonly \PHPFUI\Input\Select $area;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->area = new \PHPFUI\Input\Select('bikeShopAreaId', 'Bike Shop Area');

		if (\App\Model\Session::checkCSRF())
			{
			switch ($_POST['action'] ?? '')
				{
				case 'deleteShop':
					$bikeShop = new \App\Record\BikeShop((int)$_POST['bikeShopId']);
					$bikeShop->delete();
					$this->page->setResponse($_POST['bikeShopId']);

					return;

				case 'Add':
					$bikeShop = new \App\Record\BikeShop();
					$bikeShop->setFrom($_POST);
					$bikeShop->insert();
					$this->page->redirect('/Admin/bikeShopList');

					return;
				}
			}

		$bikeShopAreaTable = new \App\Table\BikeShopArea();
		$bikeShopAreaTable->addOrderBy('area');

		foreach ($bikeShopAreaTable->getRecordCursor() as $bikeShopArea)
			{
			$this->area->addOption($bikeShopArea->area, $bikeShopArea->bikeShopAreaId);
			}
		}

	public function edit(\App\Record\BikeShop $bikeShop = new \App\Record\BikeShop()) : \App\UI\ErrorFormSaver
		{
		if (! $bikeShop->empty())
			{
			$submit = new \PHPFUI\Submit('Save');
			$form = new \App\UI\ErrorFormSaver($this->page, $bikeShop, $submit);

			if ($form->save())
				{
				return $form;
				}

			$hidden = new \PHPFUI\Input\Hidden('bikeShopId', (string)$bikeShop->bikeShopId);
			$form->add($hidden);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add', 'action');
			$bikeShop = new \App\Record\BikeShop();
			$form = new \App\UI\ErrorFormSaver($this->page, $bikeShop);
			}

		$fieldSet = new \PHPFUI\FieldSet('Contact Info');
		$name = new \PHPFUI\Input\Text('name', 'Bike Shop Name', $bikeShop->name);
		$name->setRequired();
		$fieldSet->add($name);

		$contact = new \PHPFUI\Input\Text('contact', 'Contact', $bikeShop->contact);
		$phone = new \App\UI\TelUSA($this->page, 'phone', 'Phone', $bikeShop->phone);
		$fieldSet->add(new \PHPFUI\MultiColumn($contact, $phone));

		$email = new \PHPFUI\Input\Email('email', 'Email Address', $bikeShop->email);
		$url = new \PHPFUI\Input\Url('url', 'Website', $bikeShop->url);
		$fieldSet->add(new \PHPFUI\MultiColumn($url, $email));
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Location');
		$address = new \PHPFUI\Input\Text('address', 'Street Address', $bikeShop->address);
		$town = new \PHPFUI\Input\Text('town', 'Town', $bikeShop->town);
		$fieldSet->add(new \PHPFUI\MultiColumn($address, $town));
		$state = new \App\UI\State($this->page, 'state', 'State', $bikeShop->state ?? '');
		$zip = new \PHPFUI\Input\Zip($this->page, 'zip', 'Zip', $bikeShop->zip ?? '');
		$this->area->select((string)$bikeShop->bikeShopAreaId);
		$fieldSet->add(new \PHPFUI\MultiColumn($state, $zip, $this->area));
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Notes');
		$notes = new \App\UI\TextAreaImage('notes', 'Notes', $bikeShop->notes);
		$notes->setRows(3)->setAttribute('maxlength', (string)255);
		$notes->htmlEditing($this->page, new \App\Model\TinyMCETextArea(new \App\Record\BikeShop()->getLength('notes')));
		$fieldSet->add($notes);
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton($submit);
		$form->add($buttonGroup);

		return $form;
		}

	public function list() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$bikeShopTable = new \App\Table\BikeShop();
		$bikeShopTable->setLimit(10);
		$view = new \App\UI\ContinuousScrollTable($this->page, $bikeShopTable);
		$record = $bikeShopTable->getRecord();

		$deleter = new \App\Model\DeleteRecord($this->page, $view, $bikeShopTable, 'Are you sure you want to permanently delete this bike shop?');
		$view->addCustomColumn('del', $deleter->columnCallback(...));
		$view->addCustomColumn('name', static fn (array $bikeShop) => new \PHPFUI\Link('/Admin/bikeShopEdit/' . $bikeShop['bikeShopId'], $bikeShop['name'], false));
		$headers = ['name', 'town', 'state', 'zip', ];
		$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, ['del']))->setSortableColumns($headers);
		$container->add($view);

		$buttonGroup = new \App\UI\CancelButtonGroup();
		$add = new \PHPFUI\Button('Add Bike Shop', '/Admin/bikeShopEdit');
		$buttonGroup->addButton($add);
		$container->add($buttonGroup);

		return $container;
		}
	}
