<?php

$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include 'common.php';

$member = new \App\Record\Member(['firstName' => 'Bruce', 'lastName' => 'Wells']);

\uploadPhotos('d:\\photos\\WCC50th\\*.*', 'CCC / WCC 50th Anniversary Bash', $member);
\uploadPhotos('d:\\photos\\GADA2025\\*.*', 'Golden / Dirty Apple', $member);
\uploadPhotos('c:\\download\\50th\\*.*', '50th Photos', $member);

function uploadPhotos(string $path, string $folderName, \App\Record\Member $member) : void
	{
	$folder = new \App\Record\Folder(['name' => $folderName]);

	foreach (\glob($path) as $file)
		{
		$photo = new \App\Record\Photo();
		$photo->folder = $folder;
		$photo->member = $member;
		$photo->description = \substr($file, \strrpos($file, '\\') + 1, \strrpos($file, '.'));
		$photo->extension = \strtolower(\substr($file, \strrpos($file, '.')));
		$photo->taken = \date('Y-m-d H:i:s', \filemtime($file));
		$photo->insert();

//		copy($file, "c:\\download\\upload\\{$photo->photoId}.jpg");
		echo "{$photo->photoId}\n";
		}
	}
