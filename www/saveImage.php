<?php

include '../common.php';

$logger = \App\Tools\Logger::get();
$logger->debug($_REQUEST);

\header('Content-Type: application/json');
echo '{"url": "/path/to/new/image.png"}';
