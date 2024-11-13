<?php

namespace App\Model;

class MDTextAreaEditor implements \PHPFUI\Interfaces\HTMLEditor
	{
	/** @var array<string,mixed> */
	private array $options = [
		'toolbar' => ['"bold"', '"italic"', '"strikethrough"', '"heading"', '"quote"', '"unordered-list"', '"ordered-list"', '"link"', '"table"', '"horizontal-rule"', '"clean-block"',  '"undo"', '"redo"',
			'"preview"', '"side-by-side"', '"fullscreen"', '"guide"', ],
		'forceSync' => true,
	];

	/**
	 * @param array<string,mixed> $parameters
	 */
	public function __construct(array $parameters = [])
		{
		$this->options = \array_merge($this->options, $parameters);
		}

	public function addOption(string $option, mixed $value) : static
		{
		$this->options[$option] = $value;

		return $this;
		}

	/**
	 * @param array<string,bool|string> $options
	 */
	public function addOptions(array $options) : static
		{
		$this->options = \array_merge($this->options, $options);

		return $this;
		}

	public function deleteOption(string $option) : static
		{
		unset($this->options[$option]);

		return $this;
		}

	public function updatePage(\PHPFUI\Interfaces\Page $page, string $id) : void
		{
		$page->addStyleSheet('https://unpkg.com/easymde/dist/easymde.min.css');
		$page->addTailScript('https://unpkg.com/easymde/dist/easymde.min.js');
		$this->addOption('element', 'document.getElementById("' . $id . '")');
		$js = \PHPFUI\TextHelper::arrayToJS($this->options);
		$page->addJavaScript("const easyMDE{$id}=new EasyMDE({$js});");
		}
	}
