<?php

namespace App\Model;

class PayPal
	{
	protected string $sandbox;

	private readonly \App\Table\Setting $settingTable;

	/**
	 * make a model to return PayPal info
	 *
	 * @param string $type type of model, Membership, Events,
	 *               Store, Refunds, etc
	 */
	public function __construct(protected string $type = '', protected ?\App\Tools\Logger $logger = null)
		{
		$this->logger = $logger ?: new \App\Tools\Logger();
		$this->logger->setAlwaysFlush();
		$type = \str_replace(' ', '_', $type);
		$this->settingTable = new \App\Table\Setting();
		// is sandbox turned on for this or not?
		$this->sandbox = $type ? $this->settingTable->value('PayPal_' . $type) : '';
		}

	public function createOrderRequest(\App\Record\Invoice $invoice, string $description = 'Store') : \PayPalHttp\HttpResponse
		{
		$request = new \PayPalCheckoutSdk\Orders\OrdersCreateRequest();
		$request->prefer('return=representation');
		$order = $this->getOrder($invoice, $description);
		$request->body = ['error' => 'Invalid InvoiceId'];

		if ($order)
			{
			$request->body = $order->getData();
			}
		$client = $this->getPayPalClient();
		$response = $client->execute($request);

		return $response;
		}

	public function enableSandbox() : static
		{
		$this->sandbox = 'sandbox';

		return $this;
		}

	public function getAccountId(string $type = '') : string
		{
		return $this->getSetting($type . 'AccountId');
		}

	public function getClientId(string $type = '') : string
		{
		return $this->getSetting($type . 'ClientId');
		}

	public function getInstructions() : string
		{
		return $this->settingTable->value('PayPal_instructions');
		}

	public function getLogo() : string
		{
		$baseDirectory = '/images/paypal/';

		foreach (\glob(PUBLIC_ROOT . $baseDirectory . '*.*') as $file)
			{
			return $baseDirectory . \basename((string)$file);
			}

		return '';
		}

	public function getOrder(\App\Record\Invoice $invoice, string $description = 'Store') : ?\PHPFUI\PayPal\Order
		{
		if ($invoice->empty())
			{
			$this->logger->debug('empty invoice');

			return null;
			}

		$unpaidBalance = $invoice->unpaidBalance();

		if ($unpaidBalance <= 0.0)
			{
			$this->logger->debug($unpaidBalance);

			return null;
			}

		$customerModel = new \App\Model\Customer();
		$customer = $customerModel->read($invoice->memberId);

		$items = $invoice->InvoiceItemChildren;

		if (! \count($items))
			{
			$this->logger->debug('no items');

			return null;
			}

		$shipping = new \PHPFUI\PayPal\Shipping();
		$shipping->method = 'United States Postal Service';
		$address = new \PHPFUI\PayPal\Address();
		$address->address_line_1 = $customer->address;
		$address->admin_area_2 = $customer->town;
		$address->admin_area_1 = $customer->state;
		$address->postal_code = $customer->zip;
		$address->country_code = 'US';
		$shipping->address = $address;

		$purchaseUnit = new \PHPFUI\PayPal\PurchaseUnit();
		$purchaseUnit->description = 'Your ' . $this->settingTable->value('clubName') . ' purchase';
		$purchaseUnit->custom_id = 'Invoice-' . $invoice->invoiceId;
		$purchaseUnit->invoice_id = "{$invoice->invoiceId}";
		$purchaseUnit->shipping = $shipping;

		$itemTotal = 0.0;

		foreach ($items as $item)
			{
			$itm = new \PHPFUI\PayPal\Item($item->title, $item->quantity, new \PHPFUI\PayPal\Currency($item->price));
			$itemTotal += $item->quantity * $item->price;
			$itm->description = \Soundasleep\Html2Text::convert($item->description ?? '', ['drop_links' => true, 'ignore_errors' => true]);
			$purchaseUnit->addItem($itm);

			if (\App\Model\Cart::TYPE_GA == $item->type)
				{
				$rider = new \App\Record\GaRider($item['storeItemDetailId']);

				foreach ($rider->optionsSelected as $option)
					{
					$price = $option->price + $option->additionalPrice;
					$itm = new \PHPFUI\PayPal\Item($option->optionName, 1, new \PHPFUI\PayPal\Currency($price));
					$itemTotal += $price;
					$itm->description = $option->selectionName;
					$purchaseUnit->addItem($itm);
					}
				}
			}

		$breakdown = new \PHPFUI\PayPal\Breakdown();
		$breakdown->shipping = new \PHPFUI\PayPal\Currency($invoice->totalShipping);

		if (0.0 != $invoice->discount || 0.0 != $invoice->pointsUsed)
			{
			$breakdown->discount = new \PHPFUI\PayPal\Currency((float)($invoice->discount + $invoice->pointsUsed));
			}
		$breakdown->tax_total = new \PHPFUI\PayPal\Currency($invoice->totalTax);
		$breakdown->item_total = new \PHPFUI\PayPal\Currency($itemTotal);

		$amount = new \PHPFUI\PayPal\Amount();
		$amount->setCurrency(new \PHPFUI\PayPal\Currency($unpaidBalance));
		$amount->breakdown = $breakdown;
		$purchaseUnit->amount = $amount;

		$applicationContext = new \PHPFUI\PayPal\ApplicationContext();
		$applicationContext->locale = 'en-US';
		$applicationContext->shipping_preference = 'GET_FROM_FILE';
		$applicationContext->user_action = 'PAY_NOW';
		$baseUrl = $this->settingTable->value('homePage');
		$applicationContext->return_url = "{$baseUrl}/PayPal/completed/{$this->type}/{$invoice->invoiceId}/{$description}";
		$applicationContext->cancel_url = "{$baseUrl}/PayPal/cancel/{$this->type}/{$invoice->invoiceId}/{$description}";

		$order = new \PHPFUI\PayPal\Order('CAPTURE');
		$order->addPurchaseUnit($purchaseUnit);
		$order->application_context = $applicationContext;

		return $order;
		}

	public function getPayPalClient() : \PayPalCheckoutSdk\Core\PayPalHttpClient
		{
		if ($this->getSandbox())
			{
			$env = new \PayPalCheckoutSdk\Core\SandboxEnvironment($this->getClientId(), $this->getSecret());
			}
		else
			{
			$env = new \PayPalCheckoutSdk\Core\ProductionEnvironment($this->getClientId(), $this->getSecret());
			}

		return new \PayPalCheckoutSdk\Core\PayPalHttpClient($env);
		}

	/**
	 * @return string blank for production or 'sandbox' if sandbox
	 */
	public function getSandbox() : string
		{
		return $this->sandbox;
		}

	public function getSecret(string $type = '') : string
		 {
		 return $this->getSetting($type . 'Secret');
		 }

	/**
	 * @return string $type type of model, Membership, Events, Store, Refunds, etc
	 */
	public function getType() : string
		{
		return $this->type;
		}

	public function getUrl() : string
		{
		if ($this->getSandbox())
			{
			$env = new \PayPalCheckoutSdk\Core\SandboxEnvironment($this->getClientId(), $this->getSecret());
			}
		else
			{
			$env = new \PayPalCheckoutSdk\Core\ProductionEnvironment($this->getClientId(), $this->getSecret());
			}

		return $env->baseUrl();
		}

	/**
	 * @param array<string,string> $parameters
	 */
	public function save(array $parameters) : void
		{
		foreach ($parameters as $key => $value)
			{
			if (0 === \stripos($key, 'PayPal_'))
				{
				$this->settingTable->save($key, $value);
				}
			}

		foreach (['', 'Sandbox', ] as $type)
			{
			foreach (['AccountId', 'ClientId', 'Secret'] as $field)
				{
				if (isset($parameters[$key = 'PayPal' . $type . $field]))
					{
					$this->settingTable->save($key, $parameters[$key]);
					}
				}
			}
		}

	private function getSetting(string $setting) : string
		{
		$type = \ucfirst(\str_replace('.', '', $this->sandbox));

		return $this->settingTable->value("PayPal{$type}{$setting}");
		}
	}
