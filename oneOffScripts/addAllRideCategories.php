<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$memberTable = new \App\Table\Member();

$categoryTable = new \App\Table\Category();
$categoryCursor = $categoryTable->getAllCategories();


foreach ($memberTable->getRecordCursor() as $member)
	{
	$memberCategoryTable = new \App\Table\MemberCategory();
	$records = [];
	$record = new \App\Record\MemberCategory();
	$record->member = $member;
	foreach ($categoryCursor as $category)
		{
		$record->category = $category;
		$records[] = clone $record;
		}
	$memberCategoryTable->insert($records, 'ignore ');
	}

