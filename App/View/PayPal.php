<?php

namespace App\View;

class PayPal
	{
	protected string $buttonText;

	protected string $logo;

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Model\PayPal $paypalModel)
		{
		$this->logo = $this->paypalModel->getLogo();
		$this->settingTable = new \App\Table\Setting();
		$this->buttonText = $this->logo ? 'Change Logo' : 'Add Logo';

		if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
			{
			if ($_POST['submit'] == $this->buttonText)
				{
				@\unlink(PUBLIC_ROOT . $this->logo);
				$imageModel = new \App\Model\PayPalLogo();
				$allowedFiles = ['.jpg' => 'image/jpeg',
					'.gif' => 'image/gif',
					'.png' => 'image/png', ];

				if ($imageModel->upload('PayPalLogo', 'logo', $_FILES, $allowedFiles))
					{
					\App\Model\Session::setFlash('success', 'Logo uploaded successfully');
					}
				else
					{
					\App\Model\Session::setFlash('alert', $imageModel->getLastError());
					}
				$this->page->redirect();
				}
			}
		}

	public function addPayPalTerms(\PHPFUI\Form $form, ?\PHPFUI\HTML5Element $toggleElement = null) : void
		{
		$form->add(new \PHPFUI\Header('You must agree to the terms below to continue.', 5));
		$form->setAreYouSure(false);

		$paypalTerms = $this->settingTable->value('PayPalTerm');
		$paypalTerms = \str_replace("\n", '<br>', $paypalTerms);
		$panel = new \PHPFUI\Panel($paypalTerms);
		$panel->setCallout()->setRadius();
		$form->add($panel);

		$terms = new \PHPFUI\Input\CheckBoxBoolean('', 'I agree to the ' . $this->settingTable->value('clubName') . ' PayPal terms and conditions.');
		$form->add($terms);

		$terms->getId();

		if ($toggleElement)
			{
			$elementId = $toggleElement->getId();
			$dollar = '$';
			$terms->setAttribute('onclick', "{$dollar}(\"#{$elementId}\").toggleClass(\"hide\")");
			}
		}

	/**
	 * 	 * return a PayPal checkout form for the given invoice
	 * 	 *
	 *
	 * @param string $id id of the html element that will be replaced with success or failure status
	 * @param string $description English description where the transaction came from
	 *
	 */
	public function getCheckoutForm(\App\Record\Invoice $invoice, string $id, string $description = 'Store') : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addClass('hide');

		$checkout = new \PHPFUI\PayPal\Checkout($this->page, $this->paypalModel->getClientId());
		$checkout->addOption('enable-funding', 'venmo')->addOption('currency', 'USD');
		$description = \str_replace(' ', '_', $description);
		$head = $this->settingTable->value('homePage') . '/PayPal/';
		$tail = "/{$this->paypalModel->getType()}/{$invoice->invoiceId}/{$description}";
		$executeUrl = $head . 'completedPayment' . $tail;
		$createOrderUrl = $head . 'createOrder' . $tail;
		$completedUrl = $head . 'completed' . $tail;
		$cancelledUrl = $head . 'cancelled' . $tail;
		$errorUrl = $head . 'error' . $tail;
		$dollar = '$';
		$checkout->setFunctionJavaScript('onCancel', "{$dollar}.post('{$cancelledUrl}',JSON.stringify({orderID:data.orderID}),function(data){{$dollar}('#{$id}').html(data.html)})");
		$checkout->setFunctionJavaScript('onError', "{$dollar}.post('{$errorUrl}',JSON.stringify({data:data,actions:actions}),function(data){{$dollar}('#{$id}').html(data.html)})");
		$checkout->setFunctionJavaScript('createOrder', "return fetch('{$createOrderUrl}',{method:'post',headers:{'content-type':'application/json'}}).then(function(res){return res.json();}).then(function(data){return data.id;})");
		$checkout->setFunctionJavaScript('onApprove', "return fetch('{$executeUrl}',{method:'POST',headers:{'content-type':'application/json'},body:JSON.stringify({orderID:data.orderID})}).then(function(res){return res.json();}).then(function(details){if(details.error==='INSTRUMENT_DECLINED'){return actions.restart();}$.post('{$completedUrl}',JSON.stringify({orderID:data.orderID}),function(data){{$dollar}('#{$id}').html(data.html)})})");

		$checkout->addClass('hide');
		$this->addPayPalTerms($form, $checkout);
		$form->add($checkout);

		$form->add($buttonGroup);

		return $form;
		}

	public function getEditor(string $type = '') : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save', "submit{$type}");
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->paypalModel->save($_POST);
			$this->page->setResponse('Saved');
			}
		else
			{
			$column = new \PHPFUI\Cell(12);
			$accountId = new \PHPFUI\Input\Text("PayPal{$type}AccountId", "PayPal {$type} Account Id", $this->paypalModel->getAccountId($type));
			$accountId->setRequired(! $type);
			$accountId->setToolTip('This is the PayPal Merchant Account Id. It is used for subscriptions');
			$column->add($accountId);
			$clientId = new \PHPFUI\Input\Text("PayPal{$type}ClientId", "Client ID for REST API associated with above {$type} Email address", $this->paypalModel->getClientId($type));
			$clientId->setRequired(! $type);
			$clientId->setToolTip('The Client Id is a long string of letters and numbers.');
			$column->add($clientId);
			$secret = new \PHPFUI\Input\PasswordEye("PayPal{$type}Secret", "Secret for REST API associated with above {$type} Email address", $this->paypalModel->getSecret($type));
			$secret->setRequired(! $type);
			$secret->setToolTip('The Secret is a long string of letters and numbers.');
			$column->add($secret);
			$form->add($column);
			$form->add($submit);
			}

		return $form;
		}

	public function getPayPalLogo() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$container->add(new \PHPFUI\Header($this->settingTable->value('clubName') . ' is PayPal Verified', 3));
		$container->add(
			<<<PAYPAL
<a href="https://www.paypal.com/webapps/mpp/paypal-popup" title="How PayPal Works" onclick="javascript:window.open('https://www.paypal.com/webapps/mpp/paypal-popup','WIPayPal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700'); return false;"><img src="https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg" border="0" alt="PayPal Acceptance Mark"></a>
PAYPAL
		);
		$row = new \PHPFUI\GridX();
		$panel = new \PHPFUI\Panel($this->paypalModel->getInstructions());
		$panel->setRadius();
		$row->add($panel);
		$container->add($row);

		return $container;
		}

	public function getSettings() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save', 'submitsettings');
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->paypalModel->save($_POST);
			$this->page->setResponse('Saved');
			}
		else
			{
			$addLogoButton = new \PHPFUI\Button($this->buttonText);
			$form->saveOnClick($addLogoButton);
			$modal = new \PHPFUI\Reveal($this->page, $addLogoButton);
			$submitPhoto = new \PHPFUI\Submit($this->buttonText);
			$uploadForm = new \PHPFUI\Form($this->page);
			$uploadForm->setAreYouSure(false);
			$file = new \PHPFUI\Input\File($this->page, 'logo', 'Select Logo');
			$file->setAllowedExtensions(['png', 'jpg', 'jpeg']);
			$file->setToolTip('Logo should be clear and high quality.  It will not be resized, so make sure it meets PayPal requirements.');
			$uploadForm->add($file);
			$uploadForm->add($modal->getButtonAndCancel($submitPhoto));
			$modal->add($uploadForm);
			$photoSet = new \PHPFUI\FieldSet('Club Logo');
			$row = new \PHPFUI\GridX();
			$row->add("<img alt='Club Logo' src='{$this->logo}'>");
			$photoSet->add($row);
			$row = new \PHPFUI\GridX();
			$row->add('&nbsp;');
			$photoSet->add($row);
			$photoSet->add($addLogoButton);
			$form->add($photoSet);
			$fieldSet = new \PHPFUI\FieldSet('PayPal Instructions');
			$editor = new \PHPFUI\Input\TextArea('PayPal_instructions', '', $this->paypalModel->getInstructions());
			$editor->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			$fieldSet->add($editor);
			$form->add($fieldSet);
			$fieldSet = new \PHPFUI\FieldSet('PayPal Section Settings');
			$fieldSet->add($this->areaSetting('Membership'));
			$fieldSet->add($this->areaSetting('Store'));
			$fieldSet->add($this->areaSetting('Events'));
			$fieldSet->add($this->areaSetting('General Admission'));
			$fieldSet->add($this->areaSetting('Subscription'));
			$fieldSet->add($this->areaSetting('Refunds'));
			$form->add($fieldSet);
			$form->add($submit);
			}

		return $form;
		}

	private function areaSetting(string $type) : \PHPFUI\GridX
		{
		$row = new \PHPFUI\GridX();
		$field = \str_replace(' ', '_', $type);
		$radio = new \PHPFUI\Input\RadioGroup('PayPal_' . $field, '', $this->settingTable->value('PayPal_' . $field));
		$radio->addButton('Live', '');
		$radio->addButton('Sandbox', 'sandbox.');
		$columnA = new \PHPFUI\Cell(6, 4, 3);
		$columnA->add("<strong>{$type}:</strong>");
		$row->add($columnA);
		$columnB = new \PHPFUI\Cell(6, 8, 9);
		$columnB->add($radio);
		$row->add($columnB);

		return $row;
		}
	}
