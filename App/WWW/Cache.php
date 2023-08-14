<?php

namespace App\WWW;

class Cache extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function buster(array $parts = []) : void
		{
		$type = 'text';
		$url = \str_replace('/Cache/buster', '', $this->page->getBaseURL());
		$index = \strpos($url, '.cbv_');

		if ($index)
			{
			$last = \strrpos($url, '.');
			$url = \substr($url, 0, $index) . \substr($url, $last);
			$filePath = \PUBLIC_ROOT . $url;
			$contents = \file_get_contents($filePath);

			if (empty($contents))
				{
				\http_response_code(404);

				exit;
				}
			\header('Cache-Control: immutable');
			\header('Content-Length: ' . \strlen($contents));
			\header('Expires: ' . \date('r', \time() + 99_999_999));
			\header('Last-Modified: ' . \date('r', \filemtime($filePath)));

			if (\str_ends_with($url, '.css'))
				{
				$type = 'css';
				}
			elseif (\str_ends_with($url, '.js'))
				{
				$type = 'javascript';
				}
			\header("Content-Type: text/{$type};charset=UTF-8");
			echo $contents;

			exit;
			}

		}
	}
