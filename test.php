<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include 'common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$qrCode = \Endroid\QrCode\QrCode::create('https://www.westchestercycleclub.org/velodefemmes')
	->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
	->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::High)
	->setSize(1200)
	->setMargin(10)
	->setRoundBlockSizeMode(\Endroid\QrCode\RoundBlockSizeMode::Margin)
	->setForegroundColor(new \Endroid\QrCode\Color\Color(0, 0, 0))
	->setBackgroundColor(new \Endroid\QrCode\Color\Color(255, 255, 255));

$writer = new \Endroid\QrCode\Writer\PngWriter();
$result = $writer->write($qrCode);
file_put_contents('velodefemmesQR.png', $result->getString());


