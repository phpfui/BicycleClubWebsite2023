<?php

namespace PHPFUI;

/**
 * CacheBuster adds the file timestamp into the file path.  If the file date is different from the previous date, this forces the browser to reload the resource.
 *
 * Since it is file time stamp based, if the file has not changed it's time stamp (meaning it's contents have not changed), then the browser cache is still valid.
 *
 * issues:
 *  - css and js map files
 *  - apache RewriteRule overrides extension
 *  - some file reading issues
 */
class CacheBuster
	{

	/**
	 * @param string $sourceDirectory that contains the files to be busted, generally your public root directory
	 * @param string $baseUrl is prepended to any busted file name for routing purposes.  You need to set up the CacheBuster to return the correct file contents for this route.
	 * @param array<string,string> $mimeTypes
	 */
	public function __construct(protected string $sourceDirectory, protected string $baseUrl, protected array $mimeTypes = [
															'css' => 'text/css',
															'js' => 'application/javascript',
															'woff2' => 'font/woff2',
															'ttf' => 'font/ttf',
															])
		{
		}

	public function addMimeType(string $extension, string $mimeType) : static
		{
		$this->mimeTypes[$extension] = $mimeType;

		return $this;
		}

	/**
	 * Return the time stamp busted path and file name
	 *
	 * @param string $file base file path, example: /css/styles.css
	 *
	 * @return string busted file path, example: /Cache/busted/47693457323/css/styles.css
	 */
	public function fileName(string $file) : string
		{
		if (str_starts_with($file, 'http'))
			{
			return $file;
			}
		$fileName = str_replace('//', '/', $this->sourceDirectory . '/' . $file);
		$time = filemtime($fileName);

		return str_replace('//', '/', $this->baseUrl . '/' . $file) . '.' . $time;
		}

	/**
	 * Outputs the page contents for the busted $filePath
	 *
	 * @param string $filePath the file that needs to be busted as returned by fileName()
	 */
	public function outputBustedPage(string $filePath) : never
		{
		\header_remove(null);
		$url = \str_replace($this->baseUrl, '', $filePath);
		$parts = explode('/', $url);
		while (empty($parts[0]) || ctype_digit($parts[0]))
			{
			array_shift($parts);
			}
		$fullFilePath = str_replace('//', '/', $this->sourceDirectory . '/' . implode('/', $parts));
		$parts = explode('.', $fullFilePath);
		while (ctype_digit($parts[count($parts) - 1]))
			{
			array_pop($parts);
			}
		$fullFilePath = implode('.', $parts);

		if (!file_exists($fullFilePath))
			{
			\header('HTTP/1.1 404 bot Found');
			\http_response_code(404);
			exit;
			}
		$time = filemtime($fullFilePath);
		$contents = \file_get_contents($fullFilePath);

		if (empty($contents))
			{
			\header('HTTP/1.1 204 No Content');
			\http_response_code(204);
			exit;
			}
		$size = \strlen($contents);
		\header('HTTP/1.1 200 OK');
		\header('Content-Length: ' . $size);
		\header('Last-Modified: ' . \date('r', \filemtime($fullFilePath)));

		$index = strrpos($fullFilePath, '.');
		$type = 'text/text';
		if ($index)
			{
			$extension = substr($fullFilePath, $index + 1);
			$type = $this->mimeTypes[$extension] ?? $type;
			}
		\header("Content-Type: {$type}");
		\header('Accept-Ranges: none');
		\header('ETag: "' . dechex($size) . '-' . dechex($time) . '"');
		\http_response_code(200);
		echo $contents;
		exit;
		}
	}
