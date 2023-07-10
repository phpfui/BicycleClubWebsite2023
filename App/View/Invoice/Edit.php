<?php

namespace App\View\Invoice;

class Edit
	{
	private \PHPFUI\Input\Select $select;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->select = new \PHPFUI\Input\Select('storeItemDetailId', 'Choose an Option');

		if ($_POST && \App\Model\Session::checkCSRF())
			{
			if (($_POST['submit'] ?? '') == 'Create Invoice')
				{
				$cartItems = \App\Model\Session::getCartItems();

				if (! $cartItems)
					{
					$this->page->redirect();

					return;
					}
				$member = $cartItems[0]->member;
				$volunteerPoints = $member->volunteerPoints ?? 0;

				foreach ($cartItems as $cartItem)
					{
					$cartItem->cartItemId = 0;
					$cartItem->insert();
					}
				\App\Model\Session::saveCartItems([]);
				$invoiceModel = new \App\Model\Invoice();
				$cartModel = new \App\Model\Cart();
				$cartModel->setMemberId($cartItem->memberId);
				$cartModel->compute($volunteerPoints);
				$invoiceId = $invoiceModel->generateFromCart($cartModel);
				$this->page->redirect('/Store/Invoice/pay/' . $invoiceId);
				}
			elseif (($_POST['submit'] ?? '') == 'Add Store Item')
				{
				$items = \App\Model\Session::getCartItems();
				$cartItem = new \App\Record\CartItem();
				$cartItem->storeItemId = (int)$_POST['newStoreItemId'];
				$cartItem->storeItemDetailId = (int)$_POST['storeItemDetailId'];
				$cartItem->memberId = (int)$_POST['memberId'];
				$cartItem->quantity = (int)$_POST['quantity'];
				$cartItem->type = \App\Model\Cart::TYPE_STORE;
				$cartItem->dateAdded = $_POST['dateAdded'];
				$cartItem->cartItemId = \mt_rand();
				$items[] = $cartItem;
				\App\Model\Session::saveCartItems($items);
				$this->page->redirect();
				}
			elseif (($_POST['submit'] ?? '') == 'getOptions')
				{
				$this->select->setRequired();
				$storeItem = new \App\Record\StoreItem((int)$_POST['newStoreItemId']);

				foreach ($storeItem->StoreItemDetailChildren as $storeItemDetail)
					{
					$this->select->addOption($storeItemDetail->detailLine, (string)$storeItemDetail->storeItemDetailId);
					}
				$this->page->setRawResponse(\json_encode(['html' => "{$this->select}"]));
				}
			elseif (($_POST['action'] ?? '') == 'deleteCartItemId')
				{
				$cartItems = \App\Model\Session::getCartItems();
				$idToDelete = (int)$_POST['cartItemId'];

				foreach ($cartItems as $index => $cartItem)
					{
					if ($cartItem->cartItemId == $idToDelete)
						{
						unset($cartItems[$index]);
						}
					}
				\App\Model\Session::saveCartItems($cartItems);
				$this->page->setResponse($_POST['cartItemId']);
				}
			}
		}

	public function create(array $cartItems) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);

		$fields = [];
		$dateAdded = '';
		$member = new \App\Record\Member();

		if (\count($cartItems))
			{
			$dateAdded = $cartItems[0]->dateAdded;
			$member = $cartItems[0]->member;
			$form->add(new \PHPFUI\SubHeader('For ' . $member->fullName()));
			}

		$fieldSet = new \PHPFUI\FieldSet('Items');
		$table = new \PHPFUI\Table();
		$fieldSet->add($table);
		$table->setRecordId($recordId = 'cartItemId');
		$delete = new \PHPFUI\AJAX('deleteCartItemId', 'Delete this item from the invoice?');
		$delete->addFunction('success', "$('#{$recordId}-'+data.response).css('background-color','red').hide('fast').remove()");
		$this->page->addJavaScript($delete->getPageJS());

		$headers = ['Item', 'Option', 'Price', 'quantity' => 'Quantity', 'Del'];
		$table->setHeaders($headers);

		$addCartItemButton = new \PHPFUI\Button('Add Item');
		$addCartItemButton->addClass('success');
		$this->addCartItemModal($addCartItemButton, $member, $dateAdded);
		$totalDue = 0.0;

		foreach ($cartItems as $itemDetail)
			{
			$row = $itemDetail->toArray();
			$storeItemDetail = new \App\Record\StoreItemDetail(['storeItemId' => $itemDetail->storeItemId, 'storeItemDetailId' => $itemDetail->storeItemDetailId]);
			$row['Option'] = $storeItemDetail->detailLine;
			$storeItem = $itemDetail->storeItem;
			$row['Item'] = $storeItem->title;
			$row['Price'] = '$' . \number_format($storeItem->price, 2);
			$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$trash->addAttribute('onclick', $delete->execute([$recordId => $itemDetail->{$recordId}]));
			$row['Del'] = $trash;
			$totalDue += $storeItem->price * $itemDetail->quantity;

			$table->addRow($row);
			}
		$row = ['Price' => '<b>$' . \number_format($totalDue, 2) . '</b>', 'Option' => '<b>Total Due</b>'];
		$table->addRow($row);

		$fieldSet->add($addCartItemButton);

		$form->add($fieldSet);

		$errors = [];

		if (! \count($cartItems))
			{
			$errors['Items'] = ['You must add store items to the invoice'];
			}

		if (! $errors)
			{
			$createInvoice = new \PHPFUI\Submit('Create Invoice');

			$form->add($createInvoice);
			}

		return $form;
		}

	private function addCartItemModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Member $member, string $dateAdded) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);

		$form->add(new \PHPFUI\Header('Add Item: Please fill out all fields', 4));

		$fieldSet = new \PHPFUI\FieldSet('Member Info');
		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Member'), 'memberId', $member->toArray());
		$fieldSet->add($memberPicker->getEditControl());

		$form->add($fieldSet);

		if ($member->loaded())
			{
			$membership = $member->membership;
			$fieldSet->add(new \App\UI\Display('Address', $membership->address));
			$fieldSet->add(new \App\UI\Display('Town', $membership->town));
			$fieldSet->add(new \App\UI\Display('State / Zip', $membership->state . ' ' . $membership->zip));

			if ($member->volunteerPoints)
				{
				$fieldSet->add('<br><br>');
				$fieldSet->add(new \App\UI\Display('Volunteer Points Available', $member->volunteerPoints));
				}
			}

		$fieldSet = new \PHPFUI\FieldSet('Order / Payment Date');
		$date = new \PHPFUI\Input\Date($this->page, 'dateAdded', '', $dateAdded);
		$date->setRequired();
		$fieldSet->add($date);
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Item Information');
		$storeItemPicker = new \App\UI\StoreItemPicker($this->page, 'newStoreItemId', 'Store Item name or description');
		$editControl = $storeItemPicker->getEditControl();
		$fieldSet->add($editControl);

		$div = new \PHPFUI\HTML5Element('div');
		$div->add($this->select);
		$id = $div->getId();
		$editControl->getHiddenField()->addAttribute('onChange', 'addOptionPicker(this.value,"#' . $id . '")');
		$csrf = \PHPFUI\Session::csrf();
		$dollar = '$';
		$js = "function addOptionPicker(value,id){{$dollar}.ajax({method:'POST',dataType:'json',data:{newStoreItemId:value,submit:'getOptions',csrf:'{$csrf}'},success:function(data){{$dollar}(id).html(data.html)}})};";
		$this->page->addJavaScript($js);

		$fieldSet->add($div);
		$quantity = new \PHPFUI\Input\Number('quantity', 'Quantity');
		$quantity->setRequired();
		$fieldSet->add($quantity);
		$form->add($fieldSet);

		$submit = new \PHPFUI\Submit('Add Store Item');
		$modal->closeOnClick($submit);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}
	}
