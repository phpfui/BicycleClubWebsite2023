<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Reservation> $ReservationChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Payment> $PaymentChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\InvoiceItem> $InvoiceItemChildren
 * @property bool $showMenus
 */
class Invoice extends \App\Record\Definition\Invoice
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'InvoiceItemChildren' => [\PHPFUI\ORM\Children::class, \App\Table\InvoiceItem::class],
		'PaymentChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Payment::class],
		'ReservationChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Reservation::class],
		'showMenus' => [\App\DB\InvoiceMenu::class],
	];

	public function total() : float
		{
		return \round($this->totalPrice + $this->totalTax + $this->totalShipping, 2);
		}

	public function unpaidBalance() : float
		{
		return \round(($this->totalPrice + $this->totalTax + $this->totalShipping) - $this->paypalPaid - $this->pointsUsed - $this->paidByCheck - $this->discount, 2);
		}

	public function update() : bool
		{
		// if paid by volunteer points, no tax is due
		if ($this->totalPrice <= $this->pointsUsed)
			{
			$this->totalTax = 0.0;
			}

		return parent::update();
		}
	}
