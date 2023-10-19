<?php

namespace App\View\Store;

class Cart
	{
	private readonly \App\Model\GeneralAdmission $gaModel;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Model\Cart $model)
		{
		$this->gaModel = new \App\Model\GeneralAdmission();
		$this->processRequest($_GET);
		$this->processRequest($_POST);
		}

	public function checkOut(\App\DB\MemberCustomer $customer, string $badDiscountCode = '') : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->add(new \PHPFUI\Header('Check Out'));

		if ($this->model->getCount())
			{
			$this->show($form, false);
			$alert = new \App\UI\Alert('Your order will not be complete until you receive email confirmation of your payment.');
			$alert->addClass('info radius');

			$form->add(new \PHPFUI\SubHeader('Your Order'));
			$form->add($alert);
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton(new \PHPFUI\Button('Edit My Cart', '/Store/cart'));
			$continueShopping = new \PHPFUI\Button('Continue Shopping', '/Store/shop');
			$continueShopping->addClass('info');
			$buttonGroup->addButton($continueShopping);
			$form->add($buttonGroup);

			$form->add('<hr>');
			$form->add(new \PHPFUI\SubHeader('Shipping Information'));
			$form->add('We will ship any physical items in this order to the following address:<p>');
			$form->add("{$customer->firstName} {$customer->lastName}<br>");
			$form->add("{$customer->address}<br>{$customer->town}, {$customer->state} {$customer->zip}<p>");
			$form->add(new \PHPFUI\Button('Edit Address Information', '/Store/address'));
			$form->add('<hr>');
			$form->add(new \PHPFUI\SubHeader('Payment Information'));
			$totalOwed = $this->model->getGrandTotal();

			$submitText = 'Confirm Order';

			if ($totalOwed > 0.00)
				{
				$submitText .= ' And Pay';
				}
			$confirm = new \PHPFUI\Submit($submitText);
			$confirm->addClass('success');
			$buttonGroup->addButton($confirm);

			$owe = 'You owe $' . \number_format($totalOwed, 2) . ' to complete this order.';
			$form->add($owe);
			$fieldSet = new \PHPFUI\FieldSet('Discount Code');
			$row = new \PHPFUI\GridX();
			$cola = new \PHPFUI\Cell(6);
			$discountCode = $this->model->getDiscountCode();
			$colb = new \PHPFUI\Cell(6);

			if ($discountCode->empty())
				{
				if ($badDiscountCode)
					{
					$cola->add($badDiscountCode . ' is not valid');
					}
				$cola->add(new \PHPFUI\Input\Text('discountCode'));
				$colb->add(new \PHPFUI\Submit('Apply'));
				}
			else
				{
				$cola->add(new \PHPFUI\Header($discountCode['discountCode'], 4));
				$cola->add($discountCode['description']);
				$colb->add(new \PHPFUI\Submit('Remove'));
				}
			$row->add($cola);
			$row->add($colb);
			$fieldSet->add($row);
			$form->add($fieldSet);
			$fieldSet = new \PHPFUI\FieldSet('Special Instructions');
			$fieldSet->add(new \PHPFUI\Input\Text('instructions', 'Instructions / Comments'));
			$form->add($fieldSet);
			$alert = new \App\UI\Alert('Your order will not be complete until you receive email confirmation of your payment.');
			$alert->addClass('info radius');
			$form->add($alert);

			$confirmOrder = new \PHPFUI\Submit($submitText);
			$confirmOrder->addClass('success');
			$form->add($confirmOrder);
			}
		else
			{
			$customerModel = new \App\Model\Customer();
			$form->add($this->showEmpty($customerModel->getNumber()));
			}

		return $form;
		}

	public function show(\PHPFUI\Form $cart, bool $editQuantities = true) : \PHPFUI\Form
		{
		$table = new \PHPFUI\Table();
		$table->addAttribute('width', '100%');
		$headers = ['description' => 'Description',
			'quantity' => 'Quantity',
			'price' => 'Unit Price',
			'tax' => 'Tax',
			'total' => 'Total', ];

		if ($editQuantities)
			{
			$headers['delete'] = 'Remove';
			$table->setRecordId('index');
			}
		$table->setHeaders($headers);
		$index = $subTotal = 0;
		$dupes = [];

		$this->model->check();

		foreach ($this->model->getItems() as $item)
			{
			if (! empty($item['quantity']))
				{
				$item['index'] = ++$index;
				$add = false;
				$item['price'] = (float)($item['price'] ?? 0.0);
				$item['quantity'] = (int)($item['quantity'] ?? 0);	// @phpstan-ignore-line
				$subTotal += $item['price'] * $item['quantity'];
				$item['total'] = '$' . \number_format($item['price'] * $item['quantity'] + $item['tax'], 2);
				$item['price'] = '$' . \number_format($item['price'], 2);
				$item['tax'] = '$' . \number_format($item['tax'], 2);
				$messages = [];
				$message = '';

				switch ($item['type'])
					{
					case \App\Model\Cart::TYPE_GA:
						$warnings = $this->gaModel->getWarningMessages($item['storeItemDetailId']);

						if ($warnings)
							{
							$messages = \array_merge($messages, $warnings);
							}

						if (\in_array($item['detailLine'], $dupes))
							{
							$messages[] = 'This rider is already in your cart';
							}

						if ($messages)
							{
							$message = '<br><span class="warning">' . \implode('<br>', $messages) . '</span>';
							}
						$editLink = '';

						if ($this->page->isAuthorized('Edit Rider'))
							{
							$editLink = "<br><a href='/GA/editRider/{$item['storeItemDetailId']}'>{$item['detailLine']}</a>";
							}

						$item['description'] = "{$item['title']}<br><b>{$item['detailLine']}</b>" . $message;
						$dupes[] = $item['detailLine'];
						$item['quantity'] = 1;
						$add = true;

						break;

					case \App\Model\Cart::TYPE_STORE:
					case \App\Model\Cart::TYPE_ORDER:
						$detail = $item['detailLine'] ?? $item['optionsSelected'];
						$item['description'] = "<a href='/Store/item/{$item['storeItemId']}'>{$item['title']}</a><br>{$detail}";

						if ($editQuantities)
							{
							$quantity = new \PHPFUI\Input\Number("quantity[{$item['cartItemId']}]", '', $item['quantity']);
							$quantity->addAttribute('max', (string)999)->addAttribute('min', (string)0);
							$item['quantity'] = $quantity;
							}
						$add = true;

						break;

					case \App\Model\Cart::TYPE_EVENT:
						break;

					case \App\Model\Cart::TYPE_MEMBERSHIP:
						break;

					case \App\Model\Cart::TYPE_DISCOUNT_CODE:
						break;
					}

				if ($add)
					{
					$parameters = ['action' => 'delete', 'cartItemId' => $item['cartItemId']];
					$query = \http_build_query($parameters);
					$delete = new \PHPFUI\FAIcon('fas', 'window-close', "{$this->page->getBaseURL()}?{$query}");
					$delete->addClass('fa-2x red');
					$item['delete'] = $delete;
					$table->addRow($item);
					}
				}
			}
		$cart->add($table);
		$cart->add($this->summaryLine('<b>SubTotal</b>', '$' . \number_format($subTotal, 2)));

		if ($this->model->getVolunteerPoints())
			{
			$cart->add($this->summaryLine('<b>Available Points</b>', '$' . $this->model->getVolunteerPoints()));
			}

		if ($this->model->getPayableByPoints() > 0.0 && $this->model->getVolunteerPoints() > 0)
			{
			$cart->add($this->summaryLine('<b>Payable By Points</b>', '$' . \number_format($this->model->getPayableByPoints(), 2)));
			$cart->add($this->summaryLine('<b>Applied Points</b>', '-$' . \number_format(\min($this->model->getPayableByPoints(), $this->model->getVolunteerPoints()), 2)));
			}
		$cart->add($this->summaryLine('<b>Shipping</b>', '$' . \number_format($this->model->getShipping(), 2)));
		$cart->add($this->summaryLine('<b>Tax</b>', '$' . \number_format($this->model->getTax(), 2)));

		if (0.00 != ($discount = $this->model->getDiscount()))
			{
			$cart->add($this->summaryLine('<b>Discount</b>', '-$' . \number_format($discount, 2)));
			}
		$cart->add($this->summaryLine('<hr>', '<hr>'));
		$cart->add($this->summaryLine('<b>Grand Total</b>', '$' . \number_format($this->model->getGrandTotal(), 2)));

		return $cart;
		}

	public function showEmpty(int $customerId) : \PHPFUI\Cell
		{
		$column = new \PHPFUI\Cell(12);
		$column->add(new \PHPFUI\Header('You have no items in your cart', 3));
		$buttonGroup = new \PHPFUI\ButtonGroup();

		$invoiceTable = new \App\Table\Invoice();
		$invoiceTable->setUnpaidForMember($customerId);

		if (\count($invoiceTable))
			{
			$column->add(new \PHPFUI\Header('But you have unpaid invoices, so take a look at those', 5));
			$buttonGroup->addButton(new \PHPFUI\Button('Unpaid Invoices', '/Store/Invoice/myUnpaid'));
			}
		$shop = new \PHPFUI\Button('Continue Shopping', '/Store/shop');
		$shop->addClass('info');
		$buttonGroup->addButton($shop);
		$column->add($buttonGroup);

		return $column;
		}

	public function status() : string | \App\UI\Alert
		{
		$this->model->compute();
		$value = '$' . \number_format($this->model->getTotal(), 2);
		$count = $this->model->getCount();

		if (! $count)
			{
			$message = 'no items';
			}
		elseif (1 == $count)
			{
			$message = '1 item';
			}
		else
			{
			$message = "{$count} items";
			}
		$icon = new \PHPFUI\FAIcon('fas', 'shopping-cart');
		$icon->addClass('fa-lg');
		$alert = new \App\UI\Alert("<h4>{$icon} You have {$message} in your cart. {$value}</h4>");

		if ($count)
			{
			$alert = "<a href='/Store/cart'>{$alert}</a>";
			}

		return $alert;
		}

	/**
	 * @param array<string,array<string,string>|string> $parameters
	 */
	private function processRequest(array $parameters) : void
		{
		if ('delete' == ($parameters['action'] ?? ''))
			{
			$this->model->delete(new \App\Record\CartItem((int)$parameters['cartItemId']));
			$this->page->redirect();
			}
		elseif (isset($parameters['submit']) && \App\Model\Session::checkCSRF())
			{
			$redirect = '';

			switch ($parameters['submit'])
				{
				case 'Check Out':
					$redirect = '/Store/checkout/';

					if (isset($parameters['quantity']))
						{
						$this->model->updateCartQuantities($parameters['quantity']);
						}

					break;

				case 'Update Cart':
					$redirect = '';

					if (isset($parameters['quantity']))
						{
						$this->model->updateCartQuantities($parameters['quantity']);
						}
					$this->model->check();

					break;

				case 'Continue Shopping':
					$redirect = '/Store/shop';

					if (isset($parameters['quantity']))
						{
						$this->model->updateCartQuantities($parameters['quantity']);
						}

					break;

				case 'Confirm Order And Pay':
					$invoiceModel = new \App\Model\Invoice();
					$invoiceId = $invoiceModel->generateFromCart($this->model);
					$paypalType = $invoiceModel->getPayPalType();
					$redirect = '/Store/pay/' . $invoiceId . '/' . $paypalType;

					break;

				case 'Confirm Order':
					$invoiceModel = new \App\Model\Invoice();
					$invoiceId = $invoiceModel->generateFromCart($this->model);
					$redirect = '/Store/paid/' . $invoiceId;

					break;

				/** @noinspection PhpMissingBreakStatementInspection */
				case 'Remove':
					$parameters['discountCode'] = '';

					// Intentionally fall through
				case 'Apply':
					$redirect = '/Store/checkout/';

					if (! $this->model->updateDiscount($parameters['discountCode']) && $parameters['discountCode'])
						{
						$redirect .= '/' . $parameters['discountCode'];
						}

					break;
				}
			$this->page->redirect($redirect);
			}
		}

	private function summaryLine(string $label, string $value) : \PHPFUI\GridX
		{
		$row = new \PHPFUI\GridX();
		$spacer = new \PHPFUI\Cell(6, 8, 9);
		$spacer->add('&nbsp;');
		$row->add($spacer);
		$labelCol = new \PHPFUI\Cell(3, 2, 2);
		$labelCol->add($label);
		$valueCol = new \PHPFUI\Cell(3, 2, 1);
		$valueCol->add($value);
		$valueCol->addClass('text-right');
		$row->add($labelCol);
		$row->add($valueCol);

		return $row;
		}
	}
