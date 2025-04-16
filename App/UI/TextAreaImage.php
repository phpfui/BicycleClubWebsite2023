<?php

namespace App\UI;

class TextAreaImage extends \PHPFUI\Input\TextArea
	{
	private readonly \PHPFUI\HTML5Element $errorBox;

	public function __construct(string $name, string $label = '', ?string $value = '')
		{
		parent::__construct($name, $label, $value);
		$this->errorBox = new \PHPFUI\Callout('alert');
		$this->errorBox->addAttribute('style', 'display: none;');
		$this->errorBox->add('<p>The following text is too large for the database. Remove large images or reduce the amount of text.</p>');
		}

	public function htmlEditing(\PHPFUI\Interfaces\Page $page, \PHPFUI\Interfaces\HTMLEditor $model) : static
		{
		$model->setErrorBoxId($this->errorBox->getId());	// @phpstan-ignore-line

		return parent::htmlEditing($page, $model);
		}

	protected function getStart() : string
		{
		return $this->errorBox . parent::getStart();
		}
	}
