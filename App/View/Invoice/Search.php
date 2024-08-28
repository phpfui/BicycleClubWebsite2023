<?php

namespace App\View\Invoice;

class Search implements \Stringable
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function __toString() : string
		{
		$button = new \PHPFUI\Button('Search Invoices');
		$modal = $this->getSearchModal($button, $_GET);
		$output = '';
		$row = new \PHPFUI\GridX();
		$row->add('<br>');

		if (isset($_GET['shipped']))
			{
			$model = new \App\Model\Invoice();
			$invoiceTable = $model->getInvoiceTable($_GET);
			$view = new \App\View\Invoice($this->page);
			$output = $view->show($invoiceTable);

			if (\count($invoiceTable))
				{
				$output .= $row . $button;
				}
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $button . $output;
		}

	/**
	 * @param array<string,string> $parameters
	 */
	protected function getSearchModal(\PHPFUI\HTML5Element $modalLink, array $parameters) : \PHPFUI\Reveal
		{
		$this->setDefaults($parameters);
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'get');
		$fieldSet = new \PHPFUI\FieldSet('Find an Invoice');
		$invoiceId = new \PHPFUI\Input\Number('invoiceId', 'Invoice Id', $parameters['invoiceId']);
		$invoiceId->addAttribute('max', (string)9_999_999)->addAttribute('min', (string)0);
		$name = new \PHPFUI\Input\Text('name', 'Member Name Includes', $parameters['name']);
		$text = new \PHPFUI\Input\Text('text', 'Invoice Contains Phrase', $parameters['text']);
		$startDate = new \PHPFUI\Input\Date($this->page, 'startDate', 'Order Date From', $parameters['startDate']);
		$endDate = new \PHPFUI\Input\Date($this->page, 'endDate', 'Order Date Through', $parameters['endDate']);
		$transactionId = new \PHPFUI\Input\Text('paypaltx', 'PayPal Transaction Id', $parameters['paypaltx']);
		$fieldSet->add(new \PHPFUI\MultiColumn($invoiceId, $transactionId));
		$fieldSet->add(new \PHPFUI\MultiColumn($text, $name));
		$fieldSet->add(new \PHPFUI\MultiColumn($startDate, $endDate));
		$radio = new \PHPFUI\Input\RadioGroup('shipped', '', $parameters['shipped']);
		$radio->addButton('Shipped', '1');
		$radio->addButton('Not Shipped', '2');
		$radio->addButton('Unpaid', '3');
		$radio->addButton('All', '0');
		$fieldSet->add($radio);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	/**
	 * @param array<string,string> $parameters
	 */
	protected function setDefaults(array &$parameters) : void
		{
		$defaults = [];
		$defaults['invoiceId'] = '';
		$defaults['name'] = '';
		$defaults['shipped'] = '0';
		$defaults['paypaltx'] = '';
		$defaults['text'] = '';
		$defaults['startDate'] = '';
		$defaults['endDate'] = '';

		foreach ($defaults as $key => $value)
			{
			if ((bool)empty($parameters[$key]))
				{
				$parameters[$key] = $value;
				}
			}
		}
	}
