<?php

namespace App\View;

class QRCodes
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function membership() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->addAttribute('target', '_blank');
		$form->add('This will generate a QR Code for a free membership till Jan 31 of the next year.');
		$fieldSet = new \PHPFUI\FieldSet('Required Parameters');
		$shop = new \PHPFUI\Input\Text('shop', 'Bike Shop to credit with membership');
		$shop->addAttribute('maxlength', (string)255);
		$shop->setRequired();
		$shop->setToolTip('This will be recorded in the "Affilate" field on the membership for tracking');
		$pixels = new \PHPFUI\Input\Number('pixels', 'Number of Pixels for QR Code', 300);
		$pixels->setRequired();
		$pixels->setToolTip('Higher resolutions may print cleaner. Use smaller resolutions for the web.');
		$fieldSet->add(new \PHPFUI\MultiColumn($shop, $pixels));
		$form->add($fieldSet);
		$form->add(new \App\UI\CancelButtonGroup(new \PHPFUI\Submit('Generate')));

		return $form;
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['submit']) && 'Generate' == $_POST['submit'])
				{
				try
					{
					$settingTable = new \App\Table\Setting();
					$url = $settingTable->value('homePage') . '/Join?a=' . \urlencode((string)$_POST['shop']);

					$qrCode = \Endroid\QrCode\QrCode::create($url)
						->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
						->setErrorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh())
						->setSize((int)($_POST['pixels'] ?? 300))
						->setMargin(10)
						->setRoundBlockSizeMode(new \Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin())
						->setForegroundColor(new \Endroid\QrCode\Color\Color(0, 0, 0))
						->setBackgroundColor(new \Endroid\QrCode\Color\Color(255, 255, 255));

					$writer = new \Endroid\QrCode\Writer\PngWriter();
					$result = $writer->write($qrCode);

					\header('Cache-Control: public');
					\header('Content-Description: File Transfer');
					\header('Content-Disposition: attachment; filename="' . \str_replace(' ', '_', (string)$_POST['shop']) . '_QR_Code.png"');
					\header('Content-Transfer-Encoding: binary');
					\header('Content-Type: ' . $result->getMimeType());
					echo $result->getString();

					exit;
					}
				catch (\Exception $e)
					{
					$this->page->add(new \PHPFUI\SubHeader($e->getMessage()));
					}
				}
			}
		}
	}
