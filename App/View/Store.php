<?php

namespace App\View;

class Store extends \App\View\Folder
	{
	private readonly \App\Model\StoreImages $imageModel;

	private readonly \App\Table\Setting $settingTable;

	private int $thumbnailSize;

	public function __construct(\App\View\Page $page)
		{
		parent::__construct($page, __CLASS__);
		$this->setItemName('Store Item')->setBrowseSection('Inventory/manage');
		$this->settingTable = new \App\Table\Setting();
		$this->thumbnailSize = (int)$this->settingTable->value('thumbnailSize');
		$this->imageModel = new \App\Model\StoreImages();

		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteStoreItem':

					$storeItem = new \App\Record\StoreItem((int)$_POST['storeItemId']);
					$storeItem->delete();
					$this->page->setResponse($_POST['storeItemId']);

					break;


				case 'Resize':

					$this->thumbnailSize = \max((int)($_POST['thumbnailSize']), 30);
					$this->settingTable->save('thumbnailSize', $this->thumbnailSize);
					$photos = new \App\Table\StorePhoto();

					foreach ($photos->getArrayCursor() as $photo)
						{
						$this->imageModel->update($photo);
						$this->imageModel->createThumb($this->thumbnailSize);
						}
					$this->page->setResponse('Photos Resized');

					break;


				case 'Add To Cart':

					$cartModel = new \App\Model\Cart();
					$cartModel->addFromStore($_POST);
					$this->page->redirect('/Store/cart');

					break;

				}
			}
		}

	public function configuration() : string | \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$submit = new \PHPFUI\Submit('Resize', 'action');
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Resize Store Thumbnails');
		$input = new \PHPFUI\Input\Number('thumbnailSize', 'Current Thumbnail Size', $this->thumbnailSize);
		$input->setRequired()->setToolTip('This is the height of the thumbnail.  Photos that are too wide may distort the store layout.');
		$fieldSet->add($input);
		$fieldSet->add($submit);
		$form->add($fieldSet);
		$container->add($form);

		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$storeClosed = 'storeClosedMessage';

		if ($form->isMyCallback())
			{
			$this->settingTable->save($storeClosed, $_POST[$storeClosed]);
			$this->page->setResponse('Saved');

			return '';
			}
		$fieldSet = new \PHPFUI\FieldSet('Configuration');
		$chair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker('Store Manager'));
		$control = $chair->getEditControl();
		$control->setToolTip('This is the person store related email will be from.');
		$fieldSet->add($control);
		$chair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker('Store Shipping'));
		$control = $chair->getEditControl();
		$control->setToolTip('This is the person who will ship any physical merchandise.');
		$fieldSet->add($control);
		$storeClosedMessage = new \PHPFUI\Input\Text($storeClosed, 'Store Closed Message', $this->settingTable->value($storeClosed));
		$storeClosedMessage->setToolTip('If a message is entered, the store will be closed and this message displayed');
		$fieldSet->add($storeClosedMessage);
		$fieldSet->add($submit);
		$form->add($fieldSet);
		$container->add($form);

		return $container;
		}

	public function getInventoryRequest() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->addAttribute('target', '_blank');
		$fieldSet = new \PHPFUI\FieldSet('Select Inventory Report Type');
		$radio = new \PHPFUI\Input\RadioGroup('type', '', 'C');
		$radio->setSeparateRows();
		$radio->addButton('Complete Report', 'C');
		$radio->addButton('In Stock Items Only', 'S');
		$radio->addButton('Out Of Stock Items Only', 'O');
		$fieldSet->add($radio);
		$form->add($fieldSet);
		$form->add(new \App\UI\CancelButtonGroup(new \PHPFUI\Submit('Print Inventory Report')));

		return $form;
		}

	public function getInvoiceRequest(\PHPFUI\Form $form, bool $downloadLink = true, string $name = 'Enter Report Criteria') : \PHPFUI\Form
		{
		$fieldSet = new \PHPFUI\FieldSet($name);

		$post = \App\Model\Session::getFlash('post');

		$item = new \PHPFUI\MultiColumn();
		$startDate = new \PHPFUI\Input\Date($this->page, 'startDate', 'Start Date', $post['startDate'] ?? \App\Tools\Date::todayString(-31));
		$startDate->setRequired();
		$startDate->setToolTip('Orders placed on or after this date will be included');
		$item->add($startDate);
		$endDate = new \PHPFUI\Input\Date($this->page, 'endDate', 'End Date', $post['endDate'] ?? \App\Tools\Date::todayString());
		$endDate->setRequired();
		$endDate->setToolTip('Orders placed on or before this date will be included');
		$item->add($endDate);
		$fieldSet->add($item);

		if ($downloadLink)
			{
			$cb = new \PHPFUI\Input\CheckBoxBoolean('csv', 'Full Details In CSV Format');
			$cb->setToolTip('A full line with name and addres for each item ordered');
			$fieldSet->add($cb);
			}

		$shipping = new \PHPFUI\Input\RadioGroup('shipped', 'Shipping Status', $post['shipped'] ?? 0);
		$shipping->addButton('Unshipped', (string)2);
		$shipping->addButton('Shipped', (string)1);
		$shipping->addButton('Both', (string)0);

		$volunteer = new \PHPFUI\Input\RadioGroup('points', 'Payment Options', $post['points'] ?? 0);
		$volunteer->addButton('Volunteer', (string)2);
		$volunteer->addButton('Paid Only', (string)1);
		$volunteer->addButton('Both', (string)0);

		$fieldSet->add(new \PHPFUI\MultiColumn($shipping, $volunteer));

		$textSearch = new \PHPFUI\Input\Text('text', 'Invoice Contains Phrase', $post['text'] ?? '');
		$textSearch->setToolTip('Phrase must appear in the same order, single word searches are best.');
		$fieldSet->add($textSearch);
		$restrict = new \PHPFUI\Input\Text('restrict', 'Restrict to Items', $post['restrict'] ?? '');
		$restrict->setToolTip('List the first part of the item number, comma separated.');
		$fieldSet->add($restrict);
		$exclude = new \PHPFUI\Input\Text('exclude', 'Exclude Items', $post['exclude'] ?? '');
		$exclude->setToolTip('List the first part of the item number, comma separated.');
		$fieldSet->add($exclude);
		$form->add($fieldSet);

		return $form;
		}

	public function item(\App\Record\StoreItem $storeItem) : \PHPFUI\Form | \PHPFUI\SubHeader
		{
		if ($storeItem->empty() || ! $storeItem->active || ((\App\Model\Session::getSignedInMember()['volunteerPoints'] ?? 0) <= 0 && $storeItem->pointsOnly))
			{
			return new \PHPFUI\SubHeader('Item Not Found');
			}
		$this->imageModel->update($storeItem->toArray());
		$form = new \PHPFUI\Form($this->page);
		$cartModel = new \App\Model\Cart();
		$cartView = new \App\View\Store\Cart($this->page, $cartModel);
		$form->add($cartView->status());
		$form->add(new \PHPFUI\Input\Hidden('storeItemId', (string)$storeItem->storeItemId));
		$form->add(\App\View\Folder::getBreadCrumbs('/Store/shop', $storeItem->folder, true));
		$form->add("<h2>{$storeItem->title}</h2>");
		$form->add($this->imageModel->getProductGallery($this->page));
		$row = new \PHPFUI\GridX();
		$row->add($storeItem->description);
		$form->add($row);

		$choices = $storeItem->StoreItemOptionChildren;

		if (\count($choices))
			{
			$form->add('<b>Please select:</b>');

			$multicolumn = new \PHPFUI\MultiColumn();

			foreach ($choices as $choice)
				{
				$multicolumn->add(new \App\View\Store\StoreItemOption($choice->storeOption));
				}
			$form->add($multicolumn);
			}
		else
			{
			$choices = \App\Table\StoreItemDetail::getInStock($storeItem->storeItemId, 'detailLine');

			if (\count($choices) > 1)
				{
				$select = new \PHPFUI\Input\Select('storeItemDetailId', 'Select Size');
				$select->addOption('Please Choose', '');

				foreach ($choices as $choice)
					{
					$select->addOption($choice['detailLine'], $choice['storeItemDetailId']);
					}
				$select->setRequired();
				$form->add($select);
				}
			elseif (1 == \count($choices))
				{
				$row = new \PHPFUI\GridX();

				foreach ($choices as $choice)
					{
					$row->add("<b>{$choice['detailLine']}</b>");
					$row->add(new \PHPFUI\Input\Hidden('storeItemDetailId', $choice['storeItemDetailId']));
					}
				$form->add($row);
				}
			else
				{
				$form->add('<b>This item is currently out of stock</b>');
				}
			}
		$row = new \PHPFUI\GridX();

		if ($storeItem->shipping > 0)
			{
			$shipping = "Plus &dollar;{$storeItem->shipping} S&amp;H";
			}
		else
			{
			$shipping = 'Free Shipping!';
			}
		$price = \number_format($storeItem->price, 2);
		$row->add("<b>&dollar;{$price}</b>&nbsp; {$shipping}");
		$form->add($row);
		$submit = new \PHPFUI\Submit('Add To Cart', 'action');
		$submit->addClass('success');
		$store = new \PHPFUI\Button('Back to Store', '/Store/shop');
		$buttonGroup = new \PHPFUI\ButtonGroup();

		if (\count($choices) >= 1)
			{
			$buttonGroup->addButton($submit);
			}
		$buttonGroup->addButton($store);
		$form->add($buttonGroup);

		return $form;
		}

	public function Shop(\App\Model\Cart $cart, \App\Record\Folder $folder = new \App\Record\Folder()) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$closed = $this->settingTable->value('storeClosedMessage');

		$equalizer = null;

		if ($closed)
			{
			$container->add(new \PHPFUI\Header('Store is Temporarily Closed'));
			$container->add(new \PHPFUI\SubHeader($closed));
			}
		else
			{
			$storeItemTable = new \App\Table\StoreItem();
			$items = $storeItemTable->byTitle((\App\Model\Session::getSignedInMember()['volunteerPoints'] ?? 0) > 0, activeOnly:true, folder:$folder);
			$cartView = new \App\View\Store\Cart($this->page, $cart);
			$container->add($cartView->status());
			$container->add(new \PHPFUI\Header('Shop'));
			$container->add(\App\View\Folder::getBreadCrumbs('/Store/shop', $folder));

			$folderTable = new \App\Table\Folder();
			$folderCondition = new \PHPFUI\ORM\Condition('parentFolderId', (int)$folder->folderId);
			$folderCondition->and('folderType', \App\Enum\FolderType::STORE);
			$folderTable->setWhere($folderCondition);
			$folderTable->addOrderBy('name');
			$menu = new \PHPFUI\Menu();
			$menu->addClass('expanded align-center');

			foreach ($folderTable->getRecordCursor() as $folderMenu)
				{
				if ($folderMenu->storeItemChildren->count())
					{
					$menu->addMenuItem(new \PHPFUI\MenuItem($folderMenu->name, '/Store/shop/' . $folderMenu->folderId));
					}
				}

			if (\count($menu))
				{
				$container->add($menu);
				$container->add('<hr>');
				}

			$count = 0;

			$storePhotoTable = new \App\Table\StorePhoto();
			$storePhotoTable->setLimit(1);
			$storePhotoTable->setOrderBy('sequence');

			foreach ($items as $item)
				{
				$storePhotoTable->setWhere(new \PHPFUI\ORM\Condition('storeItemId', $item['storeItemId']));
				$photo = $storePhotoTable->getRecordCursor()->current();
				$this->imageModel->update($photo->toArray());
				$thumbnail = $this->imageModel->getThumbnailImg();
				$thumbnail->addAttribute('alt', $photo->filename ?? 'Club Logo');
				$thumbnail->addClass('imageCenter');

				if (! $count++)
					{
					$equalizer = new \PHPFUI\Equalizer();
					}
				$card = new \PHPFUI\Card();
				$card->addImage($thumbnail)->addSection($item['title'] . '<br><b>&dollar;' . $item['price'] . '</b>');
				$link = new \PHPFUI\HTML5Element('a');
				$link->add($card);
				$link->addAttribute('href', '/Store/item/' . $item['storeItemId']);
				$equalizer->addColumn($link);

				if (3 == $count)
					{
					$container->add($equalizer);
					$count = 0;
					}
				}

			if ($count)
				{
				$container->add($equalizer);
				}
			}

		return $container;
		}

	public function showInventory(\App\Table\StoreItem $storeItemTable, \App\Record\Folder $folder = new \App\Record\Folder()) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$this->page->addPageContent($this->getBreadCrumbs('/Store/Inventory/manage', $folder));

		$condition = new \PHPFUI\ORM\Condition('folderId', (int)$folder->folderId);

		if (! $folder->loaded())
			{
			$condition->or('folderId', null);
			}
		$storeItemTable->setWhere($condition);

		$condition = new \PHPFUI\ORM\Condition('folderType', \App\Enum\FolderType::STORE->value);
		$condition->and('parentFolderId', (int)$folder->folderId);
		$folderTable = new \App\Table\Folder();
		$folderTable->setWhere($condition)->addOrderBy('name');
		$container->add($this->listFolders($folderTable, $folder, null));

		$headers = ['title' => 'Item', 'price' => 'Price', 'storeItemId' => 'Item Id',
			'active' => 'Active', 'del' => 'Delete', ];

		$view = new \App\UI\ContinuousScrollTable($this->page, $storeItemTable);
		$deleter = new \App\Model\DeleteRecord($this->page, $view, $storeItemTable, 'Are you sure you want to permanently delete this item and all its sizes?');
		$view->addCustomColumn('del', $deleter->columnCallback(...));
		$view->addCustomColumn('title', static fn (array $storeItem) => new \PHPFUI\Link('/Store/edit/' . $storeItem['storeItemId'], $storeItem['title'], false));
		$view->addCustomColumn('active', static fn (array $storeItem) => $storeItem['active'] ? '<b>&check;</b>' : '');

		$view->setHeaders($headers);
		unset($headers['del']);
		$view->setSortableColumns(\array_keys($headers));
		unset($headers['active']);

		$view->setSearchColumns($headers);

		$container->add($view);

		return $container;
		}

	protected function addModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Folder $folder) : void
		{
		}
	}
