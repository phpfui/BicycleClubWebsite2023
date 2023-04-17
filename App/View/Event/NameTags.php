<?php

namespace App\View\Event;

class NameTags implements \Stringable
	{
	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Event $event)
		{
		$this->settingTable = new \App\Table\Setting();

		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
			{
			$this->printLabels($_POST['stock'], $_POST['firstLast']);
			}
		}

	public function __toString() : string
		{
		$form = new \PHPFUI\Form($this->page);
		$stockFieldSet = new \PHPFUI\FieldSet('Select Label Stock Number');
		$pdfLabels = new \PDF_Label(5384);
		$stock = new \PHPFUI\Input\RadioGroup('stock', '', (string)5384);
		$stock->setSeparateRows();
		$labels = $pdfLabels->getLabelStock();

		foreach ($labels as $stockNumber => $label)
			{
			$count = $label['NY'] * $label['NX'];
			$height = $label['height'];
			$width = $label['width'];

			if ('mm' == $label['metric'])
				{
				$height /= 25.4;
				$width /= 25.4;
				}
			$stock->addButton("{$stockNumber} ({$count} per sheet, {$height}\" x {$width}\")", $stockNumber);
			}
		$stockFieldSet->add($stock);
		$form->add($stockFieldSet);
		$optionsFieldSet = new \PHPFUI\FieldSet('Name Options');
		$firstLast = new \PHPFUI\Input\RadioGroup('firstLast', '', (string)2);
		$firstLast->setSeparateRows();
		$firstLast->addButton('First Name Only', (string)0);
		$firstLast->addButton('First / Last (1 line)', (string)1);
		$firstLast->addButton('First / Last (2 lines)', (string)2);
		$optionsFieldSet->add($firstLast);
		$form->add($optionsFieldSet);
		$optionsFieldSet = new \PHPFUI\FieldSet('Logo Options');
		$logo = new \PHPFUI\Input\CheckBoxBoolean('logo', 'Print with club logo', false);
		$logo->setToolTip('Check to print the club logo on each name tag.  Requires a color printer for best results.');
		$logoSize = new \PHPFUI\Input\Number('logoSize', 'Logo Width %', 50);
		$logoSize->setToolTip('Set the width of the logo for the label. Use a smaller number for smaller labels.');
		$optionsFieldSet->add(new \PHPFUI\MultiColumn($logo, $logoSize));
		$form->add($optionsFieldSet);
		$form->add(new \PHPFUI\Submit('Print'));

		return (string)"{$form}";
		}

	private function printLabels(string $stockNumber, int $firstLast) : void
		{
		$pdf = new \PDF_Label($stockNumber);

		if ($_POST['logo'])
			{
			$file = new \App\Model\ImageFiles();
			$pdf->Set_Background_Image($file->get($this->settingTable->value('nameTagLogo')), $_POST['logoSize']);
			}
		$pdf->Set_Alignment('C');
		$maxFont = 999;
		$members = \App\Table\ReservationPerson::getNamesAlpha($this->event);

		foreach ($members as $member)
			{
			$first = \App\Tools\TextHelper::unhtmlentities($member['firstName']);
			$last = \App\Tools\TextHelper::unhtmlentities($member['lastName']);

			if ($firstLast)
				{
				if (2 == $firstLast)
					{
					$maxFont = \min($pdf->Set_Max_Font_Size($first, $maxFont), $pdf->Set_Max_Font_Size($last, $maxFont));
					}
				else
					{
					$maxFont = \min($maxFont, $pdf->Set_Max_Font_Size($first . ' ' . $last, $maxFont));
					}
				}
			else
				{
				$maxFont = \min($maxFont, $pdf->Set_Max_Font_Size($first, $maxFont));
				}
			}
		$pdf->Set_Font_Size($maxFont);

		foreach ($members as $member)
			{
			$first = \App\Tools\TextHelper::unhtmlentities($member['firstName']);
			$last = \App\Tools\TextHelper::unhtmlentities($member['lastName']);

			if ($firstLast)
				{
				if (2 == $firstLast)
					{
					$label = $first . "\n" . $last;
					}
				else
					{
					$label = "{$first} {$last}";
					}
				}
			else
				{
				$label = $first;
				}
			$pdf->Add_PDF_Label($label);
			}
		$pdf->Output("NameTags-{$this->event->eventId}.pdf", 'I');
		}
	}
