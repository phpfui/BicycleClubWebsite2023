<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Reservation> $ReservationChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\PaypalRefund> $PaypalRefundChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Payment> $PaymentChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\InvoiceItem> $InvoiceItemChildren
 */
class Invoice extends \App\Record\Definition\Invoice
	{
	protected static array $virtualFields = [
		'InvoiceItemChildren' => [\PHPFUI\ORM\Children::class, \App\Table\InvoiceItem::class],
		'PaymentChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Payment::class],
		'PaypalRefundChildren' => [\PHPFUI\ORM\Children::class, \App\Table\PaypalRefund::class],
		'ReservationChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Reservation::class],
	];

	public function total() : float
		{
		return $this->totalPrice + $this->totalTax + $this->totalShipping;
		}

	public function unpaidBalance() : float
		{
		return ($this->totalPrice + $this->totalTax + $this->totalShipping) - $this->paypalPaid - $this->pointsUsed;
		}
	}
