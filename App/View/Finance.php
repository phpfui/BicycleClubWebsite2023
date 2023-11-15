<?php

namespace App\View;

class Finance
	{
	private readonly \App\Model\Finance $model;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->model = new \App\Model\Finance();
		}

	public function getChecksRequest() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->addAttribute('target', '_blank');
		$form->add($this->getDateRangeFieldSet());
		$form->add($this->getCheckBoxes());
		$form->add(new \PHPFUI\Submit('Print Checks Received'));

		return $form;
		}

	/**
	 * @param array<string,string> $request
	 *
	 * @psalm-return list<mixed>
	 */
	public static function getPaymentRequest(array $request) : array
		{
		$paymentTypes = \App\Table\Payment::getPaymentTypes();
		$types = [];

		foreach ($paymentTypes as $key => $type)
			{
			$field = \strtolower(\str_replace(' ', '', (string)$type));

			if (! empty($request[$field]))
				{
				$types[] = $key;
				}
			}

		return $types;
		}

	public function getStoreRequest(bool $showDetails = true) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->addAttribute('target', '_blank');
		$form->add($this->getDateRangeFieldSet());

		if ($showDetails)
			{
			$fieldSet = new \PHPFUI\FieldSet('Report Details');
			$fieldSet->add($this->makeCB('fullDetails', 'Show each cart line item including PayPal Transaction Id'));
			$form->add($fieldSet);
			}
		$fieldSet = new \PHPFUI\FieldSet('Report By Type');
		$fieldSet->add($this->makeCB('store', 'Store'));
		$fieldSet->add($this->makeCB('PE', 'Public Events'));
		$fieldSet->add($this->makeCB('membership', 'Membership Dues'));
		$fieldSet->add($this->makeCB('club', 'Club Events'));
		$fieldSet->add($this->makeCB('discount', 'Discount Code'));
		$form->add($fieldSet);
		$download = new \PHPFUI\Submit('Download CSV', 'csv');
		$download->addClass('info');
		$form->add(new \App\UI\CancelButtonGroup($download));

		return $form;
		}

	public function getTaxRequest() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->addAttribute('target', '_blank');
		$form->add($this->getDateRangeFieldSet());
		$download = new \PHPFUI\Submit('Download CSV', 'csv');
		$download->addClass('info');
		$form->add(new \App\UI\CancelButtonGroup($download));

		return $form;
		}

	public function ImportTaxTable() : \PHPFUI\Form
		{
		$fileUploadName = 'file';
		$form = new \PHPFUI\Form($this->page);

		if ($this->model->upload(\date('Y-m-d'), $fileUploadName, $_FILES, ['.csv' => 'text/csv']))
			{
			$form->add(new \App\UI\Alert('File was imported correctly'));
			$this->page->redirect('/Finance', '', 3);

			return $form;
			}

		if ($errors = $this->model->getErrors())
			{
			$container = new \PHPFUI\Container();
			$container->add(new \PHPFUI\Header('File contained the following errors:', 4));
			$ul = new \PHPFUI\UnorderedList();

			foreach ($errors as $field)
				{
				$ul->addItem(new \PHPFUI\ListItem("<b>{$field}</b>"));
				}
			$container->add($ul);
			$alert = new \App\UI\Alert($container);
			$alert->addClass('alert');
			$form->add($alert);
			}
		$fieldSet = new \PHPFUI\FieldSet('Import New Tax Table');
		$fieldSet->add('You can import a tax table downloaded from <a href="https://www.taxrates.com/state-rates/">taxrates.com</a>. ');
		$fieldSet->add('Previously uploaded tax tables will be deleted for the first state in the first record of the imported file.');
		$fieldSet->add(new \PHPFUI\Header('Required Fields', 4));
		$ul = new \PHPFUI\UnorderedList();

		foreach ($this->model->getTaxImportFields() as $field)
			{
			$ul->addItem(new \PHPFUI\ListItem("<b>{$field}</b>"));
			}
		$fieldSet->add($ul);
		$filesize = new \PHPFUI\Input\Hidden('MAX_FILE_SIZE', (string)4_000_000);
		$fieldSet->add($filesize);
		$file = new \PHPFUI\Input\File($this->page, $fileUploadName, 'Select Tax Table to Upload');
		$fieldSet->add($file);
		$form->add($fieldSet);
		$form->add(new \PHPFUI\Submit('Upload Tax File'));

		return $form;
		}

	public function Maintenance() : string
		{
		$button = new \PHPFUI\Button('Find Payments');
		$modal = $this->getSearchModal($button);
		$output = '';
		$row = new \PHPFUI\GridX();

		if (! empty($_GET['start']) && ! empty($_GET['end']))
			{
			$start = $_GET['start'];
			$end = $_GET['end'];
			$format = 'l, F j, Y';
			$row->add($start . ' - ' . $end);
			$view = new \App\View\Payments($this->page);
			$payments = \App\Table\Payment::getByDate($start, $end, self::getPaymentRequest($_GET), $_GET['myChecks']);
			$output = $view->show($payments);

			if (\count($payments))
				{
				$output .= $row . $button;
				}
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $row . $button . $output;
		}

	public function PayPalSettings() : \PHPFUI\Tabs
		{
		$paypalModel = new \App\Model\PayPal();
		$tabs = new \PHPFUI\Tabs();
		$paypalView = new \App\View\PayPal($this->page, $paypalModel);
		$tabs->addTab('Settings', $paypalView->getSettings(), true);
		$tabs->addTab('Live', $paypalView->getEditor());
		$tabs->addTab('Sandbox', $paypalView->getEditor('Sandbox'));

		return $tabs;
		}

	public function RequestMissingInvoices() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Find Missing Invoices');
		$fieldSet->add('Download a Activity Report from PayPal, convert it to CSV format, and upload it below. It should have a column labeled "<b>Item ID</b>".');
		$file = new \PHPFUI\Input\File($this->page, 'file');
		$file->setAllowedExtensions(['csv']);
		$fieldSet->add($file);
		$submit = new \PHPFUI\Submit('Upload PayPal Activity Report');
		$fieldSet->add(new \App\UI\CancelButtonGroup($submit));
		$form->add($fieldSet);

		return $form;
		}

	/**
	 * @param array<array<string>> $missing
	 */
	public function showMissingInvoices(array $missing) : \PHPFUI\Table | \PHPFUI\SubHeader
		{
		if (! \count($missing))
			{
			return new \PHPFUI\SubHeader('All Invoices Found');
			}
		$table = new \PHPFUI\Table();
		$headers = false;

		foreach ($missing as $row)
			{
			if (! $headers)
				{
				$table->setHeaders(\array_keys($row));
				$headers = true;
				}
			$table->addRow($row);
			}

		return $table;
		}

	protected function getSearchModal(\PHPFUI\HTML5Element $modalLink) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'get');
		$fieldSet = new \PHPFUI\FieldSet('Date Range');
		$end = \App\Tools\Date::today();
		$start = $end - 30;

		if (! empty($_GET['start']))
			{
			$start = \App\Tools\Date::fromString($_GET['start']);
			}

		if (! empty($_GET['end']))
			{
			$end = \App\Tools\Date::fromString($_GET['end']);
			}
		$fieldSet->add(new \PHPFUI\Input\Date($this->page, 'start', 'Start Date', \App\Tools\Date::toString($start)));
		$fieldSet->add(new \PHPFUI\Input\Date($this->page, 'end', 'End Date', \App\Tools\Date::toString($end)));
		$form->add($fieldSet);
		$form->add($this->getCheckBoxes('Payment Types'));
		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	private function getCheckBoxes(string $legend = 'Report Details') : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet($legend);
		$fieldSet->add($this->makeCB('myChecks', 'My Transactions Only'));
		$paymentTypes = \App\Table\Payment::getPaymentTypes();

		foreach ($paymentTypes as $type)
			{
			$fieldSet->add($this->makeCB(\strtolower(\str_replace(' ', '', (string)$type)), "Include {$type} Transations"));
			}

		return $fieldSet;
		}

	private function getDateRangeFieldSet() : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Report Date Range');
		$start = new \PHPFUI\Input\Date($this->page, 'start', 'Start Date', \App\Tools\Date::todayString(-31));
		$start->setRequired();
		$fieldSet->add($start);
		$end = new \PHPFUI\Input\Date($this->page, 'end', 'End Date', \App\Tools\Date::todayString());
		$end->setRequired();
		$fieldSet->add($end);

		return $fieldSet;
		}

	private function makeCB(string $name, string $label) : \PHPFUI\GridX
		{
		$row = new \PHPFUI\GridX();
		$row->add(new \PHPFUI\Input\CheckBoxBoolean($name, $label, ! empty($_POST[$name])));

		return $row;
		}
	}
