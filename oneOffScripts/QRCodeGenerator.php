<?php

include '../commonbase.php';

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

$writer = new PngWriter();

// Create QR code
$qrCode = new QrCode(
	data: 'https://www.westchestercycleclub.org/VeloDeFemmes',
	encoding: new Encoding('UTF-8'),
	errorCorrectionLevel: ErrorCorrectionLevel::Low,
	size: 300,
	margin: 10,
	roundBlockSizeMode: RoundBlockSizeMode::Margin,
	foregroundColor: new Color(0, 0, 0),
	backgroundColor: new Color(255, 255, 255)
);


$writer->write($qrCode)->saveToFile(__DIR__ . '/qrcode.png');
