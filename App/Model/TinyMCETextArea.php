<?php

namespace App\Model;

class TinyMCETextArea implements \PHPFUI\Interfaces\HTMLEditor
	{
	private string $errorBoxId;

	/** @var array<string,bool|string> */
	private static array $settings = [
		'height' => '"40em"',
		'relative_urls' => false,
		'remove_script_host' => false,
		'entity_encoding' => '"raw"',
		'paste_data_images' => true,
		'paste_auto_cleanup_on_paste' => true,
		'defaultContent' => '"&#8203;"',
		'plugins' => '"advlist autolink link image lists charmap preview anchor pagebreak ' .
			'searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking ' .
			'table directionality emoticons code autolink"',
		'toolbar1' => '"bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect"',
		'toolbar2' => '"undo redo | link unlink | image | forecolor backcolor | code"',
		'image_advtab' => true,
		'license_key' => '"gpl"',
		'valid_elements' => '"*[*]"',
	];

	/**
	 * @param array<string,mixed> $parameters
	 */
	public function __construct(private int $fieldSize = 0, array $parameters = [])
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

	public function setErrorBoxId(string $errorBoxId) : static
		{
		$this->errorBoxId = $errorBoxId;

		return $this;
		}

	public function updatePage(\PHPFUI\Interfaces\Page $page, string $id) : void
		{
		$page->addTailScript('tinymce/tinymce.min.js');
		$page->addTailScript('/PHPFUI/TinyMCEPastableImage.js');

		$replaceBlobImages = 'editor.save();uploadImage(document.getElementById("' . $id . '").value,-1,' . \App\Model\Session::csrf('"') . ').' .
			'then(resultHtml => {document.getElementById("' . $id . '").value=resultHtml;if(resultHtml.length>' .
			$this->fieldSize . '&&' . $this->fieldSize . '!==0){document.getElementById("' . $this->errorBoxId . '").style.display="block";}else{document.getElementById("' . $this->errorBoxId . '").style.display="none";}});' . "\n\n";

		$settings = self::$settings;
		$settings['selector'] = '"#' . $id . '"';
		$settings['setup'] = 'function(editor){editor.on("change",function(){' . $replaceBlobImages . '})}';
		$js = "\n\n" . 'tinymce.init(' . \PHPFUI\TextHelper::arrayToJS($settings) . ')' . "\n\n";

		$page->addJavaScript($js);
		}
	}
