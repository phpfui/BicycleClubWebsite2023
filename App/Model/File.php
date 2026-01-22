<?php

namespace App\Model;

abstract class File
	{
	protected string $destinationName = '';

	protected string $directory;

	protected string $error = '';

	protected string $extension = '';

	/**
	 * @var array<string,string>
	 */
	protected array $mimeTypes = [
		'.doc' => 'application/msword',
		'.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'.eps' => 'application/eps',
		'.gpx' => 'text/xml',
		'.htm' => 'text/html',
		'.html' => 'text/html',
		'.jpg' => 'image/jpeg',
		'.jpeg' => 'image/jpeg',
		'.gif' => 'image/gif',
		'.png' => 'image/png',
		'.ppt' => 'application/vnd.ms-powerpoint',
		'.webp' => 'image/webp',
		'.ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		'.odt' => 'application/vnd.oasis.opendocument.text',
		'.pdf' => 'application/pdf',
		'.xls' => 'application/vnd.ms-excel',
		'.xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	];

	protected string $uploadName = '';

	protected int $uploadSize = 0;

	protected function __construct(protected string $type)
		{
		$this->directory = PUBLIC_ROOT . "{$type}/";
		}

	public function delete(string | int $id) : void
		{
		foreach (\glob($this->directory . "{$id}.*") as $filename)
			{
			\App\Tools\File::unlink($filename);
			}
		}

	/**
	 * Returns empty string on success, or missing file name on error
	 */
	public function download(string | int $name, string $extension, string $downloadName = '') : string
		{
		if (empty($downloadName))
			{
			$downloadName = (string)$name . $extension;
			}
		$downloadName = $this->prettify($downloadName);
		$filename = $this->directory . $name . $extension;

		if (! \file_exists($filename))
			{
			return $name . $extension;
			}

		while (\ob_get_level() > 0)
			{
			\ob_end_flush();
			}
		$stats = \stat($filename);
		\http_response_code(200);
		\header('Pragma: public');
		\header('Last-Modified: ' . \date('D, d M Y H:i:s') . ' GMT');
		\header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
		\header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
		\header('Content-Transfer-Encoding: none');
		\header("Content-length: {$stats['size']}");

		if (empty($this->mimeTypes[$extension]) || 'binary' == $this->mimeTypes[$extension])
			{
			\header('Content-Type: application/octetstream; name="' . $downloadName . '"'); //This should work for IE & Opera
			\header('Content-Type: application/octet-stream; name="' . $downloadName . '"'); //This should work for the rest
			}
		else
			{
			\header('Content-Type: {$this->mimeType[$extension]}');
			}
		\header('Content-Disposition: attachment; filename="' . $downloadName . '"');
		\readfile($filename);

		return '';
		}

	public function get(string | int $filename) : string
		{
		return $this->directory . (string)$filename;
		}

	/**
	 * @return array<string>
	 */
	public function getAll(string $match = '*') : array
		{
		$files = [];

		foreach (\glob($this->getPath() . $match) as $file)
			{
			$files[] = \substr((string)$file, \strrpos((string)$file, '/') + 1);
			}

		return $files;
		}

	public function getDestinationName() : string
		{
		return $this->destinationName;
		}

	public function getExtension() : string
		{
		return $this->extension;
		}

	public function getFileType() : string
		{
		return $this->type;
		}

	public function getLastError() : string
		{
		return $this->error;
		}

	/**
	 * @return array<string,string>
	 */
	public function getMimeTypes() : array
		{
		return $this->mimeTypes;
		}

	public function getPath() : string
		{
		return $this->directory;
		}

	public function getUploadName() : string
		{
		return $this->uploadName;
		}

	public function getUploadSize() : int
		{
		return $this->uploadSize;
		}

	/**
	 * @param array<string> $files
	 *
	 * @return array<string>
	 */
	public function paginateFiles(array $files, int $page, int $limit = 25) : array
		{
		return \array_slice($files, $page * $limit, $limit);
		}

	public function prettify(string $fileName) : string
		{
		return \preg_replace('/[^a-zA-Z0-9\.\-\_()]/', '', \str_replace(' ', '_', $fileName));
		}

	public function processFile(string | int $path) : string
		{
		return '';
		}

	public function rename(string $fileName, string $newName) : void
		{
		\rename($this->directory . $fileName, $this->directory . $newName);
		}

	/**
	 * @param array<string> $files
	 *
	 * @return array<string>
	 */
	public function sortFiles(array $files, string $sort = 'name', string $dir = 'a') : array
		{
		\usort(
			$files,
			function(string $lhs, string $rhs) use ($sort, $dir) : int
			{
			if ('time' == $sort)
				{
				$lhs = \filemtime($this->get($lhs));
				$rhs = \filemtime($this->get($rhs));
				}

			return ('a' == $dir) ? $lhs <=> $rhs : $rhs <=> $lhs;
			}
		);

		return $files;
		}

	/**
	 * Save an uploaded a file
	 *
	 * @param string|int|null $id base name of file to be upload, if null, use uploaded file name
	 * @param string $name index into $files array of file to process
	 * @param array<string,mixed> $files generally $_FILES
	 * @param ?array<string,string> $filetypes valid extentions to accept
	 */
	public function upload(string | int | null $id, string $name, array $files, ?array $filetypes = []) : bool
		{
		$returnValue = false;
		$this->error = '';
		$this->extension = '';

		if (null === $filetypes)
			{
			$filetypes = [];
			}
		elseif (! $filetypes)
			{
			$filetypes = $this->mimeTypes;
			}

		if (isset($files[$name]))
			{
			$error = $files[$name]['error'];

			if ($error)
				{
				switch ($error)
					{
					case UPLOAD_ERR_INI_SIZE:
						$this->error = 'File size is greater than ' . \ini_get('upload_max_filesize') . ' btyes allowed by the server.';

						break;

					case UPLOAD_ERR_FORM_SIZE:
						$this->error = "File uploaded is too large.  Limit is {$_POST['MAX_FILE_SIZE']} bytes.";

						break;

					case UPLOAD_ERR_PARTIAL:
						$this->error = 'File was not completely uploaded.';

						break;

					case UPLOAD_ERR_NO_FILE:
						$this->error = 'No file was specified.';

						break;

					case UPLOAD_ERR_NO_TMP_DIR:
						$this->error = 'Server error: missing temp directory.';

						break;

					case UPLOAD_ERR_CANT_WRITE:
						$this->error = 'Server error: write failed.';

						break;

					case UPLOAD_ERR_EXTENSION:
						$this->error = 'Server error: upload denied by PHP.';

						break;
					}
				}
			else
				{
				if (\is_uploaded_file($files[$name]['tmp_name']))
					{
					$this->uploadName = $files[$name]['name'];
					$this->extension = \strtolower(\strrchr((string)$this->uploadName, '.'));

					if (! \count($filetypes) || isset($filetypes[$this->extension]))
						{
						if (null === $id)
							{
							$destination = $this->directory . $files[$name]['name'];
							}
						else
							{
							$destination = $this->directory . $this->getBaseName($id) . $this->extension;
							}

						if (! \move_uploaded_file($files[$name]['tmp_name'], $destination))
							{
							$this->error = 'Could not move file to ' . $destination;
							}
						else
							{
							$error = $this->processFile($destination);

							if (! $error)
								{
								$this->destinationName = $destination;
								$this->uploadSize = $files[$name]['size'];
								$returnValue = true;
								}
							else
								{
								\App\Tools\File::unlink($destination);
								$this->error = $error;
								}
							}
						}
					else
						{
						$this->error = $this->extension . ' is not a valid extension. Valid types are ' . \implode(',', \array_keys($filetypes));
						}
					}
				else
					{
					$this->error = 'File ' . $files[$name]['name'] . ' was not uploaded correctly';
					}
				}
			}

		return $returnValue;
		}

	public function url(string $filename) : string
		{
		return "/{$this->type}/{$filename}";
		}

	protected function getBaseName(string | int $id) : string
		{
		return (string)$id;
		}
	}
