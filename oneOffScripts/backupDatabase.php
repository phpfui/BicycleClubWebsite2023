<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Backing up file {$dbSettings->getLoadedFileName()}\n";

$backup = new \App\Model\Backup();
$backup->run();
