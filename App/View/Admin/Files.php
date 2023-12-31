<?php

namespace App\View\Admin;

class Files
	{
	private bool $uploadable = true;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Model\File $fileModel)
		{
		$this->processRequest();
		}

	public function disableUpload(bool $disable = true) : static
		{
		$this->uploadable = ! $disable;

		return $this;
		}

	public function list() : \PHPFUI\Container
		{
		$table = new \PHPFUI\SortableTable();

		// get the parameter we know we are interested in
		$parameters = $table->getParsedParameters();
		$page = (int)($parameters['p'] ?? 0);
		$limit = (int)($parameters['l'] ?? 25);
		$column = $parameters['c'] ?? 'name';
		$sort = $parameters['s'] ?? 'a';

		$sortableHeaders = ['name' => 'File', 'time' => 'Date/Time'];
//		$normalHeaders = ['Download', 'Delete'];
		$normalHeaders = ['Delete'];
		$table->setHeaders($sortableHeaders + $normalHeaders);
		$table->setSortableColumns(\array_keys($sortableHeaders))->setSortedColumnOrder($column, $sort);

		$recordIndex = 'base';
		$table->setRecordId($recordIndex);
		$delete = new \PHPFUI\AJAX('deleteFile', 'Permanently delete this File?');
		$delete->addFunction('success', "$('#{$recordIndex}-'+data.response).css('background-color','red').hide('fast').remove()");
		$this->page->addJavaScript($delete->getPageJS());

		$files = $this->fileModel->getAll();
		$lastPage = (int)((\count($files) - 1) / $limit) + 1;

		$files = $this->fileModel->sortFiles($files, $column, $sort);
		$files = $this->fileModel->paginateFiles($files, $page, $limit);

		$row = null;

		foreach ($files as $file)
			{
			$base = \basename((string)$file);
			$localPath = $this->fileModel->get($base);
			$url = $this->fileModel->url($base);

			if ($url)
				{
				$row['name'] = "<a href='{$url}' target='_blank'>{$base}</a>";
				}
			else
				{
				$row['name'] = $base;
				}
			$crc = \crc32($base);
			$row['base'] = $crc;
			$row['time'] = \gmdate('Y-m-d, g:i a', \filemtime($localPath));
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $delete->execute([$recordIndex => '"' . $crc . '"']));
			$row['Delete'] = $icon;
			$icon = new \PHPFUI\FAIcon('fas', 'file-arrow-down', '#');
			$icon->addAttribute('onclick', $delete->execute([$recordIndex => '"' . $crc . '"']));
			$row['Download'] = $icon;
			$table->addRow($row);
			}

		$container = new \PHPFUI\Container();

		if (! \count($files))
			{
			$container->add(new \PHPFUI\SubHeader('No waivers found'));
			}
		$container->add($table);

		$parameters['p'] = 'PAGE';
		$url = $table->getBaseUrl() . '?' . \http_build_query($parameters);
		$paginator = new \PHPFUI\Pagination($page, $lastPage, $url);
		$paginator->center();
		$container->add($paginator);

		if ($this->uploadable)
			{
			$button = new \PHPFUI\Button('Upload File');
			$this->getFileUploadModal($button);
			$container->add(new \App\UI\CancelButtonGroup($button));
			}

		return $container;
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'Add':

						if ($this->fileModel->upload(null, 'userfile', $_FILES))
							{
							\App\Model\Session::setFlash('success', 'File uploaded successfully');
							}
						else
							{
							\App\Model\Session::setFlash('alert', $this->fileModel->getLastError());
							}
						$this->page->redirect();

						break;


					case 'deleteFile':
						$files = $this->fileModel->getAll();

						foreach ($files as $file)
							{
							$base = \crc32(\basename($file));

							if ($_POST['base'] == "{$base}")
								{
								\App\Tools\File::unlink($this->fileModel->get($file));
								$this->page->setResponse($_POST['base']);

								break;
								}
							}
						$this->page->done();

						break;
					}
				}
			}
		}

	private function getFileUploadModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('File To Upload');
		$filesize = new \PHPFUI\Input\Hidden('MAX_FILE_SIZE', (string)4_000_000);
		$fieldSet->add($filesize);
		$file = new \PHPFUI\Input\File($this->page, 'userfile', 'File');
		$extensions = [];

		foreach ($this->fileModel->getMimeTypes() as $ext => $mime)
			{
			$extensions[] = \str_replace('.', '', $ext);
			}
		$file->setAllowedExtensions($extensions);
		$fieldSet->add($file);
		$this->fileModel->getMimeTypes();
		$fieldSet->add(new \App\UI\Display('Allowed Types', \implode(' ', \array_keys($this->fileModel->getMimeTypes()))));
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
