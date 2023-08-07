<?php

namespace App\View\Store;

class Edit
	{
	private readonly \PHPFUI\Input\TextArea $description;

	private readonly \App\Table\StorePhoto $storePhotoTable;

	private readonly \App\Model\StoreImages $thumbModel;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->thumbModel = new \App\Model\StoreImages();
		$this->storePhotoTable = new \App\Table\StorePhoto();
		$this->description = new \PHPFUI\Input\TextArea('description', 'Description');
		$this->description->htmlEditing($this->page, new \App\Model\TinyMCETextArea());

		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deletePhoto':

					$item = new \App\Record\StorePhoto((int)$_POST['storePhotoId']);

					if (! $item->empty())
						{
						$this->thumbModel->update($item->toArray());
						$this->thumbModel->delete();
						$item->delete();
						}
					$this->page->setResponse($_POST['storePhotoId']);

					break;

				case 'deleteOption':
					$key = $_POST;
					unset($key['sequence']);
					$storeItemOption = new \App\Record\StoreItemOption($key);
					$storeItemOption->delete();
					$this->page->setResponse($_POST['sequence']);

					break;

				case 'deleteItemDetail':
					$key = $_POST;
					unset($key['detailLine'], $key['quantity']);
					$storeItemDetail = new \App\Record\StoreItemDetail($key);
					$storeItemDetail->delete();
					$this->page->setResponse($_POST['storeItemDetailId']);

					break;

				case 'Add Detail':

					$storeItemDetail = new \App\Record\StoreItemDetail();
					$storeItemDetail->setFrom($_POST);
					$storeItemDetail->insert();
					$this->page->redirect();

					break;

				case 'Add Option':

					$storeItemOptionTable = new \App\Table\StoreItemOption();
					$storeItemOptionTable->setWhere(new \PHPFUI\ORM\Condition('storeItemId', $storeItemId = (int)$_POST['storeItemId']));
					$storeItemOptionTable->setOrderBy('sequence', 'desc')->setLimit(1);

					$storeItemOption = new \App\Record\StoreItemOption();
					$storeItemOption->setFrom($_POST);
					$storeItemOption->sequence = $storeItemOptionTable->getRecordCursor()->current()->sequence + 1;
					$storeItemOption->storeItemId = $storeItemId;
					$storeItemOption->storeOptionId = (int)($_POST['storeOptionId'] ?? 0);
					$storeItemOption->insert();
					$this->page->redirect();

					break;

				}
			}
		}

	public function edit(\App\Record\StoreItem $storeItem) : \PHPFUI\Form
		{
		if ($storeItem->storeItemId)
			{
			$submit = new \PHPFUI\Submit();
			$form = new \PHPFUI\Form($this->page, $submit);
			$form->add(new \PHPFUI\Input\Hidden('storeItemId', (string)$storeItem->storeItemId));
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add Store Item', 'add');
			$form = new \PHPFUI\Form($this->page);
			}

		if ($form->isMyCallback())
			{
			unset($_POST['storeItemId']);
			$storeItem->setFrom($_POST);
			$storeItem->update();

			$storeItemDetail = new \App\Record\StoreItemDetail();
			$storeItemDetail->storeItem = $storeItem;

			foreach ($_POST['storeItemDetailId'] ?? [] as $index => $storePhotoId)
				{
				foreach (['detailLine', 'storeItemDetailId', 'quantity'] as $fieldIndex => $field)
					{
					$value = $_POST[$field][$index];

					if ($fieldIndex)
						{
						// make the ints into real ints
						$value = (int)$value;
						}
					$storeItemDetail->{$field} = $value;
					}
				$storeItemDetail->update();
				}

			$storeItemOptionTable = new \App\Table\StoreItemOption();
			$storeItemOptionTable->setWhere(new \PHPFUI\ORM\Condition('storeItemId', $storeItem->storeItemId));
			$storeItemOptionTable->delete();

			foreach ($_POST['storeOptionId'] ?? [] as $index => $storeOptionId)
				{
				$storeItemOption = new \App\Record\StoreItemOption();
				$storeItemOption->storeItemId = $storeItem->storeItemId;
				$storeItemOption->storeOptionId = (int)$storeOptionId;
				$storeItemOption->sequence = $index + 1;
				$storeItemOption->insert();
				}

			$sequence = 0;

			foreach ($_POST['storePhotoId'] ?? [] as $storePhotoId)
				{
				$photo = new \App\Record\StorePhoto($storePhotoId);
				$photo->sequence = ++$sequence;
				$photo->update();
				}
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['add']))
				{
				$storeItem = new \App\Record\StoreItem();
				$storeItem->setFrom($_POST);
				$id = $storeItem->insert();
				$this->page->redirect('/Store/edit/' . $id);
				}
			}
		else
			{
			$infoSet = new \PHPFUI\FieldSet('Item Number ' . $storeItem->storeItemId . ' Information');
			$storeItemfield = new \PHPFUI\Input\Hidden('storeItemId', (string)$storeItem->storeItemId);
			$infoSet->add($storeItemfield);
			$title = new \PHPFUI\Input\Text('title', 'Item Title', $storeItem->title);
			$title->setRequired()->setToolTip('The title should not be ambigious and suscinct. You can describe and specify sizes later, so this should not include any details');
			$infoSet->add($title);
			$this->description->setValue($storeItem->description ?? '');
			$this->description->setRequired()->setToolTip('Please provide a good description with any details as to fit (if clothing) or other details buyers may need to know.  More is better here.');
			$infoSet->add($this->description);
			$price = new \PHPFUI\Input\Text('price', 'Price', \number_format($storeItem->price ?? 0.0, 2));
			$price->setRequired()->setToolTip('The item price (not including shipping) in dollar and cents.');
			$price->addAttribute('size', '6');
			$shipping = new \PHPFUI\Input\Text('shipping', 'Shipping', \number_format($storeItem->shipping ?? 0.0, 2));
			$shipping->setRequired()->setToolTip('The shipping costs for one item in dollar and cents. If set to zero, it will be shown with free shipping.');
			$shipping->addAttribute('size', '6');
			$zip = new \PHPFUI\Input\Zip($this->page, 'pickupZip', 'Pick Up Zip Code', $storeItem->pickupZip);
			$zip->setToolTip('If the item is picked up and not shipped, this will compute the correct tax to charge based on this zip code.');
			$infoSet->add(new \PHPFUI\MultiColumn($price, $shipping, $zip));
			$noshipping = new \PHPFUI\Input\CheckBoxBoolean('noShipping', 'No Shipping Required', (bool)$storeItem->noShipping);
			$noshipping->setToolTip('If this is checked, the item does not need shipping.');
			$points = new \PHPFUI\Input\CheckBoxBoolean('payByPoints', 'Payable With Points', (bool)$storeItem->payByPoints);
			$points->setToolTip('If volunteer points can be used to pay for this item, check this box.');
			$pointsOnly = new \PHPFUI\Input\CheckBoxBoolean('pointsOnly', 'Points Only', (bool)$storeItem->pointsOnly);
			$pointsOnly->setToolTip('Volunteer points are required to be used to purchace this item.');
			$infoSet->add(new \PHPFUI\MultiColumn($noshipping, $points, $pointsOnly));
			$taxable = new \PHPFUI\Input\CheckBoxBoolean('taxable', 'Taxable', (bool)$storeItem->taxable);
			$taxable->setToolTip('If the item is taxable, you need to check this box. Tax law varies, so if in doubt, ask the club treasurer.');
			$clothing = new \PHPFUI\Input\CheckBoxBoolean('clothing', 'Clothing Article', (bool)$storeItem->clothing);
			$clothing->setToolTip('Clothing articles are taxed at a lower state rate, so check this box if it is a clothing article.  Tax law varies, so if in doubt, ask the club treasurer.');
			$active = new \PHPFUI\Input\CheckBoxBoolean('active', 'Active', (bool)$storeItem->active);
			$active->setToolTip('If active is not checked, it will not show up for sale in the store.');
			$infoSet->add(new \PHPFUI\MultiColumn($taxable, $clothing, $active));
			$form->add($infoSet);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);

			if ($storeItem->storeItemId)
				{
				$storeItemDetailCount = \count($storeItem->StoreItemDetailChildren);
				$storeItemOptionCount = \count($storeItem->StoreItemOptionChildren);

				if ($storeItemDetailCount || ! $storeItemOptionCount)
					{
					$form->add($this->addItemDetails($storeItem, $form));
					}

				if (! $storeItemDetailCount || $storeItemOptionCount)
					{
					$form->add($this->addOptionDetails($storeItem, $form));
					}

				$addPhotoButton = new \PHPFUI\Button('Add Photos');
				$addPhotoButton->addClass('success');
				$buttonGroup->addButton($addPhotoButton);
				$form->saveOnClick($addPhotoButton);
				$form->add($this->addPhotoDialog($storeItem, $addPhotoButton));
				}
			$form->add($buttonGroup);
			}

		return $form;
		}

	private function addItemDetailModal(\PHPFUI\HTML5Element $modalLink, \App\Record\StoreItem $storeItem, int $itemDetailId) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Add Item Detail');
		$fieldSet->add(new \PHPFUI\Input\Hidden('storeItemId', (string)$storeItem->storeItemId));
		$fieldSet->add(new \PHPFUI\Input\Hidden('storeItemDetailId', (string)$itemDetailId));
		$detailLine = new \PHPFUI\Input\Text('detailLine', 'Details / Size');
		$detailLine->setRequired()->setToolTip('The size or other details that make this version unique.');
		$fieldSet->add($detailLine);
		$quantity = new \PHPFUI\Input\Number('quantity', 'Quantity on Hand');
		$quantity->addAttribute('max', (string)999);
		$quantity->setRequired()->setToolTip('Quantity on hand at this time.');
		$fieldSet->add($quantity);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add Detail', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function addItemDetails(\App\Record\StoreItem $storeItem, \PHPFUI\Form $form) : \PHPFUI\FieldSet
		{
		$deleteItemDetail = new \PHPFUI\AJAX('deleteItemDetail', 'Permanently delete this detail line?');
		$deleteItemDetail->addFunction('success', '$("#storeItemDetailId-"+data.response).css("background-color","red").hide("fast").remove();');
		$this->page->addJavaScript($deleteItemDetail->getPageJS());

		$detailSet = new \PHPFUI\FieldSet('Item Detail (eg. sizes) and Inventory');
		$table = new \PHPFUI\Table();
		$table->setRecordId('storeItemDetailId');
		$table->setHeaders(['details' => 'Details / Size',
			'quantity' => 'Quantity on hand',
			'delete' => 'Del', ]);
		$table->setWidths(['60%',
			'30%',
			'10%', ]);
		$last = 0;

		foreach ($storeItem->StoreItemDetailChildren as $detail)
			{
			if ($detail->storeItemDetailId > $last)
				{
				$last = $detail->storeItemDetailId;
				}
			$row = $detail->toArray();
			$row['storeItemDetailId'] = $detail->storeItemDetailId;
			$row['details'] = new \PHPFUI\Input\Text("detailLine[{$detail->storeItemDetailId}]", '', $detail->detailLine);
			$hidden = new \PHPFUI\Input\Hidden("storeItemDetailId[{$detail->storeItemDetailId}]", (string)$detail->storeItemDetailId);
			$row['details'] .= $hidden;
			$row['quantity'] = new \PHPFUI\Input\Number("quantity[{$detail->storeItemDetailId}]", '', $detail->quantity);
			$row['quantity']->addAttribute('max', (string)999);
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $deleteItemDetail->execute(['storeItemDetailId' => $detail->storeItemDetailId,
				'storeItemId' => $storeItem->storeItemId, ]));
			$row['delete'] = $icon;
			$table->addRow($row);
			}
		$add = new \PHPFUI\Button('Add Item Detail');
		$form->saveOnClick($add);
		$this->addItemDetailModal($add, $storeItem, $last + 1);
		$detailSet->add($add);
		$detailSet->add($table);

		return $detailSet;
		}

	private function addOptionDetails(\App\Record\StoreItem $storeItem, \PHPFUI\Form $form) : \PHPFUI\FieldSet
		{
		$detailSet = new \PHPFUI\FieldSet('Options');

		$recordId = 'sequence';
		$deleteOption = new \PHPFUI\AJAX('deleteOption', 'Permanently delete this option?');
		$deleteOption->addFunction('success', '$("#' . $recordId . '-"+data.response).css("background-color","red").hide("fast").remove();');
		$this->page->addJavaScript($deleteOption->getPageJS());

		$table = new \PHPFUI\OrderableTable($this->page);
		$table->setRecordId($recordId);
		$table->setHeaders(['Option', 'Del', ]);
		$last = 0;

		$storeItemOptionChildren = $storeItem->StoreItemOptionChildren;

		foreach ($storeItemOptionChildren as $detail)
			{
			$record = [$recordId => $detail->sequence];
			$record['Option'] = new \App\View\Store\OptionSelect('storeOptionId[]', '', $detail->storeOptionId);

			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $deleteOption->execute(['storeOptionId' => $detail->storeOptionId,
				$recordId => $detail->sequence, 'storeItemId' => $detail->storeItemId, ]));
			$record['Del'] = $icon;
			$table->addRow($record);
			}
		$add = new \PHPFUI\Button('Add Option');
		$form->saveOnClick($add);
		$this->addOptionModal($add, $storeItem);
		$detailSet->add($add);
		$detailSet->add($table);

		return $detailSet;
		}

	private function addOptionModal(\PHPFUI\HTML5Element $modalLink, \App\Record\StoreItem $storeItem) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Add Option');
		$fieldSet->add(new \PHPFUI\Input\Hidden('storeItemId', (string)$storeItem->storeItemId));
		$fieldSet->add(new \App\View\Store\OptionSelect('storeOptionId', 'Select Option to Add'));
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add Option', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function addPhotoDialog(\App\Record\StoreItem $storeItem, \PHPFUI\Button $addPhotoButton) : ?\PHPFUI\FieldSet
		{
		$modal = new \PHPFUI\Reveal($this->page, $addPhotoButton);
		$submitPhoto = new \PHPFUI\Button('Done', \str_replace('#', '', $this->page->getBaseURL()));
		$uploadForm = new \PHPFUI\Form($this->page);
		$uploadForm->setAreYouSure(false);

		$uploader = new \App\UI\ChunkedUploader($this->page);
		$uploader->setOption('target', "'/Store/upload'");
		$uploader->setOption('chunkSize', 1024 * 1024);
		$uploader->setOption('testChunks', false);
		$uploader->setOption('singleFile', false);
		$uploader->setOption('query', ['storeItemId' => $storeItem->storeItemId]);

		$fieldSet = new \PHPFUI\FieldSet('Upload Photos Here');
		$fieldSet->add($uploader->getError());
		$button = new \PHPFUI\Button('Select Photos');
		$text = new \PHPFUI\Container();
		$text->add($button);
		$fieldSet->add($uploader->getUploadArea($text, $button));
		$uploadForm->add($fieldSet);

		$uploadForm->add($modal->getButtonAndCancel($submitPhoto));
		$modal->add($uploadForm);
		$this->thumbModel->update($storeItem->toArray());

		$this->storePhotoTable->setWhere(new \PHPFUI\ORM\Condition('storeItemId', $storeItem->storeItemId));
		$this->storePhotoTable->setOrderBy('sequence');
		$photos = $this->storePhotoTable->getRecordCursor();

		if (! \count($photos))
			{
			return null;
			}

		$photoSet = new \PHPFUI\FieldSet('Photos');
		$orderableTable = new \PHPFUI\OrderableTable($this->page);
		$orderableTable->setHeaders(['filename' => 'Filename', 'View', 'Del']);
		$orderableTable->setRecordId($recordId = 'storePhotoId');

		foreach ($photos as $photo)
			{
			$row = $photo->toArray();
			$deletePhoto = new \PHPFUI\AJAX('deletePhoto', 'Are you sure you want to delete this photo?');
			$deletePhoto->addFunction('success', "$('#{$recordId}-'+data.response).css('background-color','red').hide('fast').remove();");
			$this->page->addJavaScript($deletePhoto->getPageJS());
			$delete = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$delete->addAttribute('onclick', $deletePhoto->execute([$recordId => $photo->storePhotoId]));
			$container = new \PHPFUI\Container();
			$container->add(new \PHPFUI\Input\Hidden('storePhotoId[]', $photo->storePhotoId));
			$container->add($delete);
			$row['Del'] = $container;
			$view = new \PHPFUI\FAIcon('far', 'eye', '#');
			$reveal = new \PHPFUI\Reveal($this->page, $view);
			$reveal->addAttribute('data-multiple-opened', 'true');
			$div = new \PHPFUI\HTML5Element('div');
			$reveal->add($div);
			$close = $reveal->getCloseButton('Close');
			$modal->closeOnClick($close);
			$reveal->add($close);
			$reveal->loadUrlOnOpen('/Store/photo/' . (string)$photo->storePhotoId, $div->getId());
			$row['View'] = $view;
			$orderableTable->addRow($row);
			}
		$photoSet->add($orderableTable);

		return $photoSet;
		}
	}
