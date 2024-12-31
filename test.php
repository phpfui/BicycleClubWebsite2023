<?php

$url = $argv[1] ?? 'https://google.com';

include 'commonbase.php';

$qrCode = new \Endroid\QrCode\QrCode(
	data: $url,
	encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
	errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::High,
	size: 1200,
	margin: 10,
	roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin,
	foregroundColor: new \Endroid\QrCode\Color\Color(0, 0, 0),
	backgroundColor: new \Endroid\QrCode\Color\Color(255, 255, 255),
	);

$writer = new \Endroid\QrCode\Writer\PngWriter();
$result = $writer->write($qrCode);
file_put_contents('QRCode.png', $result->getString());

echo "QRCode.png created for $url\n";
