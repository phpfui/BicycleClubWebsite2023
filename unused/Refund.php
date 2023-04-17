<?php

namespace App\View;

class Refund
	{
	public function __construct(private \App\View\Page $page)
		{
		}

	public function Edit(\App\Record\PayPalRefund $paypalRefund) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if ($paypalRefund->empty())
			{
			$container->add(new \App\View\PHPFUI\SubHeader('Refund not found'));

			return $container;
			}

		$submit = new \PHPFUI\Submit('Save');
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			unset($_POST['paypalRefundId']);
			$paypalRefund->setFrom($_POST);
			$paypalRefund->update();
			$this->page->setResponse('Saved');

			return $container;
			}

//		'amount' => 0.0,
//	'invoiceId' => 0,
//	'paypaltx' => '',
//	'createdDate' => 0,
//	'refundedDate' => 0,
//	'note_to_payer' => '',
//	'memberIdAprovedBy' => 0,
//	'response' => '',
//	'createdMemberNumber' => 0,

		$form->add($submit);
		$container->add($form);

		return $container;
		}

	public function list(\App\Table\PaypalRefund $paypalRefundTable) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$view = new \App\View\PaginatedTable($this->page, $paypalRefundTable);
		$deleter = new \App\Model\DeleteRecord($this->page, $view, $paypalRefundTable, 'Permanently delete this refund?');
		$view->addCustomColumn('del', $deleter->columnCallback(...));
		$view->addCustomColumn('invoiceId', static function(array $invoice) { return new \PHPFUI\FAIcon('fas', 'file-download', '/Store/invoice/' . $invoice['invoiceId']);});
		$view->addCustomColumn('createdMemberNumber', static function(array $row)
			{
			$member = new \App\Record\Member($row['createdMemberNumber']);

			if ($member->empty())
				{
				return 'Unknown';
				}

			return $member->fullName();
			});

		$sortableHeaders = ['amount' => 'Amount', 'invoiceId' => 'Invoice', 'createdMemberNumber' => 'Requested By', 'createdDate' => 'Created', 'refundedDate' => 'Refunded'];
		$normalHeaders = ['Edit', 'Approve', 'del' => 'Del'];
		$view->setHeaders($sortableHeaders + $normalHeaders);
		$view->setSortableColumns(\array_keys($sortableHeaders))->setSortedColumnOrder($column, $sort);
		$container->add($view);

		return $container;
		}
	}
