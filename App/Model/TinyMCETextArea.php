<?php

namespace App\Model;

class TinyMCETextArea implements \PHPFUI\Interfaces\HTMLEditor
	{
	/** @var array<string,bool|string> */
	private static array $settings = [
		'height' => '"40em"',
		'relative_urls' => false,
		'remove_script_host' => false,
		'entity_encoding' => '"raw"',
		'paste_auto_cleanup_on_paste' => true,
		'defaultContent' => '"&#8203;"',
		'plugins' => '"advlist autolink link image lists charmap preview anchor pagebreak ' .
			'searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking ' .
			'table directionality emoticons code autolink"',
		'toolbar1' => '"bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect"',
		'toolbar2' => '"undo redo | link unlink | image | forecolor backcolor | code"',
		'image_advtab' => true,
		'valid_elements' => '"*[*]"',
	];

	/**
	 * @param array<string,mixed> $parameters
	 */
	public function __construct(array $parameters = [])
		{
		self::$settings = \array_merge(self::$settings, $parameters);
		}

	public static function addSetting(string $key, mixed $setting) : void
		{
		self::$settings[$key] = $setting;
		}

	public static function deleteSetting(string $key) : void
		{
		unset(self::$settings[$key]);
		}

	public function updatePage(\PHPFUI\Interfaces\Page $page, string $id) : void
		{
		$page->addTailScript('tinymce/tinymce.min.js');

		$settings = self::$settings;
		$settings['selector'] = '"#' . $id . '"';
		$settings['setup'] = 'function(editor){editor.on("change",function(){editor.save();})}';

		$js = 'tinymce.init(' . \PHPFUI\TextHelper::arrayToJS($settings) . ')';

		$page->addJavaScript($js);
		}
	}
