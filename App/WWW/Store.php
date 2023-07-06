<?php

namespace App\WWW;

class Store extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	protected \App\DB\MemberCustomer $customer;

	protected int $customerId;

	private readonly \App\Model\Customer $customerModel;

	private readonly \App\Model\Invoice $invoiceModel;

	private readonly \App\View\Invoice $invoiceView;

	private readonly \App\View\Store $storeView;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->invoiceView = new \App\View\Invoice($this->page);
		$this->storeView = new \App\View\Store($this->page);
		$this->invoiceModel = new \App\Model\Invoice();
		$this->customerModel = new \App\Model\Customer();
		$this->customerId = $this->customerModel->getNumber();
		$this->customer = $this->customerModel->read($this->customerId);
		}

	public function addItem() : void
		{
		if ($this->page->addHeader('Add Store Item'))
			{
			$storeEdit = new \App\View\Store\Edit($this->page);
			$this->page->addPageContent($storeEdit->edit(new \App\Record\StoreItem()));
			}
		}

	public function address() : void
		{
		if ($this->customerId > 0)  // member sale
			{
			$view = new \App\View\Member($this->page);
			$member = new \App\Record\Member($this->customerId);
			$this->page->addPageContent($view->edit($member, true));
			$this->page->addPageContent(new \PHPFUI\Button('Checkout', '/Store/checkout'));
			}
		else  // public sale
			{
			$this->page->setPublic();
			$view = new \App\View\Customer($this->page, $this->customerModel);
			$this->page->addPageContent($view->edit($this->customerId));
			}
		}

	public function cancelOrder(\App\Record\Invoice $invoice) : void
		{
		$this->page->setPublic();
		$isAuthorized = $this->page->isAuthorized('Cancel Order');

		if (! $invoice->empty())
			{
			if ($invoice['memberId'] == $this->customerId || $isAuthorized)
				{
				if ($invoice['paypalPaid'] > 0.0)
					{
					$this->page->addPageContent(new \PHPFUI\Header("Refund Requested for Invoice {$invoice->invoiceId}"));
					$this->invoiceModel->requestRefund($invoice);
					$this->invoiceModel->delete($invoice);
					}
				else
					{
					$this->page->addPageContent(new \PHPFUI\Header("Invoice {$invoice->invoiceId} Canceled"));
					$this->invoiceModel->delete($invoice);
					}
				$this->page->redirect('/Store/Invoice/myUnpaid', '', 2);
				}
			else
				{
				$this->page->notAuthorized();
				}
			}
		elseif ($isAuthorized)
			{
			$this->page->addPageContent(new \PHPFUI\Header("Invoice {$invoice->invoiceId} Not Found"));
			}
		else
			{
			$this->page->notAuthorized();
			}
		}

	public function cart() : void
		{
		$this->page->setPublic();
		$this->page->addHeader('My Cart');
		$cartModel = new \App\Model\Cart();
		$cartView = new \App\View\Store\Cart($this->page, $cartModel);
		$cart = $cartView->show(new \PHPFUI\Form($this->page), true);

		if ($cartModel->getItems())
			{
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$checkOut = new \PHPFUI\Submit('Check Out');
			$checkOut->addClass('success');
			$buttonGroup->addButton($checkOut);
			$update = new \PHPFUI\Submit('Update Cart');
			$buttonGroup->addButton($update);
			$shop = new \PHPFUI\Submit('Continue Shopping');
			$shop->addClass('info');
			$buttonGroup->addButton($shop);
			$cart->add($buttonGroup);
			$this->page->addPageContent($cart);
			}
		else
			{
			$this->page->addPageContent($cartView->showEmpty($this->customerId));
			}
		}

	public function checkOut(string $badDiscountCode = '') : void
		{
		$errors = null;
		$this->page->setPublic();

		$model = new \App\Model\Cart();
		$model->compute($this->customer->volunteerPoints ?? 0);

		$cartView = new \App\View\Store\Cart($this->page, $model);

		$errors = $this->customer->validate();

		if ($errors)
			{
			$customerView = new \App\View\Customer($this->page, $this->customerModel);
			$form = $customerView->edit($this->customerId, false);

			$output = new \PHPFUI\Container();
			$output->add(new \PHPFUI\Header('Please correct the following errors', 4));
			$output->add(new \App\UI\ErrorCallout($errors));
			$output->add($form);
			$this->page->addPageContent($output);
			}
		else
			{
			$this->page->addPageContent($cartView->checkOut($this->customer, $badDiscountCode));
			}
		}

	public function configuration() : void
		{
		if ($this->page->addHeader('Store Configuration'))
			{
			$this->page->addPageContent($this->storeView->configuration());
			}
		}

	public function edit(\App\Record\StoreItem $storeItem = new \App\Record\StoreItem()) : void
		{
		if ($this->page->addHeader('Edit Store Item'))
			{
			$storeEdit = new \App\View\Store\Edit($this->page);
			$this->page->addPageContent($storeEdit->edit($storeItem));
			}
		}

	public function email() : void
		{
		if ($this->page->addHeader('Email Buyers'))
			{
			$this->page->addPageContent(new \App\View\Email\Buyers($this->page));
			}
		}

	public function find() : void
		{
		if ($this->page->addHeader('Find Invoice'))
			{
			$view = new \App\View\Invoice\Search($this->page);
			$this->page->addPageContent($view);
			}
		}

	public function item(\App\Record\StoreItem $storeItem = new \App\Record\StoreItem()) : void
		{
		if (! $storeItem->storeItemId)
			{
			$this->page->redirect('/Store/shop');
			}
		else
			{
			$this->page->setPublic();
			$this->page->addPageContent($this->storeView->item($storeItem));
			}
		}

	public function mailingLabel(\App\Record\Invoice $invoice = new \App\Record\Invoice()) : void
		{
		if ($this->page->isAuthorized('Print Mailing Label'))
			{
			$label = new \App\Report\Label();

			if (! $invoice->empty())
				{
				$customerModel = new \App\Model\Customer();
				$mailTo = $customerModel->read($invoice->memberId);
				$label->download($invoice, $mailTo->toArray());
				}
			}
		}

	public function myOrders() : void
		{
		if ($this->page->addHeader('My Completed Orders'))
			{
			$invoiceTable = new \App\Table\Invoice();
			$invoiceTable->setCompletedForMember($this->customerId);
			$this->page->addPageContent($this->invoiceView->show($invoiceTable));
			}
		}

	public function paid(int $invoiceId = 0) : void
		{
		$this->page->setPublic();
		$this->page->addHeader('Your Order Is Complete');
		$this->page->addSubHeader('Your Invoice Number is ' . $invoiceId);
		$this->page->addPageContent('You should receive an email with your invoice shortly');
		}

	public function pay(\App\Record\Invoice $invoice = new \App\Record\Invoice(), string $paypalType = 'Store') : void
		{
		$this->page->setPublic();

		if ($invoice->empty())
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Invoice Not Found'));

			return;
			}

		$unpaidBalance = $invoice->unpaidBalance();

		if ($unpaidBalance <= 0)
			{
			$this->page->redirect('/Store/paid/' . $invoice->invoiceId);
			}
		elseif ($invoice->memberId == $this->customerId)
			{
			$container = new \PHPFUI\HTML5Element('div');
			$container->add(new \PHPFUI\Header('Pay For Your Order'));
			$view = new \App\View\PayPal($this->page, new \App\Model\PayPal($paypalType));
			$container->add($view->getPayPalLogo());
			$owe = '<p>You owe $' . \number_format($unpaidBalance, 2) . ' to complete this order.';
			$container->add($owe);
			$container->add($view->getCheckoutForm($invoice, $container->getId(), $paypalType));
			$this->page->addPageContent($container);
			}
		}

	public function payCheck(\App\Record\Invoice $invoice = new \App\Record\Invoice()) : void
		{
		$this->page->setPublic();

		if ($invoice->empty())
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Invoice Not Found'));

			return;
			}

		$unpaidBalance = $invoice->unpaidBalance();

		if ($unpaidBalance <= 0)
			{
			$this->page->redirect('/Store/paid/' . $invoice->invoiceId);
			}
		elseif ($invoice->memberId->{$this}->customerId)
			{
			$invoice->paidByCheck = 1;
			$invoice->update();
			$this->page->addPageContent(new \PHPFUI\Header('Pay By Check'));
			$settings = new \App\Table\Setting();
			$alert = new \App\UI\Alert('Your order will not be complete until we receive your check. We will email you a confirmation of your payment.');
			$alert->addClass('warning');
			$this->page->addPageContent($alert);
			$text = '<p>Please send a check for $' . ($unpaidBalance);
			$text .= ' made out to: <strong>' . $settings->value('clubName') . '</strong>';
			$text .= "<p>Please write <strong>Invoice #{$invoice->invoiceId}</strong> on the check to insure you will be properly credited.";
			$text .= '<p>To:<blockquote><strong>';
			$text .= $settings->value('clubName');
			$text .= '</strong><br>';
			$text .= $settings->value('memberAddr');
			$text .= '<br>';
			$text .= $settings->value('memberTown');
			$text .= '</blockquote>';
			$this->page->addPageContent($text);
			$email = new \App\Tools\EMail();
			$pdf = $this->invoiceModel->generatePDF($invoice);
			$email->addAttachment($pdf->Output('S'), $this->invoiceModel->getFileName($invoice));
			$email->setSubject('Please send a check to ' . $settings->value('clubAbbrev') . ' for invoice ' . $invoice->invoiceId);
			$memberPicker = new \App\Model\MemberPicker('Store Manager');
			$storeManager = $memberPicker->getMember();
			$email->setFromMember($storeManager);
			$email->setToMember($this->customer->toArray());
			$text .= '<p>Please see attached unpaid invoice. You will receive a confirmation once we receive your payment.<p>Thanks<p>';
			$text .= $storeManager['firstName'] . ' ' . $storeManager['lastName'] . '<br>Store Manager';
			$email->setBody($text);
			$email->setHtml();
			$email->send();
			}
		}

	public function photo(\App\Record\StorePhoto $photo) : never
		{
		$thumbModel = new \App\Model\StoreImages($photo->toArray());

		echo $thumbModel->getPhotoImg();

		exit;
		}

	public function pullList() : void
		{
		if ($this->page->isAuthorized('Unshipped Invoices'))
			{
			$report = new \App\Report\PullList();
			$report->download(\App\Table\InvoiceItem::getUnshippedItems());
			$this->page->done();
			}
		}

	public function ridePullList() : void
		{
		if ($this->page->isAuthorized('Unshipped Invoices'))
			{
			$date = $_GET['rideDate'] ?? \App\Tools\Date::todayString();
			$report = new \App\Report\RidePullList();
			$report->download($date);
			$this->page->done();
			}
		}

	public function shop() : void
		{
		$this->page->setPublic();
		$this->page->addPageContent($this->storeView->shop(new \App\Model\Cart()));
		}

	public function upload() : void
		{
		if ($this->page->isAuthorized('Edit Store Item'))
			{
			$config = new \Flow\Config();
			$config->setTempDir(PROJECT_ROOT . '/files/chunkUploader');
			$file = new \Flow\File($config);

			if ('GET' === $_SERVER['REQUEST_METHOD'])
				{
				if ($file->checkChunk())
					{
					\header('HTTP/1.1 200 Ok');
					}
				else
					{
					\header('HTTP/1.1 204 No Content');
					}
				}
			else
				{
				if ($file->validateChunk())
					{
					$file->saveChunk();
					}
				else
					{
					// error, invalid chunk upload request, retry
					\header('HTTP/1.1 400 Bad Request');
					}
				}

			if ($file->validateFile())
				{
				$storePhoto = new \App\Record\StorePhoto();
				$storePhoto->storeItemId = (int)$_POST['storeItemId'];
				// break up file name to be compatible with other files
				$filename = $_POST['flowFilename'];
				$storePhoto->filename = \substr((string)$filename, 0, \strrpos((string)$filename, '.'));
				$storePhoto->extension = \substr((string)$filename, \strrpos((string)$filename, '.'));
				$storePhotoTable = new \App\Table\StorePhoto();
				$storePhotoTable->setWhere(new \PHPFUI\ORM\Condition('storeItemId', $storePhoto->storeItemId));
				$storePhotoTable->setLimit(1);
				$storePhotoTable->setOrderBy('sequence', 'DESC');
				$storePhoto->sequence = $storePhotoTable->getRecordCursor()->current()->sequence + 1;
				$storePhoto->insert();
				$file->save(PUBLIC_ROOT . 'images/storePhotos/' . (string)$storePhoto->storePhotoId . $storePhoto->extension);

				// make thumbnails
				$thumbModel = new \App\Model\StoreImages();
				$thumbModel->update($storePhoto->toArray());
				$settings = new \App\Table\Setting();
				$thumbModel->createThumb((int)$settings->value('thumbnailSize'));
				}
			}
		}
	}
