<?php

$debugBar = null;

try
	{
	$url = $_SERVER['REQUEST_URI'];

	// these file extensions come up from DebugBar, so ignore them
	if (\str_contains($url, '.map') || \str_contains($url, '.woff') || \str_contains($url, '.ttf'))
		{
		exit;
		}

	include '../common.php';

	if (! $dbSettings || $dbSettings->Setup || \str_contains($url, '/Config/') || \str_ends_with($url, '/Config'))
		{
		$controller = new \App\Model\SetupController();
		echo $controller->run();

		exit;
		}

	$settingTable = new \App\Table\Setting();
	$maintenanceMode = (bool)$settingTable->value('maintenanceMode');

	if (\App\Model\Session::getDebugging(\App\Model\Session::DEBUG_BAR))
		{
		$debugBar = new \DebugBar\StandardDebugBar();
		$debugBar->addCollector(new \DebugBar\DataCollector\PDO\PDOCollector(\PHPFUI\ORM::pdo()));
		}

	$permissions = new \App\Model\Permission();

	$controller = new \App\Model\Controller($permissions, $debugBar);

	if (! $permissions->isSuperUser() && ! isset($_GET['signin']) && $maintenanceMode)
		{
		echo new \App\View\Maintenance($controller);
		}
	else
		{
		echo $controller->run();
		}
	}
catch (Throwable $e)
	{
	if ($debugBar)
		{
		$debugBar['exceptions']->addThrowable($e);
		}
	$email = 'webmaster@' . $_SERVER['SERVER_NAME'] ?? 'localhost';
	$logger = \App\Tools\Logger::get();
	$logger->debug($e->getMessage());
	$logger->debug($e->getFile());
	$logger->debug($e->getLine());

	foreach ($e->getTrace() as $row)
		{
		foreach ($row as $key => $value)
			{
			if (! \in_array($key, ['line', 'file', 'function']))
				{
				unset($row[$key]);

				continue;
				}
			$row[$key] = \str_replace(PROJECT_ROOT, '', $value);
			}

		if ($row)
			{
			$logger->debug($row);
			}
		}
	$duck = new \App\View\ClusterDuck('This is really messed up! Email the <a href="mailto:' . $email . '?title=Cluster%20Duck">webmaster</a>.');
	$duck->addMessage($e->getMessage());
	$duck->addMessage($e->getFile());
	$duck->addMessage($e->getLine());
	$table = new \PHPFUI\Table();
	$table->setHeaders(['class', 'function', 'file', 'line']);

	foreach ($e->getTrace() as $row)
		{
		$table->addRow($row);
		}
	$duck->addMessage($table);
	echo $duck;
	}
session_write_close();
