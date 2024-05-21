<?php

namespace App\Model;

class Invoice
	{
	protected Customer $customerModel;

	protected GeneralAdmission $gaModel;

	protected Member $memberModel;

	protected string $paypalType = 'Store';

	protected \App\Model\Reservation $reservationModel;

	private readonly \App\Table\Setting $settingTable;

	public function __construct()
		{
		$this->settingTable = new \App\Table\Setting();
		$this->memberModel = new \App\Model\Member();
		$this->reservationModel = new \App\Model\Reservation();
		$this->customerModel = new \App\Model\Customer();
		$this->gaModel = new \App\Model\GeneralAdmission();
		}

	public function delete(\App\Record\Invoice $invoice) : void
		{
		if (! $invoice->empty())
			{
			$memberId = $invoice->memberId;
			$member = $this->customerModel->read($memberId);

			if ($invoice->pointsUsed > 0 && $memberId > 0)
				{
				$memberRecord = new \App\Record\Member($memberId);
				$pointHistory = new \App\Record\PointHistory();
				$pointHistory->member = $memberRecord;
				$pointHistory->oldLeaderPoints = $memberRecord->volunteerPoints;
				$memberRecord->volunteerPoints += $invoice->pointsUsed;
				$pointHistory->volunteerPoints = $memberRecord->volunteerPoints;
				$pointHistory->insert();
				// need to update point history here
				$memberRecord->update();
				}

			foreach ($invoice->InvoiceItemChildren as $invoiceItem)
				{
				switch ($invoiceItem->type)
					{
					case \App\Enum\Store\Type::STORE:
						$storeItemDetail = new \App\Record\StoreItemDetail(['storeItemId' => $invoiceItem->storeItemId,
							'storeItemDetailId' => $invoiceItem->storeItemDetailId, ]);

						if ($storeItemDetail->loaded())
							{
							$storeItemDetail->quantity = $storeItemDetail->quantity + $invoiceItem->quantity;
							$storeItemDetail->update();
							}

						break;

					case \App\Enum\Store\Type::GENERAL_ADMISSION:
						$this->gaModel->setRiderPending($invoiceItem->storeItemDetailId, 1);

						break;

					case \App\Enum\Store\Type::DISCOUNT_CODE:
					case \App\Enum\Store\Type::MEMBERSHIP:
						// nothing to do!
						break;

					case \App\Enum\Store\Type::EVENT:
						// remove the reservation from the event
						$this->reservationModel->delete(new \App\Record\Reservation($invoiceItem->storeItemDetailId));

						break;
					}
				}
			$invoice->delete();
			}
		}

	public function execute(\App\Record\Invoice $invoice, \App\Record\Payment $payment = new \App\Record\Payment()) : void
		{
		if (! $invoice->empty())
			{
			$permissionTable = new \App\Table\Permission();
			$chairs = [];
			$affectedMembers = [];
			$emails = [];
			$invoice->fullfillmentDate = \App\Tools\Date::todayString();
			$invoice->paymentDate = \App\Tools\Date::todayString();

			foreach ($invoice->InvoiceItemChildren as $invoiceItem)
				{
				switch ($invoiceItem->type)
					{
					case \App\Enum\Store\Type::ORDER:
						$storeItem = $invoiceItem->storeItem;
						$invoice->fullfillmentDate = null;
						// add into storeOrder table
						$storeOrder = new \App\Record\StoreOrder();
						$storeOrder->invoice = $invoice;
						$storeOrder->memberId = $invoice->memberId;
						$storeOrder->optionsSelected = $invoiceItem->detailLine;
						$storeOrder->quantity = $invoiceItem->quantity ?? 1;
						$storeOrder->storeItemId = $invoiceItem->storeItemId;
						$storeOrder->insert();
						$chairs['Store Shipping'] = 1;

						break;

					case \App\Enum\Store\Type::STORE:
						$storeItem = $invoiceItem->storeItem;

						if (! $storeItem->empty())
							{
							if (! $storeItem->noShipping)
								{
								$invoice->fullfillmentDate = null;
								}
							}
						$chairs['Store Shipping'] = 1;

						// nothing else to do here, items already taken out of inventory, if not paid, we will restore them elsewhere.
						break;

					case \App\Enum\Store\Type::GENERAL_ADMISSION:
						// $event = new \App\Record\GaEvent($cartItem['storeItemId']);
						// $rider = new \App\Record\GaRider($cartItem['storeItemDetailId']);
						$affectedMembers[] = $this->gaModel->executeInvoice($invoice, $invoiceItem);
						$chairs[] = $this->gaModel->getChair($invoiceItem->storeItemId);

						break;

					case \App\Enum\Store\Type::DISCOUNT_CODE:
						// nothing to do here!
						break;

					case \App\Enum\Store\Type::MEMBERSHIP:
						$affectedMembers[] = $this->memberModel->executeInvoice($invoice, $invoiceItem, $payment);
						$chairs['Membership Chair'] = 1;

						break;

					case \App\Enum\Store\Type::EVENT:
						$affectedMembers[] = $this->reservationModel->executeInvoice($invoice, $invoiceItem, $payment);
						$chairs[] = $this->reservationModel->getChair($invoiceItem->storeItemId);

						break;
					}
				}

			foreach ($chairs as $chairName => $chair)
				{
				if (! \is_array($chair))
					{
					$memberPicker = new \App\Model\MemberPicker($chairName);
					$chair = $memberPicker->getMember();
					}
				$emails[$chair['email']] = $chair['firstName'] . ' ' . $chair['lastName'];
				}
			$invoice->update();
			$invoicePDF = $this->generatePDF($invoice);
			$invoiceString = $invoicePDF->Output('', 'S');
			$customer = $this->customerModel->read($memberId = $invoice->memberId);
			// email member and chairs
			$email = new \App\Tools\EMail();
			$message = "Dear {$customer->firstName},";
			$message .= "\n\nSee attached invoice for your recent purchase from " . $this->settingTable->value('clubName');
			$message .= "\n\nIf any items require shipping, you will receive a separate email when the items ship.";
			$message .= "\n\nItems that don't involve shipping are now active.";
			$email->setBody($message);
			$clubAbbrev = $this->settingTable->value('clubAbbrev');
			$email->setSubject("{$clubAbbrev} Store Invoice #{$invoice->invoiceId} " . \App\Tools\Date::format('M j, Y'));
			$email->addAttachment($invoiceString, $this->getFileName($invoice));
			// copy the affected chairs

			foreach ($emails as $address => $name)
				{
				$email->setFrom($address, $name);
				$email->setReplyTo($address, $name);

				// email is from the first person with an item on the invoice.  Good enough.
				break;
				}

			foreach ($affectedMembers as $member)
				{
				if ($customer->email != ($member['email'] ?? ''))
					{
					$email->addBCCMember($member);
					}
				}
			// email the buyer
			$email->addToMember($customer->toArray());
			$email->bulkSend();
			}
		}

	public function executePayment(\App\Record\Invoice $invoice, string $txn, float $payment_amount) : void
		{
		// process payment
		$invoice->paymentDate = \App\Tools\Date::todayString();
		$invoice->paypaltx = $txn;
		$invoice->paypalPaid = $payment_amount;
		$invoice->update();
		$member = $invoice->member;

		$payment = new \App\Record\Payment();
		$payment->paymentType = 3;
		$payment->amount = $payment_amount;
		$payment->invoice = $invoice;
		$payment->membershipId = $member->membershipId;
		$payment->dateReceived = \App\Tools\Date::todayString();
		$payment->paymentNumber = $txn;
		$payment->paymentDated = \App\Tools\Date::todayString();
		$payment->enteringMemberNumber = -1;

		$paymentId = $payment->insert();
		$this->execute($invoice, $payment);
		$errors = \PHPFUI\ORM::getLastErrors();

		if ($errors)
			{
			$logger = new \App\Tools\Logger();
			$logger->debug($errors, "Database errors processing PayPal transaction {$txn}");
			}
		}

	/**
	 * @return (mixed|string)[][]
	 *
	 * @psalm-return list<array{'Invoice Id': string, 'From Email Address': mixed, Name: mixed}>
	 */
	public function findMissingInvoices(\App\Tools\CSV\FileReader $invoices) : array
		{
		$missing = [];
		$copyFields = ['Name', 'From Email Address', ];

		foreach ($invoices as $row)
			{
			$exploded = \explode('-', (string)$row['Item ID']);

			if (2 == \count($exploded) && 'Invoice' == $exploded[0])
				{
				$invoice = new \App\Record\Invoice((int)$exploded[1]);

				if ($invoice->empty())
					{
					$missingRow = ['Invoice Id' => $exploded[1]];

					foreach ($copyFields as $field)
						{
						$missingRow[$field] = $row[$field];
						}
					$missing[] = $missingRow;
					}
				}
			}

		return $missing;
		}

	public function generateFromCart(\App\Model\Cart $cartModel) : \App\Record\Invoice
		{
		$invoice = new \App\Record\Invoice();
		$pointsEarned = $pointsUsed = 0;
		$customerNumber = $cartModel->getCustomerNumber();
		$member = $this->customerModel->read($cartModel->getCustomerNumber());

		if (! empty($member->volunteerPoints))
			{
			$pointsEarned = (int)$member->volunteerPoints;
			}
		$pointsTotal = $cartModel->getPayableByPoints();

		if ($pointsTotal)
			{
			if ($pointsEarned >= $pointsTotal)
				{
				$pointsUsed = (int)\round($pointsTotal);

				if ($pointsUsed < $pointsTotal)
					{
					$pointsUsed = (int)\round((float)$pointsTotal + 0.5);
					}
				}
			else
				{
				$pointsUsed = $pointsEarned;
				}
			}
		$invoiceUpdates = [];
		$cartItems = $cartModel->getItems();
		$taxCalculator = new \App\Model\TaxCalculator();

		if ($cartItems)
			{
			\PHPFUI\ORM::beginTransaction();

			if ($pointsUsed > 0)
				{
				$memberRecord = $member->getMember();
				$pointHistory = new \App\Record\PointHistory();
				$pointHistory->member = $memberRecord;
				$pointHistory->oldLeaderPoints = $memberRecord->volunteerPoints;
				$pointHistory->volunteerPoints = $memberRecord->volunteerPoints = $pointsEarned - $pointsUsed;
				$pointHistory->insert();
				$memberRecord->update();
				}
			$instructions = $_POST['instructions'] ?? 'None';
			$invoice = new \App\Record\Invoice();
			$invoice->orderDate = \App\Tools\Date::todayString();
			$invoice->memberId = $cartModel->getCustomerNumber();
			$invoice->totalPrice = $cartModel->getTotal();
			$invoice->totalShipping = $cartModel->getShipping();
			$invoice->totalTax = 0.0;
			$invoice->discount = $cartModel->getDiscount();
			$invoice->paymentDate = null;
			$invoice->pointsUsed = (int)\round($pointsUsed);
			$invoice->paypalPaid = 0.0;
			$invoice->fullfillmentDate = null;
			$invoice->instructions = $instructions;
			$invoice->insert();
			$invoiceUpdates['fullfillmentDate'] = \App\Tools\Date::todayString();

			foreach ($cartItems as $cartItem)
				{
				// skip zero quantity items, makes no sense
				if (! (int)$cartItem['quantity'])
					{
					continue;
					}

				$tax = $taxCalculator->compute($cartItem);
				$invoice->totalTax += $tax;

				switch (\App\Enum\Store\Type::from((int)$cartItem['type']))
					{
					case \App\Enum\Store\Type::STORE:
					case \App\Enum\Store\Type::ORDER:
					case \App\Enum\Store\Type::MEMBERSHIP:
						$storeItem = new \App\Record\StoreItem($cartItem['storeItemId']);

						if ($storeItem->loaded())
							{
							if (! $storeItem->noShipping)
								{
								unset($invoiceUpdates['fullfillmentDate']);
								}

							$storeItemDetail = new \App\Record\StoreItemDetail();

							if (! empty($cartItem['storeItemDetailId']))
								{
								$key = ['storeItemId' => $cartItem['storeItemId'],
									'storeItemDetailId' => $cartItem['storeItemDetailId'], ];
								$storeItemDetail->read($key);
								}

							$shipping = (float)$cartItem['shipping'];
							$invoiceItem = new \App\Record\InvoiceItem();
							$invoiceItem->invoice = $invoice;
							$invoiceItem->storeItemId = $cartItem['storeItemId'];
							$invoiceItem->storeItemDetailId = $cartItem['storeItemDetailId'];
							$invoiceItem->title = $storeItem->title;
							$invoiceItem->description = $storeItem->description;

							if ($storeItemDetail->loaded())
								{
								$invoiceItem->detailLine = $storeItemDetail->detailLine;
								}
							else
								{
								$invoiceItem->detailLine = $cartItem['optionsSelected'];
								}
							$invoiceItem->price = (float)$storeItem->price;
							$invoiceItem->shipping = (float)$storeItem->shipping;
							$invoiceItem->quantity = (int)$cartItem['quantity'];
							$invoiceItem->type = $cartItem['type'];
							$invoiceItem->tax = $tax;
							$invoiceItem->insertOrUpdate();

							if (\App\Enum\Store\Type::STORE->value == $cartItem['type'])
								{
								// remove from inventory
								$storeItemDetail->quantity = $storeItemDetail->quantity - (int)$cartItem['quantity'];
								$storeItemDetail->update();
								}
							}

						break;

					case \App\Enum\Store\Type::GENERAL_ADMISSION:
						unset($invoiceUpdates['fullfillmentDate']);
						$event = new \App\Record\GaEvent($cartItem['storeItemId']);
						$rider = new \App\Record\GaRider($cartItem['storeItemDetailId']);
						$price = $this->gaModel->getPrice($event, $rider);
						$date = $event->eventDate;
						$invoiceItem = new \App\Record\InvoiceItem();
						$invoiceItem->invoice = $invoice;
						$invoiceItem->storeItemId = $cartItem['storeItemId'];
						$invoiceItem->storeItemDetailId = $cartItem['storeItemDetailId'];
						$invoiceItem->title = $event->title . ' Registration ' . $date;
						$invoiceItem->description = $date;
						$invoiceItem->detailLine = $rider->firstName . ' ' . $rider->lastName;
						$invoiceItem->price = $price;
						$invoiceItem->shipping = 0.0;
						$invoiceItem->quantity = 1;
						$invoiceItem->type = \App\Enum\Store\Type::from($cartItem['type']);
						$invoiceItem->tax = $tax;
						$invoiceItem->insert();
						$this->paypalType = 'General_Admission';

						break;

					case \App\Enum\Store\Type::DISCOUNT_CODE:
						$invoiceUpdates['discountCodeId'] = $cartItem['discountCodeId'];

						break;

					case \App\Enum\Store\Type::EVENT:
						break;
					}
				}
			// save the computed tax
			$invoice->update();

			$balanceDue = $invoice->unpaidBalance();
			// delete cart from member
			$cartItemTable = new \App\Table\CartItem();
			$cartItemTable->setWhere(new \PHPFUI\ORM\Condition('memberId', $cartModel->getCustomerNumber()));
			$cartItemTable->delete();

			if ($balanceDue <= 0)
				{
				$invoiceUpdates['paymentDate'] = \App\Tools\Date::todayString();
				}

			if ($invoiceUpdates)
				{
				$invoice->setFrom($invoiceUpdates);
				$invoice->update();
				}

			if (\PHPFUI\ORM::getLastErrors())
				{
				\PHPFUI\ORM::rollBack();
				$invoice = new \App\Record\Invoice();
				}
			else
				{
				\PHPFUI\ORM::commit();
				}

			if ($invoice->invoiceId && $balanceDue <= 0.0)
				{
				$this->execute($invoice);
				}
			}

		return $invoice;
		}

	public function generatePDF(\App\Record\Invoice $invoice) : \App\Report\Invoice
		{
		$pdf = new \App\Report\Invoice();
		$pdf->AddPage();
		$pdf->SetMargins(5, 5, 5);
		$pdf->SetAutoPageBreak(false);
		$file = new \App\Model\ImageFiles();
		$pdf->addVendor($this->settingTable->value('clubName'), $this->settingTable->value('memberAddr') . "\n" . $this->settingTable->value('memberTown') . "\n", $file->get($this->settingTable->value('invoiceLogo')));
		$pdf->addInvoiceNumber($invoice->invoiceId);
		$member = $this->customerModel->read($invoice->memberId);

		if (isset($member->customerId))
			{
			$pdf->addClient('C-' . $member->customerId);
			}
		else
			{
			$pdf->addClient((string)$member->memberId);
			}
		$pdf->addClientAddress("Ship To:\n" . $member->firstName . ' ' . $member->lastName . "\n" . $member->address . "\n" . $member->town . ', ' . $member->state . ' ' . $member->zip);
		$pdf->addTotals($invoice->totalPrice, $invoice->totalTax, $invoice->totalShipping, $invoice->paypalPaid, $invoice->pointsUsed);
		$payment = '';

		if ($invoice->paypalPaid > 0)
			{
			if ($invoice->paypaltx)
				{
				$payment = 'PayPal Transaction Id ' . $invoice->paypaltx;
				}
			elseif ($invoice->paidByCheck)
				{
				$payment = 'Paid by Check';
				}
			else
				{
				$payment = 'Paid by Cash';
				}
			}

		if ($invoice->pointsUsed > 0)
			{
			if (\strlen($payment))
				{
				$payment .= ' / ';
				}
			$payment .= 'Volunteer Points';
			}

		if ($invoice->paidByCheck)
			{
			if (\strlen($payment))
				{
				$payment .= ' / ';
				}

			if ($invoice->paymentDate)
				{
				$payment .= 'Received Check';
				}
			else
				{
				$payment .= 'Unreceived Check';
				}
			}
		$pdf->addInstructions($invoice->instructions);
		$pdf->addPaymentInfo($payment);
		$pdf->addDate(\App\Tools\Date::todayString());
		$pdf->addDateOrdered($invoice->orderDate);
		$date = '';

		if (! empty($invoice->fullfillmentDate))
			{
			$date = $invoice->fullfillmentDate;
			}
		$pdf->addDateShipped($date);
		$cols = ['Item Number' => 23,
			'Description' => 71,
			'Quantity' => 15,
			'Price' => 17,
			'Total Price' => 20,
			'Shipping' => 16,
			'Total with Shipping' => 33, ];
		$pdf->addCols($cols);
		$cols = ['Item Number' => 'C',
			'Description' => 'L',
			'Quantity' => 'C',
			'Price' => 'C',
			'Total Price' => 'C',
			'Shipping' => 'C',
			'Total with Shipping' => 'C', ];
		$pdf->addLineFormat($cols);
		$y = 109;

		foreach ($invoice->InvoiceItemChildren as $invoiceItem)
			{
			$details = \App\Tools\TextHelper::unhtmlentities($invoiceItem->detailLine);
			$title = \App\Tools\TextHelper::unhtmlentities($invoiceItem->title);
			$invoiceItem->price ??= 0.0;
			$invoiceItem->shipping ??= 0.0;
			$line = ['Item Number' => $invoiceItem->storeItemId . '-' . $invoiceItem->storeItemDetailId,
				'Description' => "{$title}\n{$details}",
				'Quantity' => $invoiceItem->quantity,
				'Price' => '$' . \number_format($invoiceItem->price ?? 0.0, 2),
				'Total Price' => '$' . \number_format($invoiceItem->quantity * $invoiceItem->price, 2),
				'Shipping' => '$' . \number_format($invoiceItem->shipping ?? 0, 2),
				'Total with Shipping' => '$' . \number_format($invoiceItem->quantity * $invoiceItem->price + $invoiceItem->quantity * $invoiceItem->shipping, 2), ];
			$size = $pdf->addLine($y, $line);
			$y += $size + 2;

			if (\App\Enum\Store\Type::GENERAL_ADMISSION == $invoiceItem->type)
				{
				$rider = new \App\Record\GaRider($invoiceItem->storeItemDetailId);

				foreach ($rider->optionsSelected as $option)
					{
					$price = $option->price + $option->additionalPrice;
					$line = ['Item Number' => '',
						'Description' => "{$option->optionName}\n   {$option->selectionName}",
						'Quantity' => 1,
						'Price' => '$' . \number_format($price, 2),
						'Total Price' => '$' . \number_format($price, 2),
						'Shipping' => '$0.00',
						'Total with Shipping' => '$' . \number_format($price, 2), ];
					$size = $pdf->addLine($y, $line);
					$y += $size + 2;
					}
				}
			}

		return $pdf;
		}

	/**
	 * @return string[]
	 *
	 * @psalm-return array<array-key, string>
	 */
	public function getChairs(\App\Record\Invoice $invoice) : array
		{
		if ($invoice->empty())
			{
			return [];
			}
		$permissionTable = new \App\Table\Permission();
		$chairs = [];
		$emails = [];

		foreach ($invoice->InvoiceItemChildren as $invoiceItem)
			{
			switch ($invoiceItem->type)
				{
				case \App\Enum\Store\Type::ORDER:
				case \App\Enum\Store\Type::STORE:
					$chairs['Store Shipping'] = 1;

					break;

				case \App\Enum\Store\Type::GENERAL_ADMISSION:
					$chairs[] = $this->gaModel->getChair($invoiceItem->storeItemId);

					break;

				case \App\Enum\Store\Type::DISCOUNT_CODE:
					// nothing to do here!
					break;

				case \App\Enum\Store\Type::MEMBERSHIP:
					$chairs['Membership Chair'] = 1;

					break;

				case \App\Enum\Store\Type::EVENT:
					$chairs[] = $this->reservationModel->getChair($invoiceItem->storeItemId);

					break;
				}
			}

		foreach ($chairs as $chairName => $chair)
			{
			if (\is_array($chair))
				{
				$emails[$chair['email']] = $chair['firstName'] . ' ' . $chair['lastName'];
				}
			else
				{
				$members = $permissionTable->getMembersWithPermissionGroup($chairName);

				foreach ($members ?? [] as $member)
					{
					$emails[$member->email] = $member->fullName();
					}
				}
			}

		return $emails;
		}

	public function getFileName(\App\Record\Invoice $invoice) : string
		{
		return $this->settingTable->value('clubAbbrev') . "_Invoice_{$invoice->invoiceId}.pdf";
		}

	/**
	 * Get the invoice table correctly configured for the search parameters
	 *
	 * @param array<string, string> $parameters
	 */
	public function getInvoiceTable(array $parameters) : \App\Table\Invoice
		{
		$invoiceTable = new \App\Table\Invoice();
		$invoiceTable->addJoin('member');
		$condition = new \PHPFUI\ORM\Condition();

		if (! empty($parameters['invoiceId']))
			{
			$condition->and('invoiceId', (int)$parameters['invoiceId']);
			}

		if (! empty($parameters['name']))
			{
			$nameCondition = new \PHPFUI\ORM\Condition('member.firstName', '%' . $parameters['name'] . '%', new \PHPFUI\ORM\Operator\Like());
			$nameCondition->or('member.lastName', '%' . $parameters['name'] . '%', new \PHPFUI\ORM\Operator\Like());
			$condition->and($nameCondition);
			}

		if (! empty($parameters['shipped']))
			{
			switch ((int)$parameters['shipped'])
				{
				case 1:
					$condition->and('fullfillmentDate', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());

					break;

				case 2:
					$condition->and('fullfillmentDate', null, new \PHPFUI\ORM\Operator\IsNull());
					$condition->and('paymentDate', null, new \PHPFUI\ORM\Operator\IsNotNull());

					break;

				case 3:
					$condition->and('paymentDate', null, new \PHPFUI\ORM\Operator\IsNull());

					break;
				}
			}

		if (! empty($parameters['text']))
			{
			$invoiceTable->addJoin('invoiceItem');
			$invoiceTable->setDistinct();
			$textCondition = new \PHPFUI\ORM\Condition('invoiceItem.title', '%' . $parameters['text'] . '%', new \PHPFUI\ORM\Operator\Like());
			$textCondition->or('invoiceItem.description', '%' . $parameters['text'] . '%', new \PHPFUI\ORM\Operator\Like());
			$textCondition->or('invoiceItem.detailLine', '%' . $parameters['text'] . '%', new \PHPFUI\ORM\Operator\Like());
			$condition->and($textCondition);
			}

		if (! empty($parameters['orderDate_from']))
			{
			$condition->and('orderDate', $parameters['orderDate_from'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if (! empty($parameters['orderDate_through']))
			{
			$condition->and('orderDate', $parameters['orderDate_through'], new \PHPFUI\ORM\Operator\LessThanEqual());
			}

		if (! empty($parameters['paypaltx']))
			{
			$condition->and('paypaltx', $parameters['paypaltx']);
			}
		$invoiceTable->setWhere($condition);

		return $invoiceTable;
		}

	public function getPayPalType() : string
		{
		return $this->paypalType;
		}

	public function markAsReceived(\App\Record\Invoice $invoice) : void
		{
		if (! $invoice->empty())
			{
			$invoice->paymentDate = \App\Tools\Date::todayString();
			$invoice->paypalPaid = $invoice->totalPrice + $invoice->totalTax + $invoice->totalShipping;
			$invoice->update();
			$this->execute($invoice);
			$chair = \App\Model\Session::getSignedInMember();
			$member = $this->customerModel->read($invoice->memberId);
			$pdf = $this->generatePDF($invoice);
			$message = 'Thanks for sending in a check to pay for your recent order. We have marked your invoice as paid. Please see attached. If you have any questions, please reply to this email.';
			$message .= "\n\n{$chair['firstName']} {$chair['lastName']}";
			$email = new \App\Tools\EMail();
			$email->setBody($message);
			$email->setSubject('Your check to ' . $this->settingTable->value('clubAbbrev') . ' has been received');
			$email->setFromMember($chair);
			$email->addBCCMember($chair);
			$email->addToMember($member->toArray());
			$email->addAttachment($pdf->Output('S'), $this->getFileName($invoice));
			$email->send();
			unset($pdf);
			}
		}

	public function markAsShipped(\App\Record\Invoice $invoice) : void
		{
		if (! $invoice->empty())
			{
			$invoice->fullfillmentDate = \App\Tools\Date::todayString();
			$invoice->update();
			$chair = \App\Model\Session::getSignedInMember();
			$member = $this->customerModel->read($invoice->memberId);
			$pdf = $this->generatePDF($invoice);
			$tempName = new \App\Tools\TempFile();
			$pdf->Output($tempName, 'F');
			$message = 'Thanks for your recent purchase from our online store.  I have just packaged this order and will get ' . 'it in the mail shortly.  You should receive it in 3-4 days.  Please see attached invoice.  If you have ' . "any questions, please reply to this email.\n\n{$chair['firstName']} {$chair['lastName']}";
			$email = new \App\Tools\EMail();
			$email->setBody($message);
			$email->setSubject('Your ' . $this->settingTable->value('clubAbbrev') . ' order has shipped');
			$email->setFromMember($chair);
			$email->addBCCMember($chair);
			$email->addToMember($member->toArray());
			$email->addAttachment($tempName, $this->getFileName($invoice));
			$email->send();
			unset($pdf);
			}
		}

	public function requestRefund(\App\Record\Invoice $invoice) : void
		{
		if (! $invoice->empty())
			{
			$memberId = $invoice->memberId;
			$member = $this->customerModel->read($memberId);

			if ($invoice->paypalPaid > 0.0)
				{
				$pdf = $this->generatePDF($invoice);
				$memberPicker = new \App\Model\MemberPicker();
				$treasurer = $memberPicker->getMember('Treasurer');
				$treasurerName = "{$treasurer['firstName']} {$treasurer['lastName']}";
				$email = new \App\Tools\EMail();
				$email->setFromMember($treasurer);
				$email->addBCCMember($treasurer);
				$email->addToMember($member->toArray());
				$email->setSubject($this->settingTable->value('clubName') . ' Refund Request');
				$message = "Dear {$member->firstName} {$member->lastName}<p>";
				$message .= "We are processing your request for a refund for Invoice #{$invoice->invoiceId}. Please see attached invoice to be refunded.</p><p>";
				$message .= "We will be refunding a payment of {$invoice->paypalPaid} paid by PayPal ";
				$message .= "payment number {$invoice->paypaltx} on {$invoice->paymentDate}. ";
				$message .= 'The refund should be done shortly, but please allow some time for processing.</p><p>';
				$message .= "Thanks for your cooperation,</p><p>{$treasurerName}<br>";
				$message .= "{$this->settingTable->value('clubName')} Treasurer</p>";
				$email->addAttachment($pdf->Output('', 'S'), $this->getFileName($invoice));
				$email->setHtml();
				$email->setBody($message);
				$email->send();
				}
			}
		}
	}
