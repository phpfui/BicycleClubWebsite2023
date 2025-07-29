<?php

namespace App\UI;

class ContentEditor
	{
	protected \App\Model\TinyMCEInline $tinyMCE;

	private readonly \App\Model\ContentFiles $contentFileModel;

	public function __construct(protected \App\View\Page $page, protected ?bool $editable)
		{
		$this->contentFileModel = new \App\Model\ContentFiles();
		$this->tinyMCE = new \App\Model\TinyMCEInline();

		if ($this->editable)
			{
			$page->addTailScript('/TinyMCEPastableImage.js');

			if (\App\Model\Session::checkCSRF())
				{
				$this->processRequest();
				}
			}
		}

	/**
	 * @param array<string,string> $parameters
	 */
	public function makeEditable(string $id, array $parameters = []) : void
		{
		$this->tinyMCE->updatePage($this->page, $id);
		}

	private function processRequest() : void
		{
		switch ($_POST['action'] ?? '')
			{
			case 'uploadContentImage':
				$allowedTypes = ['.jpg' => 'image/jpeg',
					'.jpeg' => 'image/jpeg',
					'.gif' => 'image/gif',
					'.png' => 'image/png', ];

				[$junk, $id] = \explode('-', (string)$_POST['id']);
				$guid = \uniqid();
				$name = "story{$id}-{$guid}";
				$this->contentFileModel->upload($name, 'file', $_FILES, $allowedTypes);
				$path = \str_replace(PUBLIC_ROOT, '', $this->contentFileModel->getDestinationName());
				$this->page->setRawResponse(\json_encode(['link' => $path,
					'errorStatus' => $this->contentFileModel->getLastError(), ], JSON_THROW_ON_ERROR));

				break;
			}
		}
	}
