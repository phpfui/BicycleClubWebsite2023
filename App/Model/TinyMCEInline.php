<?php

namespace App\Model;

class TinyMCEInline implements \PHPFUI\Interfaces\HTMLEditor
	{
	/** @var array<string,bool|string|list<string>> */
	private array $options = [
		'inline' => true,
		'menubar' => true,
		'relative_urls' => false,
		'entity_encoding' => 'raw',
		'paste_data_images' => true,
		'paste_auto_cleanup_on_paste' => true,
		'defaultContent' => '&#8203;',
		'remove_script_host' => false,
		'toolbar_mode' => 'floating',
		'toolbar_location' => 'bottom',
		'plugins' => 'advlist autolink link image lists charmap preview anchor pagebreak ' .
			'searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking ' .
			'table directionality emoticons code autolink',
		'toolbar' => [
			'bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect',
			'undo redo | link unlink | image | forecolor backcolor | code',
		],
		'block_formats' => 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6; Preformatted=pre',
		'valid_elements' => '*[*]',
		'license_key' => 'gpl',
		'image_advtab' => true,
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

	public function getActivateCode(\PHPFUI\Page $page, string $id) : string
		{
		$this->updatePage($page, $id);
		$options = $this->options;
		$options['selector'] = "#{$id}";

		return 'tinymce.init(' . \PHPFUI\TextHelper::arrayToJS($options, '"') . ');';
		}

	public function updatePage(\PHPFUI\Interfaces\Page $page, string $id) : void
		{
		$page->addTailScript('tinymce/tinymce.min.js');
		$page->addTailScript('/PHPFUI/TinyMCEPastableImage.js');
		}
	}
