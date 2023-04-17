<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$email = \filter_var($argv[1] ?? '', \FILTER_VALIDATE_EMAIL);

if (! $email)
	{
	echo "You must pass your valid email address\n";

	exit;
	}
$name = 'Your Name';

$mail = new \App\Tools\EMail(true);
$mail->setReplyTo($email, $name);
$mail->addTo($email, $name);
$mail->setFrom($email, $name);
$mail->setHTML(true);
$mail->setSubject('This is a WAMP Server email test');
$mail->setBody('<b>Bold</b> Normal<h3>Headline</h3>');

echo "\n";
echo $mail->Send();
