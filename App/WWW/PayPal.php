<?php

namespace App\WWW;

class PayPal extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Tools\Logger $logger;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->logger = \App\Tools\Logger::get();
		}

	public function cancelled(string $paypalType = '', \App\Record\Invoice $invoice = new \App\Record\Invoice(), string $description = '') : void
		{
		$json = \json_decode(\file_get_contents('php://input'), true);
		$container = new \PHPFUI\Container();

		if ($json['orderID'] ?? 'invalid' == $_SESSION['PayPalId'])
			{
			$description = \str_replace('_', ' ', $description);
			$container->add(new \PHPFUI\Header('PayPal Payment Cancelled for ' . $description));
			$container->add(new \PHPFUI\Header('The following order was cancelled:', 4));
			$invoiceView = new \App\View\Invoice($this->page);
			$container->add($invoiceView->status($invoice, true));
			// delete the invoice
			$invoiceModel = new \App\Model\Invoice();
			$invoiceModel->delete($invoice);
			}
		else
			{
			$container->add(new \PHPFUI\Header('PayPal Invalid OrderId ' . $description));
			}
		unset($_SESSION['PayPalId']);
		$response = ['html' => "{$container}"];
		$this->page->setRawResponse(\json_encode($response, JSON_PRETTY_PRINT));
		}

	public function completed(string $paypalType = '', \App\Record\Invoice $invoice = new \App\Record\Invoice(), string $description = '') : void
		{
		$json = \json_decode(\file_get_contents('php://input'), true);
		$container = new \PHPFUI\Container();

		if (($json['orderID'] ?? 'invalid') == ($_SESSION['PayPalId'] ?? ''))
			{
			$description = \str_replace('_', ' ', $description);
			$container->add(new \PHPFUI\Header('Thanks for your PayPal Payment for ' . $description));
			$container->add(new \PHPFUI\Header('You should receive an email confirmation shortly.', 4));
			$invoiceView = new \App\View\Invoice($this->page);
			$container->add($invoiceView->status($invoice));
			}
		else
			{
			$container->add(new \PHPFUI\Header('PayPal Invalid OrderId ' . $description));
			}
		unset($_SESSION['PayPalId']);
		$response = ['html' => "{$container}"];
		$this->page->setRawResponse(\json_encode($response, JSON_PRETTY_PRINT));
		}

	public function completedPayment(string $paypalType = '', \App\Record\Invoice $invoice = new \App\Record\Invoice(), string $description = '') : void
		{
		$json = \json_decode(\file_get_contents('php://input'), true);

		if (! isset($json['orderID']))
			{
			$this->logger->debug($json, __METHOD__ . ' invalid json');

			return;
			}
		$request = new \PayPalCheckoutSdk\Orders\OrdersCaptureRequest($json['orderID']);
		$request->prefer('return=representation');
		$model = new \App\Model\PayPal($paypalType);
		try
			{
			$client = $model->getPayPalClient();
			$response = $client->execute($request);
			}
		catch (\Exception $e)
			{
			$this->logger->debug($e, __METHOD__);
			$this->page->setRawResponse(\json_encode([], JSON_PRETTY_PRINT));

			return;
			}
		$result = $response->result;

		if ($result->id != $json['orderID']) // @phpstan-ignore property.nonObject
			{
			$this->logger->debug(__METHOD__ . ' orderID mismatch');
			}
		elseif ($result->purchase_units[0]->invoice_id != $invoice->invoiceId) // @phpstan-ignore property.nonObject
			{
			$this->logger->debug(__METHOD__ . ' invoiceId mismatch');
			}
		else
			{
			$txn = $result->purchase_units[0]->payments->captures[0]->id; // @phpstan-ignore property.nonObject
			$status = $result->purchase_units[0]->payments->captures[0]->status; // @phpstan-ignore property.nonObject
			$payment_amount = $result->purchase_units[0]->payments->captures[0]->amount->value; // @phpstan-ignore property.nonObject
			$invoiceModel = new \App\Model\Invoice();
			$invoiceModel->executePayment($invoice, $txn, $payment_amount);
			}

		$this->page->setRawResponse(\json_encode($response->result, JSON_PRETTY_PRINT));
		}

	public function createOrder(string $paypalType = '', \App\Record\Invoice $invoice = new \App\Record\Invoice(), string $description = '') : void
		{
		$model = new \App\Model\PayPal($paypalType);
		$response = null;

		try
			{
			$response = $model->createOrderRequest($invoice, $description);
			}
		catch (\Exception $e)
			{
			$this->logger->debug($e, __METHOD__);
			}

		if (! $response)
			{
			return;
			}

		$_SESSION['PayPalId'] = $response->result->id; // @phpstan-ignore property.nonObject
		$this->page->setRawResponse(\json_encode($response->result, JSON_PRETTY_PRINT));
		}

	public function error(string $paypalType = '', \App\Record\Invoice $invoice = new \App\Record\Invoice(), string $description = '') : void
		{
		$json = \json_decode(\file_get_contents('php://input'), true);
		$container = new \PHPFUI\Container();
		$container->add(new \PHPFUI\Header('PayPal error during ' . $description));
		$container->add(new \PHPFUI\Header('We have logged the issue.', 4));

		if (! empty($json['data']['code']))
			{
			$alert = new \PHPFUI\Callout('alert');
			$message = $this->getError($json['data']['code']);
			$this->logger->debug($message, 'PayPal Error');
			$alert->add($message);
			$container->add($alert);
			}
		else
			{
			$this->logger->debug($json, 'PayPal Error');
			}

		$alert = new \PHPFUI\Callout('warning');
		$alert->add('Please check your PayPal account and email before retrying this transaction.  Sometimes PayPal reports this error when the transaction actually was successful.');
		$container->add($alert);

		unset($_SESSION['PayPalId']);
		$response = ['html' => "{$container}"];
		$this->page->setRawResponse(\json_encode($response, JSON_PRETTY_PRINT));
		}

	private function getError(int $code) : string
		{
		$data = [
			0 => 'Approved',
			1 => 'User authentication failed. Error is caused by one or more of the following:<ul><li>Invalid Processor information entered. Contact merchant bank to verify.</li><li>"Allowed IP Address" security feature implemented. The transaction is coming from an unknown IP address. For more information, refer to Allowed IP Addresses.</li><li>You are using a test (not active) account to submit a transaction to the live PayPal servers. Change the URL from pilot-payflowpro.paypal.com to payflowpro.paypal.com.</li></ul>',
			2 => 'Invalid tender type. Your merchant bank account does not support the following credit card type that was submitted.',
			3 => 'Invalid transaction type. Transaction type is not appropriate for this transaction. For example, you cannot credit an authorization-only transaction.',
			4 => 'Invalid amount format or a $0 transaction.',
			5 => 'Invalid merchant information. Processor does not recognize your merchant account information. Contact your bank account acquirer to resolve this problem.',
			6 => 'Invalid or unsupported currency code',
			7 => 'Field format error. Invalid information entered.',
			8 => 'Not a transaction server',
			9 => 'Too many parameters or invalid stream',
			10 => 'Too many line items',
			11 => 'Client time-out waiting for response',
			12 => 'Declined. Check the credit card number, expiration date, and transaction information to make sure they were entered correctly. If this does not resolve the problem, have the customer call their card issuing bank to resolve.',
			13 => 'Referral. Transaction cannot be approved electronically but can be approved with a verbal authorization. Contact your merchant bank to obtain an authorization and submit a manual Voice Authorization transaction.  ',
			14 => 'Invalid Client Certification ID. Check the HTTP header. If the tag, X-VPS-VIT-CLIENT-CERTIFICATION-ID, is missing, RESULT code 14 is returned.',
			19 => 'Original transaction ID not found. The transaction ID you entered for this transaction is not valid.',
			20 => 'Cannot find the customer reference number',
			22 => 'Invalid ABA number',
			23 => 'Invalid account number. Check credit card number and re-submit.',
			24 => 'Invalid expiration date. Check and re-submit.',
			25 => 'Invalid Host Mapping. You are trying to process a tender type such as Discover Card, but you are not set up with your merchant bank to accept this card type.',
			26 => 'Invalid vendor account',
			27 => 'Insufficient partner permissions',
			28 => 'Insufficient user permissions',
			29 => 'Invalid XML document. This could be caused by an unrecognized XML tag or a bad XML format that cannot be parsed by the system.',
			30 => 'Duplicate transaction',
			31 => 'Error in adding the recurring profile',
			32 => 'Error in modifying the recurring profile',
			33 => 'Error in canceling the recurring profile',
			34 => 'Error in forcing the recurring profile',
			35 => 'Error in reactivating the recurring profile',
			36 => 'OLTP Transaction failed',
			37 => 'Invalid recurring profile ID',
			50 => 'Insufficient funds available in account',
			51 => 'Exceeds per transaction limit',
			99 => 'General error. See RESPMSG.',
			100 => 'Transaction type not supported by host',
			101 => 'Time-out value too small',
			102 => 'Processor not available',
			103 => 'Error reading response from host',
			104 => 'Timeout waiting for processor response. Try your transaction again.',
			105 => 'Credit error. Make sure you have not already credited this transaction, or that this transaction ID is for a creditable transaction. (For example, you cannot credit an authorization.)',
			106 => 'Host not available',
			107 => 'Duplicate suppression time-out',
			108 => 'Void error. Make sure the transaction ID entered has not already been voided. If not, then look at the Transaction Detail screen for this transaction to see if it has settled. (The Batch field is set to a number greater than zero if the transaction has been settled). If the transaction has already settled, your only recourse is a reversal (credit a payment or submit a payment for a credit).',
			109 => 'Time-out waiting for host response',
			110 => 'Referenced auth (against order) Error',
			111 => 'Capture error. Either an attempt to capture a transaction that is not an authorization transaction type, or an attempt to capture an authorization transaction that has already been captured.',
			112 => 'Failed AVS check. Address and ZIP code do not match. An authorization may still exist on the cardholder’s account.',
			113 => 'Merchant sale total will exceed the sales cap with current transaction. ACH transactions only.',
			114 => 'Card Security Code mismatch. An authorization may still exist on the cardholder’s account.',
			115 => 'System busy, try again later',
			116 => 'PayPal Internal error. Failed to lock terminal number',
			117 => 'Failed merchant rule check. One or more of the following three failures occurred:<ul><li>An attempt was made to submit a transaction that failed to meet the security settings specified on the PayPal Manager Security Settings page. If the transaction exceeded the Maximum Amount security setting, then no values are returned for AVS or Card Security Code.</li><li>AVS validation failed. The AVS return value should appear in the RESPMSG.</li><li>Card Security Code validation failed. The Card Security Code return value should appear in the RESPMSG.</li></ul>',
			118 => 'Invalid keywords found in string fields',
			119 => 'General failure within PIM Adapter',
			120 => 'Attempt to reference a failed transaction',
			121 => 'Not enabled for feature',
			122 => 'Merchant sale total will exceed the credit cap with current transaction. ACH transactions only.',
			125 => 'Fraud Protection Services Filter — Declined by filters',
			126 => 'Fraud Protection Services Filter — Flagged for review by filters<ul><li>Important Note: Result code 126 indicates that a transaction triggered a fraud filter. This is not an error, but a notice that the transaction is in a review status. The transaction has been authorized but requires you to review and to manually accept the transaction before it will be allowed to settle.</li></li><li>If this is a new Payflow account, this result occurred because all new accounts include a “test drive” of the Fraud Protection Services at no charge. The filters are on by default. You can modify these settings based on your business needs.</li></li><li>Result code 126 is intended to give you an idea of the kind of transaction that is considered suspicious to enable you to evaluate whether you can benefit from using the Fraud Protection Services.</li></li><li>To eliminate result 126, turn the filters off.</li></ul>',
			127 => 'Fraud Protection Services Filter — Not processed by filters',
			128 => 'Fraud Protection Services Filter — Declined by merchant after being flagged for review by filters',
			131 => 'Version 1 Payflow SDK client no longer supported. Upgrade to the most recent version of the Payflow client.',
			132 => 'Card has not been submitted for update',
			133 => 'Data mismatch in HTTP retry request',
			150 => 'Issuing bank timed out',
			151 => 'Issuing bank unavailable',
			200 => 'Reauth error',
			201 => 'Order error',
			402 => 'PIM Adapter Unavailable',
			403 => 'PIM Adapter stream error',
			404 => 'PIM Adapter Timeout',
			600 => 'Cybercash Batch Error',
			601 => 'Cybercash Query Error',
			1000 => 'Generic host error. This is a generic message returned by your credit card processor. The RESPMSG will contain more information describing the error.',
			1001 => 'Buyer Authentication unavailable',
			1002 => 'Buyer Authentication — Transaction timeout',
			1003 => 'Buyer Authentication — Invalid client version',
			1004 => 'Buyer Authentication — Invalid timeout value',
			1011 => 'Buyer Authentication unavailable',
			1012 => 'Buyer Authentication unavailable',
			1013 => 'Buyer Authentication unavailable',
			1014 => 'Buyer Authentication — Merchant is not enrolled for Buyer Authentication Service (3-D Secure).',
			1016 => 'Buyer Authentication — 3-D Secure error response received. Instead of receiving a PARes response to a Validate Authentication transaction, an error response was received.',
			1017 => 'Buyer Authentication — 3-D Secure error response is invalid. An error response is received and the response is not well formed for a Validate Authentication transaction.',
			1021 => 'Buyer Authentication — Invalid card type',
			1022 => 'Buyer Authentication — Invalid or missing currency code',
			1023 => 'Buyer Authentication — merchant status for 3D secure is invalid',
			1041 => 'Buyer Authentication — Validate Authentication failed: missing or invalid PARES',
			1042 => 'Buyer Authentication — Validate Authentication failed: PARES format is invalid',
			1043 => 'Buyer Authentication — Validate Authentication failed: Cannot find successful Verify Enrollment',
			1044 => 'Buyer Authentication — Validate Authentication failed: Signature validation failed for PARES',
			1045 => 'Buyer Authentication — Validate Authentication failed: Mismatched or invalid amount in PARES',
			1046 => 'Buyer Authentication — Validate Authentication failed: Mismatched or invalid acquirer in PARES',
			1047 => 'Buyer Authentication — Validate Authentication failed: Mismatched or invalid Merchant ID in PARES',
			1048 => 'Buyer Authentication — Validate Authentication failed: Mismatched or invalid card number in PARES',
			1049 => 'Buyer Authentication — Validate Authentication failed: Mismatched or invalid currency code in PARES',
			1050 => 'Buyer Authentication — Validate Authentication failed: Mismatched or invalid XID in PARES',
			1051 => 'Buyer Authentication — Validate Authentication failed: Mismatched or invalid order date in PARES',
			1052 => 'Buyer Authentication — Validate Authentication failed: This PARES was already validated for a previous Validate Authentication transaction',
		];

		return $data[$code] ?? "Unknown error {$code}";
		}
	}
